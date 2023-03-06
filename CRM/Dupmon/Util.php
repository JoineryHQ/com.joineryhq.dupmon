<?php

class CRM_Dupmon_Util {
  /**
   * Get all configured rule monitors.
   * FIXME: STUB.
   */
  public static function getRuleMonitors() {
    $ruleMonitors = [];
    $dupmonRuleMonitorGet = civicrm_api3('dupmonRuleMonitor', 'get', [
      'options' => [
        'limit' => 0,
      ],
      'sequential' => 1,
      'api.RuleGroup.get' => [],
    ]);

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
    $maxScanCid = (int)max($scanCids);
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
  public static function getScanContactList($contactType, $minCid = 0, $limit = 0) {
    if (!$limit) {
      $limit = self::getNextLimitQuantum();
    }
    $queryParams = [
      1 => [$contactType, 'String'],
      2 => [$minCid, 'Int'],
      3 => [$limit, 'Int'],
    ];
    $query = "SELECT id FROM civicrm_contact WHERE NOT is_deleted AND contact_type = %1 AND id > %2 ORDER BY id LIMIT %3";
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    $rows = $dao->fetchAll();
    $cids = CRM_Utils_Array::collect('id', $rows);
    sort($cids);
    return $cids;
  }

  public static function scanRule($rgid, $cids) {
    $dbMaxQueryTimeVariableProps = self::getDbMaxQueryTimeVariableProps();

    $variableDao = CRM_Core_DAO::executeQuery("SHOW VARIABLES LIKE '%{$dbMaxQueryTimeVariableProps['name']}%'");
    $variableDao->fetch();
    $originalTimeLimit = $variableDao->Value;
    CRM_Core_DAO::executeQuery("SET SESSION {$dbMaxQueryTimeVariableProps['name']}={$dbMaxQueryTimeVariableProps['value']}");
    try {
      $dupes = CRM_Dedupe_Finder::dupes($rgid, $cids, FALSE);
    }
    catch (PEAR_Exception $e) {
      if ($e->getMessage() == "DB Error: unknown error") {
        throw new CRM_Dupmon_Exception('Dedupe scan exceeded the congigured max query time.', 'TIMEOUT');
      }
      else {
        throw $e;
      }
    }
    CRM_Core_DAO::executeQuery("SET SESSION {$dbMaxQueryTimeVariableProps['name']}={$originalTimeLimit}");
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
        // As a last resort, return limit of "0".
        return 0;
      }
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

  public static function createBatches($dupes, $scannedCids, $ruleId, $batchSize = NULL) {
    if (is_null($batchSize)) {
      $batchSize = self::getNextLimitQuantum();
    }
    $allDupeCids = array_unique(
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
      return;
    }
    $cidBatches = array_chunk($rangeCids, $batchSize);
    foreach ($cidBatches as $cidBatch) {
      $groupCreate = civicrm_api3('group', 'create', [
        'is_hidden' => TRUE,
        'title' => 'DedupeMonitorBatch '. uniqid(),
      ]);
      $groupId = $groupCreate['id'];

      // FIXME: TODO: create batchGroup entity with group id.
      civicrm_api3('dupmonBatch', 'create', [
        'group_id' => $groupId,
        'rule_group_id' => $ruleId,
      ]);

      foreach ($cidBatch as $cid) {
        civicrm_api3('groupContact', 'create', [
          'group_id' => $groupId,
          'contact_id' => $cid,
        ]);
      }
    }
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
}