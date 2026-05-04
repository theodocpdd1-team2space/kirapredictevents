<?php

namespace App\Services;

use App\Models\CapacityTier;
use App\Models\EquipmentDependency;
use App\Models\Event;
use App\Models\Inventory;
use App\Models\Rule;
use App\Models\Setting;

class InferenceEngineService
{
    private ?int $tenantId = null;

    public function run(Event $event): array
    {
        $this->tenantId = $event->tenant_id ? (int) $event->tenant_id : null;

        $trace = [
            'version' => 'v2-pure-engine-db-rules',
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
            'location'            => $event->location,
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
            'step'    => 'parser',
            'input'   => (string)($event->special_requirement ?? ''),
            'tags'    => $facts['sr_tags'],
            'facts'   => $facts['sr_facts'],
            'matches' => $parsed['matches'] ?? [],
        ];

        $facts = $this->normalizeFacts($facts);
        $facts = $this->attachCapacityTierFacts($facts);

        $trace['steps'][] = [
            'step'  => 'normalize_facts',
            'facts' => $facts,
        ];

        [$equipmentReq, $crewReq, $matchedRules, $ruleTrace, $meta] = $this->inferRequirements($facts);

        $trace['steps'][] = [
            'step'          => 'rules',
            'matched_rules' => $matchedRules,
            'rule_trace'    => $ruleTrace,
            'equipment_req' => $equipmentReq,
            'crew_req'      => $crewReq,
            'meta'          => $meta,
        ];

        [$equipmentReq, $dependencyTrace] = $this->resolveEquipmentDependencies($equipmentReq);

        $trace['steps'][] = [
            'step'          => 'equipment_dependencies',
            'dependency_trace' => $dependencyTrace,
            'equipment_req' => $equipmentReq,
        ];

        $crewReqBefore = $crewReq;
        $crewReq = $this->overrideCrewFromEvent($event, $crewReq);

        $trace['steps'][] = [
            'step'   => 'crew_override',
            'before' => $crewReqBefore,
            'after'  => $crewReq,
            'source' => 'event_manual_if_present',
        ];

        $inventoryCheck = $this->validateInventory($equipmentReq);

        $trace['steps'][] = [
            'step'      => 'inventory_validate',
            'inventory' => $inventoryCheck,
        ];

        $breakdown = $this->calculateCosts($facts, $inventoryCheck, $crewReq, $meta);

        $trace['steps'][] = [
            'step'      => 'costs',
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

    private function currentTenantId(): ?int
    {
        return $this->tenantId;
    }

    private function attachCapacityTierFacts(array $facts): array
    {
        $tenantId = $this->currentTenantId();
        $participants = (int)($facts['participants'] ?? 0);
        $serviceLevel = strtolower(trim((string)($facts['service_level'] ?? 'standard')));

        $facts['capacity_tier'] = null;
        $facts['capacity_label'] = null;
        $facts['capacity_watt_min'] = null;
        $facts['capacity_watt_max'] = null;
        $facts['package_key'] = null;

        if (!$tenantId || $participants <= 0) {
            return $facts;
        }

        $tier = CapacityTier::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->where('min_participants', '<=', $participants)
            ->where(function ($q) use ($participants) {
                $q->whereNull('max_participants')
                    ->orWhere('max_participants', '>=', $participants);
            })
            ->orderBy('sort_order')
            ->orderBy('min_participants')
            ->first();

        if (!$tier) {
            return $facts;
        }

        $facts['capacity_tier'] = $tier->key;
        $facts['capacity_label'] = $tier->label;
        $facts['capacity_watt_min'] = $tier->watt_min;
        $facts['capacity_watt_max'] = $tier->watt_max;

        // Format final:
        // tier_5001_10000_premium
        $facts['package_key'] = strtolower(trim($tier->key . '_' . $serviceLevel));

        return $facts;
    }

    private function inferRequirements(array $facts): array
    {
        $tenantId = $this->currentTenantId();

        if (!$tenantId) {
            return [
                [],
                ['operator' => 0, 'engineer' => 0, 'stage' => 0],
                [],
                [],
                $this->emptyMeta(),
            ];
        }

        $rules = Rule::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $equipmentReq = [];
        $crewReq = [
            'operator' => 0,
            'engineer' => 0,
            'stage'    => 0,
        ];

        $matched = [];
        $ruleTrace = [];
        $meta = $this->emptyMeta();

        foreach ($rules as $rule) {
            $field = (string) $rule->condition_field;
            $op    = (string) $rule->operator;
            $val   = (string) $rule->value;

            $factValue = $facts[$field] ?? null;
            $matchedBool = $this->matches($field, $factValue, $op, $val);

            $ruleTrace[] = [
                'rule_id'    => $rule->id,
                'field'      => $field,
                'operator'   => $op,
                'value'      => $val,
                'fact_value' => $factValue,
                'matched'    => $matchedBool,
                'action'     => $rule->action,
                'priority'   => $rule->priority,
                'category'   => $rule->category,
            ];

            if (!$matchedBool) {
                continue;
            }

            $matched[] = [
                'id'       => $rule->id,
                'field'    => $field,
                'operator' => $op,
                'value'    => $val,
                'category' => $rule->category,
                'priority' => $rule->priority,
            ];

            $this->applyAction($rule->action, $equipmentReq, $crewReq, $meta);
        }

        return [$equipmentReq, $crewReq, $matched, $ruleTrace, $meta];
    }

    private function emptyMeta(): array
    {
        return [
            'package_label' => null,
            'operational_costs' => [],
            'markup_percent_additions' => [],
        ];
    }

    private function resolveEquipmentDependencies(array $equipmentReq): array
    {
        $tenantId = $this->currentTenantId();

        if (!$tenantId || empty($equipmentReq)) {
            return [$equipmentReq, []];
        }

        $dependencyTrace = [];

        /*
         * Loop beberapa kali supaya dependency bertingkat tetap kebaca:
         * Mixer -> Stagebox -> Cat6 tambahan, misalnya.
         */
        for ($i = 0; $i < 5; $i++) {
            $changed = false;
            $equipmentNames = array_keys($equipmentReq);

            $dependencies = EquipmentDependency::query()
                ->where('tenant_id', $tenantId)
                ->where('is_active', 1)
                ->whereIn('trigger_equipment_name', $equipmentNames)
                ->orderBy('id')
                ->get();

            foreach ($dependencies as $dep) {
                $trigger = trim((string) $dep->trigger_equipment_name);
                $required = trim((string) $dep->required_equipment_name);
                $qty = max(1, (int) $dep->quantity);

                if ($trigger === '' || $required === '') {
                    continue;
                }

                if (!array_key_exists($trigger, $equipmentReq)) {
                    continue;
                }

                $before = (int)($equipmentReq[$required] ?? 0);

                /*
                 * Dependency pakai max, bukan tambah terus.
                 * Contoh:
                 * X32 butuh S32 qty 1.
                 * Kalau S32 sudah ada qty 1 dari rule lain, tidak jadi 2.
                 */
                $after = max($before, $qty);

                if ($after !== $before) {
                    $equipmentReq[$required] = $after;
                    $changed = true;

                    $dependencyTrace[] = [
                        'trigger' => $trigger,
                        'required' => $required,
                        'quantity' => $qty,
                        'before' => $before,
                        'after' => $after,
                        'reason' => $dep->reason,
                    ];
                }
            }

            if (!$changed) {
                break;
            }
        }

        return [$equipmentReq, $dependencyTrace];
    }

    private function overrideCrewFromEvent(Event $event, array $crewReq): array
    {
        $hasManual =
            $event->crew_operator_qty !== null ||
            $event->crew_engineer_qty !== null ||
            $event->crew_stage_qty !== null;

        if (!$hasManual) {
            return $crewReq;
        }

        return [
            'operator' => (int) ($event->crew_operator_qty ?? 0),
            'engineer' => (int) ($event->crew_engineer_qty ?? 0),
            'stage'    => (int) ($event->crew_stage_qty ?? 0),
        ];
    }

    private function validateInventory(array $equipmentReq): array
    {
        $tenantId = $this->currentTenantId();

        if (!$tenantId || empty($equipmentReq)) {
            return [];
        }

        $names = array_keys($equipmentReq);

        $invMap = Inventory::query()
            ->where('tenant_id', $tenantId)
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

    private function calculateCosts(array $facts, array $inventoryCheck, array $crewReq, array $meta = []): array
    {
        $tenantId = $this->currentTenantId();

        $eventDays   = max(1, (int) ($facts['event_days'] ?? 1));
        $hoursPerDay = max(1, (int) ($facts['hours_per_day'] ?? 1));
        $totalHours  = max(1, (int) ($facts['duration'] ?? ($eventDays * $hoursPerDay)));

        $b1 = (float) Setting::getValue('duration_block_1', 1, $tenantId);
        $b2 = (float) Setting::getValue('duration_block_2', 2, $tenantId);
        $b3 = (float) Setting::getValue('duration_block_3', 3, $tenantId);

        $durationBlock = $hoursPerDay <= 4 ? $b1 : ($hoursPerDay <= 8 ? $b2 : $b3);

        $equipmentCostPerDay = 0;

        foreach ($inventoryCheck as $row) {
            $equipmentCostPerDay += ((int) $row['need'] * (int) $row['unit_price']);
        }

        $equipmentCost = (int) round($equipmentCostPerDay * $eventDays * $durationBlock);

        $capacityTier = (string)($facts['capacity_tier'] ?? '');
        $crewFeeModel = (string) Setting::getValue('crew_fee_model', 'package_by_participants', $tenantId);

        $laborCost = 0;

        if ($crewFeeModel === 'package_by_participants') {
            /*
             * Backward compatible dengan settings lama:
             * labor_t1, labor_t2, labor_t3, labor_t4
             *
             * Mapping baru:
             * tier_1_100, tier_101_500              => labor_t1
             * tier_501_1500, tier_1501_3000         => labor_t2
             * tier_3001_5000, tier_5001_10000       => labor_t3
             * tier_10001_20000, tier_20001_plus     => labor_t4
             */
            $laborBase = match ($capacityTier) {
                'tier_1_100',
                'tier_101_500' => (int) Setting::getValue('labor_t1', 600000, $tenantId),

                'tier_501_1500',
                'tier_1501_3000' => (int) Setting::getValue('labor_t2', 1200000, $tenantId),

                'tier_3001_5000',
                'tier_5001_10000' => (int) Setting::getValue('labor_t3', 2500000, $tenantId),

                'tier_10001_20000',
                'tier_20001_plus' => (int) Setting::getValue('labor_t4', 5000000, $tenantId),

                default => (int) Setting::getValue('labor_t1', 600000, $tenantId),
            };

            $laborCost = (int) round($laborBase * $eventDays * $durationBlock);
        } elseif ($crewFeeModel === 'per_role_per_day') {
            $opRate  = (int) Setting::getValue('crew_operator_rate_day', 350000, $tenantId);
            $engRate = (int) Setting::getValue('crew_engineer_rate_day', 500000, $tenantId);
            $stgRate = (int) Setting::getValue('crew_stage_rate_day', 250000, $tenantId);

            $laborPerDay =
                ((int) ($crewReq['operator'] ?? 0) * $opRate) +
                ((int) ($crewReq['engineer'] ?? 0) * $engRate) +
                ((int) ($crewReq['stage'] ?? 0)    * $stgRate);

            $laborCost = (int) round($laborPerDay * $eventDays * $durationBlock);
        } else {
            $opRate  = (int) Setting::getValue('crew_operator_rate_hour', 60000, $tenantId);
            $engRate = (int) Setting::getValue('crew_engineer_rate_hour', 90000, $tenantId);
            $stgRate = (int) Setting::getValue('crew_stage_rate_hour', 45000, $tenantId);

            $laborPerHour =
                ((int) ($crewReq['operator'] ?? 0) * $opRate) +
                ((int) ($crewReq['engineer'] ?? 0) * $engRate) +
                ((int) ($crewReq['stage'] ?? 0)    * $stgRate);

            $laborCost = (int) round($laborPerHour * ($eventDays * $hoursPerDay));
        }

        $transportCost = $this->calculateTransportCost($facts, $tenantId);

        $operationalPercent = (float) Setting::getValue('operational_percent', 5, $tenantId);
        $markupPercent      = (float) Setting::getValue('markup_percent', 0, $tenantId);

        foreach (($meta['markup_percent_additions'] ?? []) as $m) {
            $markupPercent += (float)($m['value'] ?? 0);
        }

        $operationalCost = (int) round($equipmentCost * ($operationalPercent / 100));

        $manualOperationalCost = 0;
        foreach (($meta['operational_costs'] ?? []) as $row) {
            $manualOperationalCost += (int)($row['amount'] ?? 0);
        }

        $operationalTotal = $operationalCost + $manualOperationalCost;

        $subTotal = $equipmentCost + $laborCost + $transportCost + $operationalTotal;
        $markupCost = (int) round($subTotal * ($markupPercent / 100));
        $total = $subTotal + $markupCost;

        return [
            'equipment'         => $equipmentCost,
            'labor'             => $laborCost,
            'transport'         => $transportCost,
            'operational'       => $operationalTotal,
            'operational_percent_cost' => $operationalCost,
            'operational_manual_cost'  => $manualOperationalCost,
            'markup'            => $markupCost,
            'total'             => $total,

            'event_days'        => $eventDays,
            'hours_per_day'     => $hoursPerDay,
            'duration_hours'    => $totalHours,
            'duration_block'    => $durationBlock,
            'equipment_days'    => $eventDays,

            'crew_fee_model'    => $crewFeeModel,
            'crew_used'         => $crewReq,

            'capacity_tier'     => $facts['capacity_tier'] ?? null,
            'capacity_label'    => $facts['capacity_label'] ?? null,
            'capacity_watt_min' => $facts['capacity_watt_min'] ?? null,
            'capacity_watt_max' => $facts['capacity_watt_max'] ?? null,
            'package_key'       => $facts['package_key'] ?? null,
            'package_label'     => $meta['package_label'] ?? null,

            'markup_percent'    => $markupPercent,
            'operational_costs' => $meta['operational_costs'] ?? [],

            'sr_tags'           => $facts['sr_tags'] ?? [],
        ];
    }

    private function calculateTransportCost(array $facts, ?int $tenantId): int
    {
        $locationRaw = strtolower(trim((string) ($facts['location'] ?? '')));

        $transportOutdoor = (int) Setting::getValue('transport_outdoor', 600000, $tenantId);
        $transportOther   = (int) Setting::getValue('transport_other', 300000, $tenantId);

        $freeCitiesRaw = (string) Setting::getValue('transport_free_cities', 'surabaya,sidoarjo,gresik', $tenantId);
        $freeCities = array_filter(array_map(
            fn ($x) => strtolower(trim((string) $x)),
            explode(',', $freeCitiesRaw)
        ));

        $cityRatesRaw = Setting::getValue('transport_city_rates', '{}', $tenantId);
        $cityRates = [];

        if (is_array($cityRatesRaw)) {
            $cityRates = $cityRatesRaw;
        } elseif (is_string($cityRatesRaw)) {
            $decoded = json_decode($cityRatesRaw, true);
            if (is_array($decoded)) {
                $cityRates = $decoded;
            }
        }

        $venue = strtolower(trim((string)($facts['venue_type'] ?? '')));
        $isOutdoor = ($venue === 'outdoor') || in_array('is_outdoor', ($facts['sr_tags'] ?? []), true);

        $cityKey = trim(explode(',', $locationRaw)[0] ?? $locationRaw);

        if ($isOutdoor) {
            return $transportOutdoor;
        }

        if ($cityKey !== '' && in_array($cityKey, $freeCities, true)) {
            return 0;
        }

        if ($cityKey !== '' && array_key_exists($cityKey, $cityRates)) {
            return (int) $cityRates[$cityKey];
        }

        return $transportOther;
    }

    private function normalizeFacts(array $facts): array
    {
        foreach (['event_type', 'location', 'service_level', 'venue_type'] as $k) {
            if (isset($facts[$k]) && is_string($facts[$k])) {
                $facts[$k] = strtolower(trim($facts[$k]));
            }
        }

        foreach (['participants', 'duration', 'event_days', 'hours_per_day'] as $k) {
            if (isset($facts[$k])) {
                $facts[$k] = (int) $facts[$k];
            }
        }

        $tags = $facts['sr_tags'] ?? [];
        $facts['sr_tags_text'] = is_array($tags) ? implode(',', $tags) : '';

        return $facts;
    }

    private function matches(string $field, $factValue, string $operator, string $ruleValue): bool
    {
        $operator = strtolower(trim($operator));

        if ($operator === 'between') {
            if (!is_numeric($factValue)) {
                return false;
            }

            [$min, $max] = $this->parseRange($ruleValue);

            if ($min === null || $max === null) {
                return false;
            }

            $fv = (int) $factValue;

            return $fv >= $min && $fv <= $max;
        }

        if ($operator === 'contains') {
            if (is_array($factValue)) {
                $hay = strtolower(implode(',', $factValue));
            } else {
                $hay = is_string($factValue) ? strtolower($factValue) : '';
            }

            $needle = strtolower(trim($ruleValue));

            return $needle !== '' && str_contains($hay, $needle);
        }

        if ($operator === '=') {
            if (is_numeric($factValue) && is_numeric($ruleValue)) {
                return (float) $factValue == (float) $ruleValue;
            }

            return strtolower((string) $factValue) === strtolower(trim($ruleValue));
        }

        if (in_array($operator, ['>', '>=', '<', '<='], true)) {
            if (!is_numeric($factValue) || !is_numeric($ruleValue)) {
                return false;
            }

            $a = (float) $factValue;
            $b = (float) $ruleValue;

            return match ($operator) {
                '>'  => $a >  $b,
                '>=' => $a >= $b,
                '<'  => $a <  $b,
                '<=' => $a <= $b,
                default => false,
            };
        }

        if ($operator === 'in') {
            $options = array_map('trim', explode(',', strtolower($ruleValue)));

            return in_array(strtolower((string) $factValue), $options, true);
        }

        return false;
    }

    private function parseRange(string $range): array
    {
        $range = trim($range);

        if (!str_contains($range, '-')) {
            return [null, null];
        }

        $parts = array_map('trim', explode('-', $range, 2));

        if (count($parts) !== 2) {
            return [null, null];
        }

        $min = is_numeric($parts[0]) ? (int) $parts[0] : null;
        $max = is_numeric($parts[1]) ? (int) $parts[1] : null;

        return [$min, $max];
    }

    private function applyAction($actionRaw, array &$equipmentReq, array &$crewReq, array &$meta): void
    {
        if (empty($actionRaw)) {
            return;
        }

        $decoded = is_array($actionRaw) ? $actionRaw : json_decode($actionRaw, true);

        if (!is_array($decoded)) {
            return;
        }

        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            $type = strtoupper((string) ($item['type'] ?? ''));

            if ($type === 'ADD_EQUIPMENT') {
                $name = trim((string) ($item['name'] ?? ''));
                $qty  = (int) ($item['qty'] ?? 0);

                if ($name === '' || $qty <= 0) {
                    continue;
                }

                $equipmentReq[$name] = ($equipmentReq[$name] ?? 0) + $qty;
                continue;
            }

            if ($type === 'ADD_CREW') {
                $role = strtolower(trim((string) ($item['role'] ?? '')));
                $qty  = (int) ($item['qty'] ?? 0);

                if (!in_array($role, ['operator', 'engineer', 'stage'], true)) {
                    continue;
                }

                if ($qty <= 0) {
                    continue;
                }

                $crewReq[$role] = ($crewReq[$role] ?? 0) + $qty;
                continue;
            }

            if ($type === 'SET_PACKAGE_LABEL') {
                $name = trim((string) ($item['name'] ?? ''));

                if ($name !== '') {
                    $meta['package_label'] = $name;
                }

                continue;
            }

            if ($type === 'ADD_OPERATIONAL_COST') {
                $name = trim((string) ($item['name'] ?? 'Operational Cost'));
                $amount = (int) ($item['amount'] ?? 0);

                if ($amount > 0) {
                    $meta['operational_costs'][] = [
                        'name' => $name,
                        'amount' => $amount,
                    ];
                }

                continue;
            }

            if ($type === 'ADD_MARKUP_PERCENT') {
                $value = (float) ($item['value'] ?? 0);

                if ($value > 0) {
                    $meta['markup_percent_additions'][] = [
                        'value' => $value,
                    ];
                }

                continue;
            }
        }
    }
}