<?php

use CRM_Dupmon_ExtensionUtil as E;

return array(
  'dupmon_max_query_time' => array(
    'group_name' => 'Dedupe Monitor Settings',
    'group' => 'dupmon',
    'name' => 'dupmon_max_query_time',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Maxiumum time (in seconds) before aborting a Dedupe Monitor scan of any given dedupe rule.',
    'title' => E::ts('Maximum Rule Scan Time'),
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'default' => 5,
    'html_type' => 'text',
    'formRules' => [
      'required' => E::ts('%1 is a required field', [1 => 'Maximum Rule Scan Time']),
      'positiveInteger' => E::ts('%1 must be a positive integer', [1 => 'Maximum Rule Scan Time']),
    ],
  ),
  'dupmon_debug_log' => array(
    'group_name' => 'Dedupe Monitor Settings',
    'group' => 'dupmon',
    'name' => 'dupmon_debug_log',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'If yes, log some debug output to ConfigAndLog/dedupmon.log.txt',
    'title' => E::ts('Log debug messages to file?'),
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => 0,
    'html_type' => '',
  ),
);
