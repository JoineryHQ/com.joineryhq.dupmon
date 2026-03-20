<?php
use CRM_Dupmon_ExtensionUtil as E;

return [
  'name' => 'DupmonRuleInfo',
  'table' => 'civicrm_dupmon_rule_info',
  'class' => 'CRM_Dupmon_DAO_DupmonRuleInfo',
  'getInfo' => fn() => [
    'title' => E::ts('Dupmon Rule Info'),
    'title_plural' => E::ts('Dupmon Rule Infos'),
    'description' => E::ts('Relevant info for dedupe rule groups'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique DupmonRuleInfo ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
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
    'hash' => [
      'title' => E::ts('Hash'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('hash of rule configuration'),
    ],
  ],
];
