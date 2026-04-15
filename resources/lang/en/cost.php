<?php

return [
  'title' => 'Cost & Rates',
  'subtitle' => 'Adjust default rates without changing inference engine code.',

  'general' => 'General',
  'currency' => 'Currency',
  'currency_help' => 'For now it is display-only (no FX conversion).',

  'operational_percent' => 'Operational %',
  'operational_help' => 'Example: 5 means 5% of equipment total.',

  'markup_percent' => 'Markup %',
  'markup_help' => 'Optional. Example: 20 means +20% on total.',

  'labor' => 'Labor Base (per Participants Tier)',
  'labor_help' => 'Base labor rate per duration block.',

  'tier1' => 'Tier 1 (0–100 pax)',
  'tier2' => 'Tier 2 (101–300 pax)',
  'tier3' => 'Tier 3 (301–1000 pax)',
  'tier4' => 'Tier 4 (1001+ pax)',

  'transport' => 'Transport',
  'transport_help' => 'Free cities + custom city rates + fallback.',

  'transport_default' => 'Transport Default',
  'transport_default_help' => 'Used when city is not free/custom.',

  'transport_outdoor' => 'Transport Outdoor',
  'transport_outdoor_help' => 'Extra cost for outdoor events (optional logic).',

  'free_cities' => 'Free Cities (comma separated)',
  'free_cities_help' => 'Example: surabaya,sidoarjo,gresik',

  'other_city_default' => 'Other City Default',
  'other_city_default_help' => 'Fallback when city is not in free/custom.',

  'custom_city_rates' => 'Custom City Rates',
  'custom_city_rates_help' => 'City + rate (e.g. jakarta = 1000000).',

  'add_city' => 'Add City',
  'remove' => 'Remove',

  'save' => 'Save Changes',
];