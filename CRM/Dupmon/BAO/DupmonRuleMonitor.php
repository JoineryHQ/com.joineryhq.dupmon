<?php
// phpcs:disable
use CRM_Dupmon_ExtensionUtil as E;
// phpcs:enable

class CRM_Dupmon_BAO_DupmonRuleMonitor extends CRM_Dupmon_DAO_DupmonRuleMonitor {

  /**
   * Create a new DupmonRuleMonitor based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Dupmon_DAO_DupmonRuleMonitor|NULL
   */
  
  public static function create($params) {
    $className = 'CRM_Dupmon_DAO_DupmonRuleMonitor';
    $entityName = 'DupmonRuleMonitor';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    if (empty($params['id'])) {
      // If we're creating a monitor, get the hash of the rule and store that in ruleInfo.
      $hash = CRM_Dupmon_Util::getRuleHash($params['rule_group_id']);
      $ruleInfo = civicrm_api3('dupmonRuleInfo', 'get', [
        'sequential' => 1,
        'rule_group_id' => $params['rule_group_id'],
      ]);
      if ($ruleInfo['count']) {
        $ruleInfoId = $ruleInfo['values'][0]['id'];
      }
      $ruleInfoCreate = civicrm_api3('dupmonRuleInfo', 'create', [
        'id' => $ruleInfoId,
        'hash' => $hash,
        'rule_group_id' => $params['rule_group_id'],
      ]);
    }

    return $instance;
  }
  

}
