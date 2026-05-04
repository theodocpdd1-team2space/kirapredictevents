<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class CostController extends Controller
{
    private function guardOwnerRole(): void
    {
        abort_unless(auth()->user()?->isOwner(), 403, 'Akses hanya untuk Owner.');
    }

    public function edit()
    {
        $this->guardOwnerRole();

        $transportCityRatesRaw = Setting::getValue('transport_city_rates', '{}');
        $transportCityRatesArr = [];

        if (is_array($transportCityRatesRaw)) {
            $transportCityRatesArr = $transportCityRatesRaw;
        } elseif (is_string($transportCityRatesRaw)) {
            $decoded = json_decode($transportCityRatesRaw, true);
            if (is_array($decoded)) {
                $transportCityRatesArr = $decoded;
            }
        }

        return view('pages.settings.cost.edit', [
            'rates_currency' => Setting::getValue('rates_currency', 'IDR'),

            'crew_fee_model' => Setting::getValue('crew_fee_model', 'package_by_participants'),

            'labor_t1' => (int) Setting::getValue('labor_t1', 600000),
            'labor_t2' => (int) Setting::getValue('labor_t2', 1200000),
            'labor_t3' => (int) Setting::getValue('labor_t3', 2500000),
            'labor_t4' => (int) Setting::getValue('labor_t4', 5000000),

            'crew_operator_rate_day' => (int) Setting::getValue('crew_operator_rate_day', 350000),
            'crew_engineer_rate_day' => (int) Setting::getValue('crew_engineer_rate_day', 500000),
            'crew_stage_rate_day'    => (int) Setting::getValue('crew_stage_rate_day', 250000),

            'crew_operator_rate_hour' => (int) Setting::getValue('crew_operator_rate_hour', 60000),
            'crew_engineer_rate_hour' => (int) Setting::getValue('crew_engineer_rate_hour', 90000),
            'crew_stage_rate_hour'    => (int) Setting::getValue('crew_stage_rate_hour', 45000),

            'duration_block_1' => (float) Setting::getValue('duration_block_1', 1),
            'duration_block_2' => (float) Setting::getValue('duration_block_2', 2),
            'duration_block_3' => (float) Setting::getValue('duration_block_3', 3),

            'transport_outdoor'        => (int) Setting::getValue('transport_outdoor', 600000),
            'transport_other'          => (int) Setting::getValue('transport_other', 300000),
            'transport_free_cities'    => (string) Setting::getValue('transport_free_cities', 'surabaya,sidoarjo,gresik'),
            'transport_city_rates_arr' => $transportCityRatesArr,

            'operational_percent' => (float) Setting::getValue('operational_percent', 5),
            'markup_percent'      => (float) Setting::getValue('markup_percent', 0),
        ]);
    }

    public function update(Request $request)
    {
        $this->guardOwnerRole();

        $v = $request->validate([
            'rates_currency' => ['required', 'string', 'max:10'],

            'crew_fee_model' => ['required', 'in:package_by_participants,per_role_per_day,per_role_per_hour'],

            'labor_t1' => ['required', 'integer', 'min:0'],
            'labor_t2' => ['required', 'integer', 'min:0'],
            'labor_t3' => ['required', 'integer', 'min:0'],
            'labor_t4' => ['required', 'integer', 'min:0'],

            'crew_operator_rate_day' => ['required', 'integer', 'min:0'],
            'crew_engineer_rate_day' => ['required', 'integer', 'min:0'],
            'crew_stage_rate_day'    => ['required', 'integer', 'min:0'],

            'crew_operator_rate_hour' => ['required', 'integer', 'min:0'],
            'crew_engineer_rate_hour' => ['required', 'integer', 'min:0'],
            'crew_stage_rate_hour'    => ['required', 'integer', 'min:0'],

            'duration_block_1' => ['required', 'numeric', 'min:0', 'max:50'],
            'duration_block_2' => ['required', 'numeric', 'min:0', 'max:50'],
            'duration_block_3' => ['required', 'numeric', 'min:0', 'max:50'],

            'transport_outdoor'     => ['required', 'integer', 'min:0'],
            'transport_other'       => ['required', 'integer', 'min:0'],
            'transport_free_cities' => ['nullable', 'string', 'max:500'],

            'city_name'   => ['nullable', 'array'],
            'city_name.*' => ['nullable', 'string', 'max:80'],
            'city_rate'   => ['nullable', 'array'],
            'city_rate.*' => ['nullable', 'integer', 'min:0'],

            'operational_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'markup_percent'      => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $map = [];
        $names = $request->input('city_name', []);
        $rates = $request->input('city_rate', []);

        foreach ($names as $i => $name) {
            $city = strtolower(trim((string) $name));
            $rate = array_key_exists($i, $rates) ? $rates[$i] : null;

            if ($city === '' || $rate === null || $rate === '') {
                continue;
            }

            $map[$city] = (int) $rate;
        }

        foreach ($v as $key => $value) {
            if (in_array($key, ['city_name', 'city_rate'], true)) {
                continue;
            }

            Setting::setValue($key, $value);
        }

        Setting::setValue('transport_city_rates', $map);

        return back()->with('success', 'Cost settings updated.');
    }
}