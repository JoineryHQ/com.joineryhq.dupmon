<?php
// phpcs:disable
use CRM_Dupmon_ExtensionUtil as E;
// phpcs:enable

class CRM_Dupmon_BAO_DupmonBatch extends CRM_Dupmon_DAO_DupmonBatch {

  /**
   * Create a new DupmonBatch based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Dupmon_DAO_DupmonBatch|NULL
   */
  /*
  public static function create($params) {
    $className = 'CRM_Dupmon_DAO_DupmonBatch';
    $entityName = 'DupmonBatch';
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
