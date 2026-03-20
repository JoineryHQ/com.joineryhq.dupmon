<?php
use CRM_Dupmon_ExtensionUtil as E;

return [
  'name' => 'DupmonRuleMonitor',
  'table' => 'civicrm_dupmon_rule_monitor',
  'class' => 'CRM_Dupmon_DAO_DupmonRuleMonitor',
  'getInfo' => fn() => [
    'title' => E::ts('Dupmon Rule Monitor'),
    'title_plural' => E::ts('Dupmon Rule Monitors'),
    'description' => E::ts('Dedupe Monitor rule configurations'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'is_active' => [
      'fields' => [
        'rule_group_id' => TRUE,
      ],
      'unique' => TRUE,
    ],
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
    'rule_group_id' => [
      'title' => E::ts('Rule Group ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Dedupe Rule Group'),
      'entity_reference' => [
        'entity' => 'DedupeRuleGroup',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'scan_limit' => [
      'title' => E::ts('Scan Limit'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => E::ts('maximum number of contacts to scan with this rule ()'),
    ],
    'min_cid' => [
      'title' => E::ts('Min Cid'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => E::ts('minimum contact ID to scan with this rule (1 + max cid from previous scan)'),
      'default' => 0,
    ],
    'limit_group_id' => [
      'title' => E::ts('Limit Group ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to civicrm_group.id (limit monitor to contacts in this group, if specified)'),
      'entity_reference' => [
        'entity' => 'Group',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'is_active' => [
      'title' => E::ts('Enabled'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'description' => E::ts('Is this monitor active?'),
      'default' => TRUE,
    ],
  ],
];
