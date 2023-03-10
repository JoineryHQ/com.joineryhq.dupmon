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
  public static function create($params) {
    $className = 'CRM_Dupmon_DAO_DupmonBatch';
    $entityName = 'DupmonBatch';
    $hook = empty($params['id']) ? 'create' : 'edit';

    if (empty($params['id'])) {
      // If we're creating a batch, we must create a group for it.
      $groupCreate = civicrm_api3('group', 'create', [
        'is_hidden' => TRUE,
        'title' => 'DedupeMonitorBatch_' . uniqid(),
      ]);
      $groupId = $groupCreate['id'];
      CRM_Dupmon_Util::debugLog("creating batch (group_id: $groupId)", __METHOD__);
      $params['group_id'] = $groupId;
    }

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  public static function del($id) {
    // Instead of deleting the dupmonBatch, delete its associated Group; FK cascade
    // will delete the dupmonBatch, and the group itself has no value without
    // the dupmonBatch.
    $className = 'CRM_Dupmon_DAO_DupmonBatch';
    $instance = new $className();
    $instance->id = $id;
    $instance->find();
    $instance->fetch();
    $groupId = $instance->group_id;
    if (!$groupId) {
      return FALSE;
    }
    civicrm_api3('group', 'delete', [
      'id' => $groupId,
    ]);
    return $instance;
  }

}
