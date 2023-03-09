<?php
// phpcs:disable
use CRM_Dupmon_ExtensionUtil as E;
// phpcs:enable

class CRM_Dupmon_BAO_DupmonRuleInfo extends CRM_Dupmon_DAO_DupmonRuleInfo {

  /**
   * Create a new DupmonRuleInfo based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Dupmon_DAO_DupmonRuleInfo|NULL
   */
  /*
  public static function create($params) {
    $className = 'CRM_Dupmon_DAO_DupmonRuleInfo';
    $entityName = 'DupmonRuleInfo';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }
  */

}
