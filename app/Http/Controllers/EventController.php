<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Estimation;
use App\Models\EstimationDetail;
use App\Models\Setting;
use App\Services\InferenceEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function create()
    {
        $freeCitiesRaw = (string) Setting::getValue('transport_free_cities', 'surabaya,sidoarjo,gresik');

        $freeCities = array_filter(array_map(function ($c) {
            return strtolower(trim((string) $c));
        }, explode(',', $freeCitiesRaw)));

        $cityRatesRaw = Setting::getValue('transport_city_rates', '{}');
        $cityRates = [];
        if (is_string($cityRatesRaw)) {
            $decoded = json_decode($cityRatesRaw, true);
            if (is_array($decoded)) $cityRates = $decoded;
        }

        $rateCities = array_keys($cityRates);
        $rateCities = array_filter(array_map(fn ($c) => strtolower(trim((string) $c)), $rateCities));

        $locationOptions = array_values(array_unique(array_merge($freeCities, $rateCities)));
        sort($locationOptions);

        return view('pages.events.create', [
            'locationOptions' => $locationOptions,
        ]);
    }

    public function store(Request $request, InferenceEngineService $engine)
    {
        $data = $request->validate([
            'event_name' => ['required', 'string', 'max:150'],

            'client_name' => ['required', 'string', 'max:150'],
            'client_whatsapp' => ['required', 'string', 'max:30'],

            'event_type_choice' => ['required', 'string', 'max:50'],
            'event_type_other' => ['nullable', 'string', 'max:100'],

            'participants' => ['required', 'integer', 'min:1'],

            'location_choice' => ['required', 'string', 'max:80'],
            'location_other' => ['nullable', 'string', 'max:120'],

            // ✅ venue type
            'venue_type' => ['required', 'in:indoor,outdoor'],

            'event_days' => ['required', 'integer', 'min:1', 'max:30'],
            'hours_per_day' => ['required', 'integer', 'min:1', 'max:24'],

            'service_level' => ['required', 'string', 'max:50'],
            'special_requirement' => ['nullable', 'string'],

            'crew_operator_qty' => ['nullable', 'integer', 'min:0'],
            'crew_engineer_qty' => ['nullable', 'integer', 'min:0'],
            'crew_stage_qty' => ['nullable', 'integer', 'min:0'],
        ]);

        // Final event_type
        $eventTypeFinal = $data['event_type_choice'] === 'other'
            ? trim((string)($data['event_type_other'] ?? ''))
            : $data['event_type_choice'];

        if ($data['event_type_choice'] === 'other' && $eventTypeFinal === '') {
            return back()->withErrors(['event_type_other' => 'Tipe acara wajib diisi'])->withInput();
        }

        // Final location (CITY only)
        $locationChoice = strtolower(trim((string) $data['location_choice']));
        $locationFinal = $locationChoice === 'other'
            ? trim((string)($data['location_other'] ?? ''))
            : $locationChoice;

        if ($locationChoice === 'other' && $locationFinal === '') {
            return back()->withErrors(['location_other' => 'Lokasi wajib diisi'])->withInput();
        }

        $userId = auth()->id();

        return DB::transaction(function () use ($data, $engine, $eventTypeFinal, $locationFinal, $userId) {

            $durationLegacy = max(1, (int)$data['event_days'] * (int)$data['hours_per_day']);

            $event = Event::create([
                'created_by' => $userId,

                'event_name' => $data['event_name'],
                'client_name' => $data['client_name'],
                'client_whatsapp' => $data['client_whatsapp'],

                'event_type' => $eventTypeFinal,
                'event_type_choice' => $data['event_type_choice'],
                'event_type_other' => $data['event_type_other'] ?? null,

                'participants' => (int) $data['participants'],

                // city only
                'location' => strtolower($locationFinal),
                'location_choice' => $data['location_choice'],
                'location_other' => $data['location_other'] ?? null,

                // ✅ separate venue type
                'venue_type' => $data['venue_type'],

                'event_days' => (int)$data['event_days'],
                'hours_per_day' => (int)$data['hours_per_day'],

                // legacy
                'duration' => $durationLegacy,

                'service_level' => $data['service_level'],
                'special_requirement' => $data['special_requirement'] ?? null,

                'crew_operator_qty' => array_key_exists('crew_operator_qty', $data) ? $data['crew_operator_qty'] : null,
                'crew_engineer_qty' => array_key_exists('crew_engineer_qty', $data) ? $data['crew_engineer_qty'] : null,
                'crew_stage_qty' => array_key_exists('crew_stage_qty', $data) ? $data['crew_stage_qty'] : null,
            ]);

            $result = $engine->run($event);

            $breakdown  = $result['breakdown'] ?? [];
            $traceJson  = $result['trace_json'] ?? null;
            $parsedTags = $result['parsed_tags'] ?? [];

            $estimation = Estimation::create([
                'created_by'  => $userId,
                'event_id'    => $event->id,
                'total_cost'  => (int)($breakdown['total'] ?? 0),
                'status'      => 'pending',
                'breakdown'   => $breakdown,

                'trace_json'  => $traceJson,
                'parsed_tags' => $parsedTags,
            ]);

            foreach (($result['inventory'] ?? []) as $name => $row) {
                $lineTotal =
                    (int)($row['unit_price'] ?? 0) *
                    (int)($row['need'] ?? 0) *
                    (int)($breakdown['duration_block'] ?? 1) *
                    (int)($breakdown['equipment_days'] ?? 1);

                EstimationDetail::create([
                    'estimation_id'  => $estimation->id,
                    'equipment_name' => $name,

                    'quantity' => (int)($row['need'] ?? 0),
                    'price'    => (int)($row['unit_price'] ?? 0),
                    'total'    => (int)$lineTotal,

                    'original_quantity' => (int)($row['need'] ?? 0),
                    'original_price'    => (int)($row['unit_price'] ?? 0),
                    'original_total'    => (int)$lineTotal,

                    'available' => (int)($row['available'] ?? 0),
                    'shortage'  => (int)($row['shortage'] ?? 0),
                ]);
            }

            return redirect()->route('estimations.show', $estimation->id);
        });
    }
}