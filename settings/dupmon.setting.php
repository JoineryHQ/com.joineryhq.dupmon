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
    'description' => E::ts('Maximum time (in seconds) before aborting a Dedupe Monitor scan of any given dedupe rule.'),
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
  'dupmon_max_batch_size' => array(
    'group_name' => 'Dedupe Monitor Settings',
    'group' => 'dupmon',
    'name' => 'dupmon_max_batch_size',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Maximum size of a scanned batch, up to %1; you may need to reduce this if the "Dedupe" link for a large batch is dying before the page can load.', [1 => CRM_Dupmon_Util::getNextLimitQuantum()]),
    'title' => E::ts('Maximum Batch Size'),
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'default' => CRM_Dupmon_Util::getNextLimitQuantum(),
    'html_type' => 'text',
    'formRules' => [
      'positiveInteger' => E::ts('%1 must be a positive integer', [1 => 'Maximum Batch Size']),
    ],
  ),
  'dupmon_debug_log' => array(
    'group_name' => 'Dedupe Monitor Settings',
    'group' => 'dupmon',
    'name' => 'dupmon_debug_log',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('If yes, log some debug output to ConfigAndLog/dupmon.log.txt'),
    'title' => E::ts('Log debug messages to file?'),
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => 0,
    'html_type' => '',
  ),
);
