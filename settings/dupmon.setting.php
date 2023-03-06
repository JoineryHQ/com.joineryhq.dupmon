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
    'type' => 'Int',
    'quick_form_type' => 'Element',
    'default' => 5,
  ),
  'dupmon_debug_log' => array(
    'group_name' => 'Dedupe Monitor Settings',
    'group' => 'dupmon',
    'name' => 'dupmon_debug_log',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'If true, log messages to ConfigAndLog/dedupmon.log.txt',
    'title' => E::ts('Log debug messages to file?'),
    'type' => 'Bool',
    'quick_form_type' => 'Element',
    'default' => FALSE,
  ),
);
