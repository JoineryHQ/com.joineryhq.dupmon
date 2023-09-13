<?php

require_once 'dupmon.civix.php';

// phpcs:disable
use CRM_Dupmon_ExtensionUtil as E;

// phpcs:enable

function dupmon_civicrm_buildForm($formName, $form) {
  if ($formName == 'CRM_Contact_Form_DedupeFind' && ($form->_action == CRM_Core_Action::PREVIEW)) {
    // Add a warning to the top of thE "use rule" form.
    $batchesUrl = CRM_Utils_System::url('civicrm/admin/dupmon/batches', 'reset=1', TRUE);
    $template = '
      <div class="clear"></div>
      {crmButton href="' . $batchesUrl . '" class="edit" title="View Scanned Batches" icon=fa-rocket}{ts}View Scanned Batches{/ts}{/crmButton}
      <div class="clear"></div>
    ';
    // Use smarty 'eval' plugin directly in PHP to parse string as template (so
    // that we can use crmButton to make our button).
    $smarty = CRM_Core_Smarty::singleton();
    require_once $smarty->_get_plugin_filepath('function', 'eval');
    $compiled = smarty_function_eval(['var' => $template], $smarty);

    CRM_Core_Session::setStatus(E::ts('This operation can slow or freeze your site. Please consider using the Dedupe Monitor Scanned Batches instead.') . $compiled, E::ts('Wait!'), 'no-popup');
  }
}

function dupmon_civicrm_postProcess($formName, $form) {
  if ($formName == 'CRM_Contact_Form_DedupeRules') {
    if ($ruleGroupId = $form->getVar('_rgid')) {
      CRM_Dupmon_Util::updateRuleHash($ruleGroupId);
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function dupmon_civicrm_config(&$config) {
  _dupmon_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function dupmon_civicrm_install(): void {
  _dupmon_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function dupmon_civicrm_postInstall(): void {
  _dupmon_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function dupmon_civicrm_uninstall(): void {
  _dupmon_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function dupmon_civicrm_enable(): void {
  _dupmon_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function dupmon_civicrm_disable(): void {
  _dupmon_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function dupmon_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _dupmon_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function dupmon_civicrm_entityTypes(&$entityTypes): void {
  _dupmon_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function dupmon_civicrm_preProcess($formName, &$form): void {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function dupmon_civicrm_navigationMenu(&$menu) {
  $items = [];
  $items[] = [
    'parent' => 'Contacts/Find and Merge Duplicate Contacts',
    'properties' => [
      'label' => E::ts('Dedupe Monitor'),
      'name' => 'Dedupe Monitor',
      'url' => NULL,
      'permission' => 'merge duplicate contacts',
      'operator' => 'AND',
      'separator' => NULL,
    ],
  ];
  $items[] = [
    'parent' => 'Contacts/Find and Merge Duplicate Contacts/Dedupe Monitor',
    'properties' => [
      'label' => E::ts('Scanned Batches'),
      'name' => 'Scanned Batches',
      'url' => CRM_Utils_System::url('civicrm/admin/dupmon/batches', 'reset=1', TRUE),
      'permission' => 'merge duplicate contacts',
      'operator' => 'AND',
      'separator' => NULL,
      'icon' => 'crm-i fa-rocket',
    ],
  ];
  $items[] = [
    'parent' => 'Contacts/Find and Merge Duplicate Contacts/Dedupe Monitor',
    'properties' => [
      'label' => E::ts('Settings'),
      'name' => 'Settings',
      'url' => CRM_Utils_System::url('civicrm/admin/dupmon/settings', 'reset=1', TRUE),
      'permission' => 'merge duplicate contacts',
      'operator' => 'AND',
      'separator' => NULL,
      'icon' => 'crm-i fa-gear',
    ],
  ];
  foreach ($items as $item) {
    _dupmon_civix_insert_navigation_menu($menu, $item['parent'], $item['properties']);
  }
  _dupmon_civix_navigationMenu($menu);
}
