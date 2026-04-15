<?php

return [
  'title' => 'Dashboard',
  'subtitle' => 'Event estimation system overview',

  'actions' => [
    'new_estimation' => '+ New Estimation',
    'history' => 'History',
  ],

  'cards' => [
    'total_estimations' => 'Total Estimations',
    'pending_estimations' => 'Pending Estimations',
    'approved_estimations' => 'Approved Estimations',
    'inventory_title' => 'Total Equipment Inventory',
    'inventory_types' => ':count equipment types',
    'accuracy_title' => 'Estimation Accuracy',
    'accuracy_note' => 'Based on expert evaluation',
    'accuracy_formula' => 'Accuracy = Accurate evaluations / Verified estimations',
  ],

  'table' => [
    'title' => 'Recent Estimations',
    'event_name' => 'Event Name',
    'date' => 'Date',
    'estimated_cost' => 'Estimated Cost',
    'status' => 'Status',
    'empty' => 'No estimations yet. Click New Estimation to create one.',
  ],

  'status' => [
    'approved' => 'Approved',
    'pending' => 'Pending',
    'rejected' => 'Rejected',
    'revised' => 'Revised',
  ],
];