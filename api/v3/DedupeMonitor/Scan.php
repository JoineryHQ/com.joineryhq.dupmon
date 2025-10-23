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
 * @throws CRM_Core_Exception
 */
function civicrm_api3_dedupe_monitor_Scan($params) {
  // Populate batches
  $ruleMonitors = CRM_Dupmon_Util::getRuleMonitors(TRUE);
  $ruleMonitorsProcessed = [];
  $batchesCreatedCount = 0;
  foreach ($ruleMonitors as $ruleMonitor) {
    $ruleCompleted = FALSE;
    $limit = ($ruleMonitor['scan_limit'] ?? NULL);
    $debugLimitGroupId = ($ruleMonitor['limit_group_id'] ?? 'null');
    $debugMonitorDescriptor = "id={$ruleMonitor['id']}, rule_group_id={$ruleMonitor['rule_group_id']}, limit_group_id={$debugLimitGroupId}";
    CRM_Dupmon_Util::debugLog("START processing monitor: $debugMonitorDescriptor", __FUNCTION__);
    CRM_Dupmon_Util::debugLog("Starting with limit: " . ($ruleMonitor['scan_limit'] ?? '(NONE)'), __FUNCTION__);
    if (!$limit) {
      $limit = CRM_Dupmon_Util::getNextLimitQuantum();
      CRM_Dupmon_Util::debugLog("Limit was empty; got next limit: $limit", __FUNCTION__);
    }
    while (!$ruleCompleted) {
      // Initialize $dupes as an empty array.
      $dupes = [];
      $cids = CRM_Dupmon_Util::getScanContactList($ruleMonitor['contact_type'], $ruleMonitor['min_cid'], $limit, ($ruleMonitor['limit_group_id'] ?? NULL));
      if (!empty($cids)) {
        try {
          $dupes = CRM_Dupmon_Util::scanRule($ruleMonitor['rule_group_id'], $cids);
          $ruleCompleted = TRUE;
        }
        catch (CRM_Dupmon_Exception $e) {
          CRM_Dupmon_Util::debugLog("trying to get next quantum for current limit = $limit", __FUNCTION__);
          $limit = CRM_Dupmon_Util::getNextLimitQuantum($limit);
          CRM_Dupmon_Util::debugLog("got next quantum limit: $limit", __FUNCTION__);
          if ($limit == 0) {
            CRM_Dupmon_Util::debugLog("Reached quantum of '0'; cannot continue further with this monitor.", __FUNCTION__);
            break;
          }
        }
      }
      else {
        CRM_Dupmon_Util::debugLog("Zero cids found for this monitor. Will not attempt to scan.", __FUNCTION__);
        $ruleCompleted = TRUE;
      }
    }

    CRM_Dupmon_Util::updateRuleMonitor($ruleMonitor, $limit, $cids);

    // If any dupes were found, process them into batches:
    if (!empty($dupes)) {
      $batchesCreatedCount += CRM_Dupmon_Util::createBatches($dupes, $cids, $ruleMonitor['rule_group_id'], $limit);
    }
    $ruleMonitorsProcessed[] = [$ruleMonitor['id'] => count($dupes) . " dupes"];
    CRM_Dupmon_Util::debugLog("END processing monitor: $debugMonitorDescriptor", __FUNCTION__);
  }

  // Cleanup any empty batches that may be hanging around (e.g. if all of the
  // batch contacts have been deleted by deduping or other means).
  $cleanedEmptyCount = CRM_Dupmon_Util::cleanupEmptyBatches();
  // Clean any batches that have zero actual dedupe matches.
  $cleanedDupelessCount = CRM_Dupmon_Util::cleanupDupelessBatches();

  $returnValues = [
    'Rule Monitors Processed' => $ruleMonitorsProcessed,
    'New Batches Created' => $batchesCreatedCount,
    'Empty Batches Removed' => $cleanedEmptyCount,
    'Dupeless Batches Removed' => $cleanedDupelessCount,
  ];
  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'DedupeMonitor', 'Scan');
}
