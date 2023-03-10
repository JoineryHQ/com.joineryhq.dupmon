<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'Cron:DedupeMonitor.Scan',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Call DedupeMonitor.Scan API',
      'description' => 'Call DedupeMonitor.Scan API',
      'run_frequency' => 'Hourly',
      'api_entity' => 'DedupeMonitor',
      'api_action' => 'Scan',
      'parameters' => '',
      'update' => 'never',
    ],
  ],
];
