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
      CRM_Dupmon_Util::updateRuleHash($params['rule_group_id']);
    }

    return $instance;
  }


}
