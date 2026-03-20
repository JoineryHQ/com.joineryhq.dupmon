<?php
use CRM_Dupmon_ExtensionUtil as E;

return [
  'name' => 'DupmonBatch',
  'table' => 'civicrm_dupmon_batch',
  'class' => 'CRM_Dupmon_DAO_DupmonBatch',
  'getInfo' => fn() => [
    'title' => E::ts('Dupmon Batch'),
    'title_plural' => E::ts('Dupmon Batches'),
    'description' => E::ts('Dedupe Monitor batch scan result'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique DupmonRuleMonitor ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'group_id' => [
      'title' => E::ts('Group ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to Group (required in sql but not in api)'),
      'entity_reference' => [
        'entity' => 'Group',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'rule_group_id' => [
      'title' => E::ts('Rule Group ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to Dedupe Rule Group'),
      'entity_reference' => [
        'entity' => 'DedupeRuleGroup',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'created' => [
      'title' => E::ts('Created'),
      'sql_type' => 'timestamp',
      'input_type' => NULL,
      'description' => E::ts('Date/Time created'),
      'default' => 'CURRENT_TIMESTAMP',
    ],
  ],
];
