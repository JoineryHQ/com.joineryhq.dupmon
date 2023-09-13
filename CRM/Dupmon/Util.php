<?php

class CRM_Dupmon_Util {

  /**
   * Get all configured rule monitors.
   *
   * @param boolean $isActive If provided, limit returned monitors based on is_active setting.
   * @return array
   */
  public static function getRuleMonitors($isActive = NULL) {
    $ruleMonitors = [];
    $apiParams = [
      'options' => [
        'limit' => 0,
      ],
      'sequential' => 1,
      'api.RuleGroup.get' => [],
    ];
    if (isset($isActive)) {
      $apiParams['is_active'] = (bool) $isActive;
    }
    $dupmonRuleMonitorGet = civicrm_api3('dupmonRuleMonitor', 'get', $apiParams);

    foreach ($dupmonRuleMonitorGet['values'] as $dupmonRuleMonitor) {
      $ruleMonitor = $dupmonRuleMonitor;
      unset($ruleMonitor['api.RuleGroup.get']);
      $ruleMonitor['contact_type'] = $dupmonRuleMonitor['api.RuleGroup.get']['values'][0]['contact_type'];
      $ruleMonitors[] = $ruleMonitor;
    }
    return $ruleMonitors;
  }

  public static function updateRuleMonitor($ruleMonitor, $limit, $scanCids) {
    $dupmonRuleMonitorParams = [
      'id' => $ruleMonitor['id'],
      'scan_limit' => $limit,
    ];
    $maxScanCid = (int) max($scanCids);
    // Check to see if we've reached the end of all contacts of this type.
    // (Remember, we're proceeding through all contacts, in order by contactId;
    // so we want to know if there's even 1 undeleted contacts of this type
    // with a greater contactId than the max contactId in our set of scanned contactIds.
    $remainingContactCount = civicrm_api3('Contact', 'getcount', [
      'id' => ['>' => $maxScanCid],
      'contact_type' => $ruleMonitor['contact_type'],
      'is_deleted' => 0,
    ]);
    if ($remainingContactCount) {
      // We've found remaining unscanned contats. Therefore set this rule's
      // min_cid to start immediately after this set of scanned cids.
      $minCid = ($maxScanCid + 1);
    }
    else {
      // We've found no remaining contacts, so we'll set this rule to start
      // again with the smalled-ID contacts.
      $minCid = 0;
    }
    $dupmonRuleMonitorParams['min_cid'] = $minCid;
    civicrm_api3('dupmonRuleMonitor', 'create', $dupmonRuleMonitorParams);
  }

  /**
   * Get a set of contacts to scan for the given ruleMonitor properties
   */
  public static function getScanContactList($contactType, $minCid = 0, $limit = 0, $limitGroupId = NULL) {
    if (!$limit) {
      $limit = self::getNextLimitQuantum();
    }
    // Use procedural api4 here, because we must conditionally add a WHERE based on
    // $limitGroupId; OOP api4 was not returning a countable
    // Civi\Api4\Generic\Result object unless ->execute() is oop-chained in the
    // initial api call, e.g. $contacts = \Civi\Api4\Contact::get(FALSE)->...->execute();
    $contactApiParams = [
      'select' => ['id'],
      'where' => [
        ['id', '>', $minCid],
        ['contact_type', '=', $contactType],
        ['is_deleted', '=', FALSE],
      ],
      'limit' => $limit,
      'checkPermissions' => FALSE,
    ];
    if ($limitGroupId) {
      $contactApiParams['where'][] = ['groups', 'IN', [$limitGroupId]];
    }
    $contacts = civicrm_api4('Contact', 'get', $contactApiParams);
    $cids = CRM_Utils_Array::collect('id', $contacts->getArrayCopy());
    sort($cids);
    return $cids;
  }

  public static function scanRule($rgid, $cids) {
    CRM_Dupmon_Util::debugLog("Scanning with rule $rgid, contact_count = " . count($cids), __FUNCTION__);
    $dbMaxQueryTimeVariableProps = self::getDbMaxQueryTimeVariableProps();

    $variableDao = CRM_Core_DAO::executeQuery("SHOW VARIABLES LIKE '%{$dbMaxQueryTimeVariableProps['name']}%'");
    $variableDao->fetch();
    $originalTimeLimit = $variableDao->Value;
    CRM_Dupmon_Util::debugLog("\$originalTimeLimit: $originalTimeLimit", __FUNCTION__);
    CRM_Core_DAO::executeQuery("SET SESSION {$dbMaxQueryTimeVariableProps['name']}={$dbMaxQueryTimeVariableProps['value']}");
    CRM_Dupmon_Util::debugLog("query: SET SESSION {$dbMaxQueryTimeVariableProps['name']}={$dbMaxQueryTimeVariableProps['value']}", __FUNCTION__);
    try {
      $dupes = CRM_Dedupe_Finder::dupes($rgid, $cids, FALSE);
    }
    catch (PEAR_Exception $e) {
      if ($e->getMessage() == "DB Error: unknown error") {
        CRM_Dupmon_Util::debugLog("Timed out", __FUNCTION__);
        CRM_Core_DAO::executeQuery("SET SESSION {$dbMaxQueryTimeVariableProps['name']}={$originalTimeLimit}");
        throw new CRM_Dupmon_Exception('Dedupe scan exceeded the congigured max query time.', 'TIMEOUT');
      }
      else {
        CRM_Dupmon_Util::debugLog("Other error: " . $e->getMessage(), __FUNCTION__);
        throw $e;
      }
    }
    CRM_Core_DAO::executeQuery("SET SESSION {$dbMaxQueryTimeVariableProps['name']}={$originalTimeLimit}");
    CRM_Dupmon_Util::debugLog("Found dupes: " . count($dupes), __FUNCTION__);
    return $dupes;
  }

  public static function getLimitQuanta() {
    return array(
      100000,
      50000,
      10000,
      5000,
      2500,
      1000,
      500,
      100,
      50,
      10,
      1,
    );
  }

  /**
   * Return the next available limit quantum which is less than the given quantum.
   * @param type $currentQuantum
   * @return Int
   */
  public static function getNextLimitQuantum($currentQuantum = NULL) {
    $quanta = self::getLimitQuanta();
    rsort($quanta);
    if ($currentQuantum) {
      foreach ($quanta as $quantum) {
        if ($quantum < $currentQuantum) {
          return $quantum;
        }
      }
      // As a last resort, return limit of "0".
      return 0;
    }
    else {
      return max($quanta);
    }
  }

  public static function getDbMaxQueryTimeVariableProps() {
    $props = array(
      'name' => '',
      'value' => '',
    );
    $dupmon_max_query_time = Civi::settings()->get('dupmon_max_query_time');
    $version = CRM_Utils_SQL::getDatabaseVersion();
    if (stripos($version, 'mariadb') !== FALSE) {
      // MariaDB variable has a certain name, and value is in seconds.
      $props['name'] = 'MAX_STATEMENT_TIME';
      $props['value'] = $dupmon_max_query_time;
    }
    else {
      // MySQL variable has a differnt name, and value is in milliseconds.
      $props['name'] = 'MAX_EXECUTION_TIME';
      $props['value'] = ($dupmon_max_query_time * 1000);
    }

    return $props;
  }

  /**
   * For a given collection of duplicate candidates, batch them into reviewable dupmonBatch entities.
   *
   * @param Array $dupes
   * @param Array $scannedCids
   * @param Int $ruleId
   * @param Int $batchSize
   * @return Int Count of batches created
   */
  public static function createBatches($dupes, $scannedCids, $ruleId, $batchSize = NULL) {
    $batchesCreatedCount = 0;

    if (is_null($batchSize)) {
      $batchSize = self::getNextLimitQuantum();
    }
    // Reduce $batchSize if it's larger than the configured maximum.
    $maxBatchSize = Civi::settings()->get('dupmon_max_batch_size');
    $originaBatchSize = $batchSize;
    $batchSize = min([
      $originaBatchSize,
      $maxBatchSize,
    ]);
    CRM_Dupmon_Util::debugLog("batch size is $batchSize (smaller of $originaBatchSize, $maxBatchSize)", __FUNCTION__);

    $allDupeCids = array_unique(
      // TODO: this could be a place to optimize; in testing, larger arrays of $dupes,
      // merging all of this at once seems to create memory problems on some systems.
      array_merge(
        CRM_Utils_Array::collect(0, $dupes),
        CRM_Utils_Array::collect(1, $dupes)
      )
    );
    $rangeCids = array_intersect($allDupeCids, $scannedCids);
    $unbatchedCids = self::stripBatchedCids($rangeCids);
    if (empty($unbatchedCids)) {
      // All in-range duplicate cids are already in another batch, so we have
      // nothing to do.
      return $batchesCreatedCount;
    }
    CRM_Dupmon_Util::debugLog("unbatched cids count: " . count($unbatchedCids), __FUNCTION__);
    $cidBatches = array_chunk($unbatchedCids, $batchSize);

    foreach ($cidBatches as $cidBatch) {
      $dupmonBatchCreate = civicrm_api3('dupmonBatch', 'create', [
        'rule_group_id' => $ruleId,
        'sequential' => 1,
      ]);

      foreach ($cidBatch as $cid) {
        civicrm_api3('groupContact', 'create', [
          'group_id' => $dupmonBatchCreate['values'][0]['group_id'],
          'contact_id' => $cid,
        ]);
      }
      $batchesCreatedCount++;
    }
    return $batchesCreatedCount;
  }

  /**
   * Given an array of contact Ids, strip any that are already in other batches,
   * and return the rest.
   *
   * @param type $cids
   */
  public static function stripBatchedCids($cids) {
    $usedCids = [];
    $dupmonBatchGet = civicrm_api3('dupmonBatch', 'get', [
      'options' => [
        'limit' => 0,
      ],
    ]);
    foreach ($dupmonBatchGet['values'] as $dupmonBatch) {
      $groupContactGet = civicrm_api3('groupContact', 'get', [
        'group_id' => $dupmonBatch['group_id'],
        'options' => [
          'limit' => 0,
        ],
      ]);
      $groupContactIds = CRM_Utils_Array::collect('contact_id', $groupContactGet['values']);
      $usedCids = array_merge($usedCids, $groupContactIds);
    }
    return array_diff($cids, $usedCids);
  }

  public static function debugLog($message, $prefix = NULL) {
    if (!Civi::settings()->get('dupmon_debug_log')) {
      return;
    }
    if ($prefix) {
      $message = "{$prefix} :: $message";
    }
    // Write to a dedicated log file which will be managed by civicrm.
    CRM_Core_Error::debug_log_message($message, FALSE, 'dupmon');
  }

  /**
   * Remove any batches that no longer have group members.
   *
   * @return int Number of batches removed.
   */
  public static function cleanupEmptyBatches() {
    // Find the groupContact count of all groups tied to dupmonBatches.
    $dupmonBatchGet = civicrm_api3('DupmonBatch', 'get', [
      'sequential' => 1,
      'api.GroupContact.getcount' => ['group_id' => "\$value.group_id"],
      'options' => ['limit' => 0],
    ]);
    foreach ($dupmonBatchGet['values'] as $dupmonBatch) {
      // If the count is 0, delete the batch (this also deletes the Group).
      $count = $dupmonBatch['api.GroupContact.getcount'];
      if ($count == 0) {
        civicrm_api3('dupmonBatch', 'delete', [
          'id' => $dupmonBatch['id'],
        ]);
      }
    }
    // Find and delete any orphaned groups. E.g., if a rule_group is deleted,
    // the FK cascade will delete any dupmonBatches using that rule; this will
    // orphan any Group associated with that batch.
    // These groups will have name like 'DedupeMonitorBatch_%' and are hidden.
    // TODO: could probably handle this more cleanly with triggers.
    $dao = CRM_Core_DAO::executeQuery("
      SELECT g.id
      FROM
        civicrm_group g
        LEFT JOIN civicrm_dupmon_batch b ON b.group_id = g.id
      WHERE
        g.name LIKE 'DedupeMonitorBatch_%'
        AND g.is_hidden
        AND b.group_id IS NULL
    ");
    $cleanedCount = 0;
    while ($dao->fetch()) {
      CRM_Dupmon_Util::debugLog("About to delete group id = {$dao->id}", __FUNCTION__);
      civicrm_api3('group', 'delete', [
        'id' => $dao->id,
      ]);
      $cleanedCount++;
    }
    return $cleanedCount;
  }

  public static function getRuleHash($ruleGroupId) {
    $ruleGroup = civicrm_api3('ruleGroup', 'getSingle', [
      'id' => $ruleGroupId,
      'api.rule.get' => ['dedupe_rule_group_id' => '$value.id'],
    ]);
    foreach ($ruleGroup['api.rule.get']['values'] as &$rule) {
      // Strip IDs. These are changed with every ruleGroup save, but they are
      // not meaningful in the application of the rule, so we don't want them
      // polluting the hash.
      unset($rule['id']);
    }
    return hash('sha256', serialize($ruleGroup));
  }

  public static function updateRuleHash($ruleGroupId, $force = FALSE) {
    $newHash = CRM_Dupmon_Util::getRuleHash($ruleGroupId);
    $ruleInfo = civicrm_api3('dupmonRuleInfo', 'get', [
      'sequential' => 1,
      'rule_group_id' => $ruleGroupId,
    ]);
    if ($ruleInfo['count']) {
      $ruleInfoId = $ruleInfo['values'][0]['id'];
      $oldHash = $ruleInfo['values'][0]['hash'];
    }
    if (
      $force || ($oldHash != $newHash)
    ) {
      // Hash has changed. Update the stored hash.
      $ruleInfoCreate = civicrm_api3('dupmonRuleInfo', 'create', [
        'id' => $ruleInfoId,
        'hash' => $newHash,
        'rule_group_id' => $ruleGroupId,
      ]);

      // Delete any batches for this rule.
      $batchGet = civicrm_api3('dupmonBatch', 'get', [
        'rule_group_id' => $ruleGroupId,
        'options' => [
          'limit' => 0,
        ],
        'api.dupmonBatch.delete' => [],
      ]);

      // Nullify scan_limit for any monitors on this rule (because the rule
      // criteria may have changed, we might be able to run with a greater scan_limit
      // on next run.
      $ruleMonitorGet = civicrm_api3('dupmonRuleMonitor', 'get', [
        'rule_group_id' => $ruleGroupId,
      ]);
      foreach ($ruleMonitorGet['values'] as $ruleMonitor) {
        civicrm_api3('dupmonRuleMonitor', 'create', [
          'id' => $ruleMonitor['id'],
          'scan_limit' => 'null',
        ]);
      }
    }
  }

}
