<?php
use CRM_Dupmon_ExtensionUtil as E;

/**
 * DupmonBatch.create API specification (optional).
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_dupmon_batch_create_spec(&$spec) {
  $spec['rule_group_id']['api.required'] = 1;

  // NOTE: The group_id column is required in SQL, but we do not require a group_id
  // param in this api. Instead, we will create a group in the api 'create' action,
  // and use that group_id.
  $spec['group_id']['api.required'] = 0;
}

/**
 * DupmonBatch.create API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws CRM_Core_Exception
 */
function civicrm_api3_dupmon_batch_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'DupmonBatch');
}

/**
 * DupmonBatch.delete API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws CRM_Core_Exception
 */
function civicrm_api3_dupmon_batch_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * DupmonBatch.get API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws CRM_Core_Exception
 */
function civicrm_api3_dupmon_batch_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, TRUE, 'DupmonBatch');
}
