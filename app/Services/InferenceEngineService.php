<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Inventory;
use App\Models\Rule;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class InferenceEngineService
{
    public function run(Event $event): array
    {
        $trace = [
            'version' => 'v1-trace',
            'steps' => [],
        ];

        $eventDays   = max(1, (int)($event->event_days ?? 1));
        $hoursPerDay = max(1, (int)($event->hours_per_day ?? 1));
        $totalHours  = max(1, $eventDays * $hoursPerDay);

        $parser = new SpecialRequirementParser();
        $parsed = $parser->parse((string)($event->special_requirement ?? ''));

        $facts = [
            'event_type'          => $event->event_type,
            'participants'        => (int) $event->participants,

            // ✅ city only
            'location'            => $event->location,

            // ✅ NEW: venue_type separate
            'venue_type'          => (string)($event->venue_type ?? ''),

            'service_level'       => $event->service_level,
            'special_requirement' => (string)($event->special_requirement ?? ''),

            'event_days'          => $eventDays,
            'hours_per_day'       => $hoursPerDay,
            'duration'            => $totalHours,

            'sr_tags'             => $parsed['tags'] ?? [],
            'sr_facts'            => $parsed['facts'] ?? [],
        ];

        $trace['steps'][] = [
            'step' => 'parser',
            'input' => (string)($event->special_requirement ?? ''),
            'tags' => $facts['sr_tags'],
            'facts' => $facts['sr_facts'],
            'matches' => $parsed['matches'] ?? [],
        ];

        $facts = $this->normalizeFacts($facts);

        $trace['steps'][] = [
            'step' => 'normalize_facts',
            'facts' => $facts,
        ];

        [$equipmentReq, $crewReq, $matchedRules, $ruleTrace] = $this->inferRequirements($facts);

        $trace['steps'][] = [
            'step' => 'rules',
            'matched_rules' => $matchedRules,
            'rule_trace' => $ruleTrace,
            'equipment_req' => $equipmentReq,
            'crew_req' => $crewReq,
        ];

        $crewReqBefore = $crewReq;
        $crewReq = $this->overrideCrewFromEvent($event, $crewReq);

        $trace['steps'][] = [
            'step' => 'crew_override',
            'before' => $crewReqBefore,
            'after' => $crewReq,
            'source' => 'event_manual_if_present',
        ];

        $inventoryCheck = $this->validateInventory($equipmentReq);

        $trace['steps'][] = [
            'step' => 'inventory_validate',
            'inventory' => $inventoryCheck,
        ];

        $breakdown = $this->calculateCosts($facts, $inventoryCheck, $crewReq);

        $trace['steps'][] = [
            'step' => 'costs',
            'breakdown' => $breakdown,
        ];

        return [
            'requirements'  => $equipmentReq,
            'crew'          => $crewReq,
            'inventory'     => $inventoryCheck,
            'breakdown'     => $breakdown,
            'matched_rules' => $matchedRules,

            'parsed_tags'   => $facts['sr_tags'] ?? [],
            'trace_json'    => $trace,
        ];
    }

    private function currentUserId(): ?int
    {
        $id = Auth::id();
        return $id ? (int) $id : null;
    }

    private function inferRequirements(array $facts): array
    {
        $userId = $this->currentUserId();
        if (!$userId) return [[], ['operator' => 0, 'engineer' => 0, 'stage' => 0], [], []];

        $rules = Rule::query()
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $equipmentReq = [];
        $crewReq = ['operator' => 0, 'engineer' => 0, 'stage' => 0];
        $matched = [];
        $ruleTrace = [];

        foreach ($rules as $rule) {
            $field = (string) $rule->condition_field;
            $op    = (string) $rule->operator;
            $val   = (string) $rule->value;

            $factValue = $facts[$field] ?? null;

            $matchedBool = $this->matches($field, $factValue, $op, $val);

            $ruleTrace[] = [
                'rule_id' => $rule->id,
                'field' => $field,
                'operator' => $op,
                'value' => $val,
                'fact_value' => $factValue,
                'matched' => $matchedBool,
                'action' => $rule->action,
                'priority' => $rule->priority,
                'category' => $rule->category,
            ];

            if (!$matchedBool) continue;

            $matched[] = [
                'id'       => $rule->id,
                'field'    => $field,
                'operator' => $op,
                'value'    => $val,
                'category' => $rule->category,
                'priority' => $rule->priority,
            ];

            $this->applyAction($rule->action, $equipmentReq, $crewReq);
        }

        return [$equipmentReq, $crewReq, $matched, $ruleTrace];
    }

    private function overrideCrewFromEvent(Event $event, array $crewReq): array
    {
        $hasManual =
            $event->crew_operator_qty !== null ||
            $event->crew_engineer_qty !== null ||
            $event->crew_stage_qty !== null;

        if (!$hasManual) return $crewReq;

        return [
            'operator' => (int) ($event->crew_operator_qty ?? 0),
            'engineer' => (int) ($event->crew_engineer_qty ?? 0),
            'stage'    => (int) ($event->crew_stage_qty ?? 0),
        ];
    }

    private function validateInventory(array $equipmentReq): array
    {
        $userId = $this->currentUserId();
        if (!$userId) return [];
        if (empty($equipmentReq)) return [];

        $names = array_keys($equipmentReq);

        $invMap = Inventory::query()
            ->where('user_id', $userId)
            ->whereIn('equipment_name', $names)
            ->get()
            ->keyBy('equipment_name');

        $result = [];

        foreach ($equipmentReq as $name => $need) {
            $inv = $invMap->get($name);

            $available = (int) ($inv->quantity ?? 0);
            $shortage  = max(0, (int) $need - $available);
            $unitPrice = (int) ($inv->price ?? 0);

            $result[$name] = [
                'need'       => (int) $need,
                'available'  => $available,
                'shortage'   => $shortage,
                'unit_price' => $unitPrice,
                'exists'     => $inv !== null,
            ];
        }

        return $result;
    }

    private function calculateCosts(array $facts, array $inventoryCheck, array $crewReq): array
    {
        $userId = $this->currentUserId();

        $eventDays   = max(1, (int) ($facts['event_days'] ?? 1));
        $hoursPerDay = max(1, (int) ($facts['hours_per_day'] ?? 1));
        $totalHours  = max(1, (int) ($facts['duration'] ?? ($eventDays * $hoursPerDay)));

        $b1 = (float) Setting::getValue('duration_block_1', 1, $userId);
        $b2 = (float) Setting::getValue('duration_block_2', 2, $userId);
        $b3 = (float) Setting::getValue('duration_block_3', 3, $userId);

        $durationBlock = $hoursPerDay <= 4 ? $b1 : ($hoursPerDay <= 8 ? $b2 : $b3);

        $equipmentCostPerDay = 0;
        foreach ($inventoryCheck as $row) {
            $equipmentCostPerDay += ((int) $row['need'] * (int) $row['unit_price']);
        }
        $equipmentCost = (int) round($equipmentCostPerDay * $eventDays * $durationBlock);

        $tier = $facts['participants_tier'] ?? 't1_0-100';
        $crewFeeModel = (string) Setting::getValue('crew_fee_model', 'package_by_participants', $userId);

        $laborCost = 0;

        if ($crewFeeModel === 'package_by_participants') {
            $laborBase = match ($tier) {
                't1_0-100'    => (int) Setting::getValue('labor_t1', 600000, $userId),
                't2_101-300'  => (int) Setting::getValue('labor_t2', 1200000, $userId),
                't3_301-1000' => (int) Setting::getValue('labor_t3', 2500000, $userId),
                default       => (int) Setting::getValue('labor_t4', 5000000, $userId),
            };
            $laborCost = (int) round($laborBase * $eventDays * $durationBlock);
        } elseif ($crewFeeModel === 'per_role_per_day') {
            $opRate  = (int) Setting::getValue('crew_operator_rate_day', 350000, $userId);
            $engRate = (int) Setting::getValue('crew_engineer_rate_day', 500000, $userId);
            $stgRate = (int) Setting::getValue('crew_stage_rate_day', 250000, $userId);

            $laborPerDay =
                ((int)$crewReq['operator'] * $opRate) +
                ((int)$crewReq['engineer'] * $engRate) +
                ((int)$crewReq['stage']    * $stgRate);

            $laborCost = (int) round($laborPerDay * $eventDays * $durationBlock);
        } else {
            $opRate  = (int) Setting::getValue('crew_operator_rate_hour', 60000, $userId);
            $engRate = (int) Setting::getValue('crew_engineer_rate_hour', 90000, $userId);
            $stgRate = (int) Setting::getValue('crew_stage_rate_hour', 45000, $userId);

            $laborPerHour =
                ((int)$crewReq['operator'] * $opRate) +
                ((int)$crewReq['engineer'] * $engRate) +
                ((int)$crewReq['stage']    * $stgRate);

            $laborCost = (int) round($laborPerHour * ($eventDays * $hoursPerDay));
        }

        // transport
        $locationRaw = strtolower(trim((string) ($facts['location'] ?? '')));
        $transportOutdoor = (int) Setting::getValue('transport_outdoor', 600000, $userId);
        $transportOther   = (int) Setting::getValue('transport_other', 300000, $userId);

        $freeCitiesRaw = (string) Setting::getValue('transport_free_cities', 'surabaya,sidoarjo,gresik', $userId);
        $freeCities = array_filter(array_map(fn($x) => strtolower(trim($x)), explode(',', $freeCitiesRaw)));

        $cityRatesRaw = Setting::getValue('transport_city_rates', '{}', $userId);
        $cityRates = [];
        if (is_string($cityRatesRaw)) {
            $decoded = json_decode($cityRatesRaw, true);
            if (is_array($decoded)) $cityRates = $decoded;
        }

        // ✅ venue check pakai venue_type
        $venue = strtolower(trim((string)($facts['venue_type'] ?? '')));
        $isOutdoor = ($venue === 'outdoor') || in_array('is_outdoor', ($facts['sr_tags'] ?? []), true);

        $cityKey = trim(explode(',', $locationRaw)[0] ?? $locationRaw);

        if ($isOutdoor) {
            $transportCost = $transportOutdoor;
        } else {
            if ($cityKey !== '' && in_array($cityKey, $freeCities, true)) {
                $transportCost = 0;
            } elseif ($cityKey !== '' && array_key_exists($cityKey, $cityRates)) {
                $transportCost = (int) $cityRates[$cityKey];
            } else {
                $transportCost = $transportOther;
            }
        }

        $operationalPercent = (float) Setting::getValue('operational_percent', 5, $userId);
        $markupPercent      = (float) Setting::getValue('markup_percent', 0, $userId);

        $operationalCost = (int) round($equipmentCost * ($operationalPercent / 100));

        $subTotal = $equipmentCost + $laborCost + $transportCost + $operationalCost;
        $markupCost = (int) round($subTotal * ($markupPercent / 100));
        $total = $subTotal + $markupCost;

        return [
            'equipment'         => $equipmentCost,
            'labor'             => $laborCost,
            'transport'         => $transportCost,
            'operational'       => $operationalCost,
            'markup'            => $markupCost,
            'total'             => $total,

            'event_days'        => $eventDays,
            'hours_per_day'     => $hoursPerDay,
            'duration_hours'    => $totalHours,
            'duration_block'    => $durationBlock,

            'crew_fee_model'    => $crewFeeModel,
            'crew_used'         => $crewReq,
            'participants_tier' => $tier,

            'sr_tags'           => $facts['sr_tags'] ?? [],
        ];
    }

    private function normalizeFacts(array $facts): array
    {
        foreach (['event_type', 'location', 'service_level', 'venue_type'] as $k) {
            if (isset($facts[$k]) && is_string($facts[$k])) {
                $facts[$k] = strtolower(trim($facts[$k]));
            }
        }

        foreach (['participants', 'duration', 'event_days', 'hours_per_day'] as $k) {
            if (isset($facts[$k])) $facts[$k] = (int) $facts[$k];
        }

        $p = (int) ($facts['participants'] ?? 0);
        if ($p <= 100) $tier = 't1_0-100';
        elseif ($p <= 300) $tier = 't2_101-300';
        elseif ($p <= 1000) $tier = 't3_301-1000';
        else $tier = 't4_1001+';

        $facts['participants_tier'] = $tier;

        $tags = $facts['sr_tags'] ?? [];
        if (is_array($tags)) {
            $facts['sr_tags_text'] = implode(',', $tags);
        } else {
            $facts['sr_tags_text'] = '';
        }

        return $facts;
    }

    private function matches(string $field, $factValue, string $operator, string $ruleValue): bool
    {
        $operator = strtolower(trim($operator));

        if ($operator === 'between') {
            if (!is_numeric($factValue)) return false;
            [$min, $max] = $this->parseRange($ruleValue);
            if ($min === null || $max === null) return false;
            $fv = (int) $factValue;
            return $fv >= $min && $fv <= $max;
        }

        if ($operator === 'contains') {
            $hay = is_string($factValue) ? strtolower($factValue) : '';
            $needle = strtolower(trim($ruleValue));
            return $needle !== '' && str_contains($hay, $needle);
        }

        if ($operator === '=') {
            if (is_numeric($factValue) && is_numeric($ruleValue)) {
                return (float) $factValue == (float) $ruleValue;
            }
            return strtolower((string) $factValue) === strtolower(trim($ruleValue));
        }

        if (in_array($operator, ['>','>=','<','<='], true)) {
            if (!is_numeric($factValue) || !is_numeric($ruleValue)) return false;

            $a = (float) $factValue;
            $b = (float) $ruleValue;

            return match ($operator) {
                '>'  => $a >  $b,
                '>=' => $a >= $b,
                '<'  => $a <  $b,
                '<=' => $a <= $b,
            };
        }

        return false;
    }

    private function parseRange(string $range): array
    {
        $range = trim($range);
        if (!str_contains($range, '-')) return [null, null];

        $parts = array_map('trim', explode('-', $range, 2));
        if (count($parts) !== 2) return [null, null];

        $min = is_numeric($parts[0]) ? (int) $parts[0] : null;
        $max = is_numeric($parts[1]) ? (int) $parts[1] : null;

        return [$min, $max];
    }

    private function applyAction($actionRaw, array &$equipmentReq, array &$crewReq): void
    {
        if (empty($actionRaw)) return;

        $decoded = is_array($actionRaw) ? $actionRaw : json_decode($actionRaw, true);
        if (!is_array($decoded)) return;

        foreach ($decoded as $item) {
            if (!is_array($item)) continue;

            $type = strtoupper((string) ($item['type'] ?? ''));

            if ($type === 'ADD_EQUIPMENT') {
                $name = trim((string) ($item['name'] ?? ''));
                $qty  = (int) ($item['qty'] ?? 0);
                if ($name === '' || $qty <= 0) continue;

                $equipmentReq[$name] = ($equipmentReq[$name] ?? 0) + $qty;
                continue;
            }

            if ($type === 'ADD_CREW') {
                $role = strtolower(trim((string) ($item['role'] ?? '')));
                $qty  = (int) ($item['qty'] ?? 0);
                if (!in_array($role, ['operator','engineer','stage'], true)) continue;
                if ($qty <= 0) continue;

                $crewReq[$role] = ($crewReq[$role] ?? 0) + $qty;
                continue;
            }
        }
    }
}