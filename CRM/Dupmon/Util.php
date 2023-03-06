<?php

class CRM_Dupmon_Util {
  /**
   * Get all configured rule monitors.
   * FIXME: STUB.
   */
  public static function getRuleMonitors() {
    return [
      [
        'rule_id' => 4,
        'limit' => NULL,
        'minimum_cid' => 0,
        'contact_type' => 'individual',
      ]
    ];
  }

  /**
   * Get a set of contacts to scan for the given ruleMonitor properties
   */
  public static function getScanContactList($contactType, $minCid = 0, $limit = 0) {
    if (!$limit) {
      $limit = self::getNextLimitQuanta();
    }
    $queryParams = [
      1 => [$contactType, 'String'],
      2 => [$minCid, 'Int'],
      3 => [$limit, 'Int'],
    ];
    $query = "SELECT id FROM civicrm_contact WHERE NOT is_deleted AND contact_type = %1 AND id > %2 LIMIT %3";
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    $rows = $dao->fetchAll();
    $cids = CRM_Utils_Array::collect('id', $rows);
    sort($cids);
    return $cids;
  }

  public static function scanRule($rgid, $cids) {
    echo "Scanning rule $rgid against ". count($cids) . " contacts\n";
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
  public static function getNextLimitQuanta($currentQuantum = NULL) {
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

  /**
   * Update a given rule monitor with the given parameters.
   * FIXME: STUB.
   * @param type $rgid
   * @param type $params
   */
  public static function updateRuleMonitor($rgid, $params) {

  }

  public static function createBatches($dupes, $scannedCids, $batchSize = NULL) {
    if (is_null($batchSize)) {
      $batchSize = self::getNextLimitQuanta();
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
    // FIXME: TODO; STUB.
    $usedCids = [207];
    return array_diff($cids, $usedCids);
  }
}