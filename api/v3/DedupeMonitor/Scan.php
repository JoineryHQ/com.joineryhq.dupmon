<?php
use CRM_Dupmon_ExtensionUtil as E;

/**
 * DedupeMonitor.Scan API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_dedupe_monitor_Scan_spec(&$spec) {
  // No params.
}

/**
 * DedupeMonitor.Scan API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_dedupe_monitor_Scan($params) {
  // Populate batches
  $ruleMonitors = CRM_Dupmon_Util::getRuleMonitors();
  foreach ($ruleMonitors as $ruleMonitor) {
    $ruleCompleted = FALSE;
    $limit = $ruleMonitor['scan_limit'];
    CRM_Dupmon_Util::debugLog(__FUNCTION__ . " :: Starting with limit: $limit");
    if (!$limit) {
      $limit = CRM_Dupmon_Util::getNextLimitQuantum();
      CRM_Dupmon_Util::debugLog(__FUNCTION__ . " :: Limit was empty; got next limit: $limit");
    }
    while (!$ruleCompleted) {
      $cids = CRM_Dupmon_Util::getScanContactList($ruleMonitor['contact_type'], $ruleMonitor['min_cid'], $limit);
      try {
        $dupes = CRM_Dupmon_Util::scanRule($ruleMonitor['rule_group_id'], $cids);
        $ruleCompleted = TRUE;
      } catch (CRM_Dupmon_Exception $e) {
        CRM_Dupmon_Util::debugLog(__FUNCTION__ . " :: trying to get next quantum for current limit = $limit");
        $limit = CRM_Dupmon_Util::getNextLimitQuantum($limit);
        CRM_Dupmon_Util::debugLog(__FUNCTION__ . " :: got next quantum limit: $limit");
      }
    }
    
    CRM_Dupmon_Util::updateRuleMonitor($ruleMonitor, $limit, $cids);
    
    // If any dupes were found, process them into batches:
    if (!empty($dupes)) {
      CRM_Dupmon_Util::createBatches($dupes, $cids, $ruleMonitor['rule_group_id'], $limit);
    }
  }

  // Cleanup any empty batches that may be hanging around (e.g. if all of the
  // batch contacts have been deleted by deduping or other means).
  CRM_Dupmon_Util::cleanupEmptyBatches();
  
  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'DedupeMonitor', 'Scan');
}
