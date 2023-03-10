<?php

use CRM_Dupmon_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Dupmon_Form_DeleteBatch extends CRM_Core_Form {
  protected $_batchId;

  public function preProcess() {
    // Set context (for redirection on cancel)
    $url = CRM_Utils_System::url('civicrm/admin/dupmon/batches', 'reset=1');
    $session = CRM_Core_Session::singleton();
    $session->replaceUserContext($url);
  }

  public function buildQuickForm() {
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Forget batch'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ),
    ));

    // Get batch info for user information.
    $this->_batchId = CRM_Utils_Request::retrieve('id', 'Int', $this, TRUE);

    $dupmonBatch = civicrm_api3('DupmonBatch', 'getSingle', [
      'id' => $this->_batchId,
      'api.RuleGroup.get' => ['id' => "\$value.rule_group_id", 'return' => ['title', 'contact_type']],
    ]);
    $dupmonBatch['rule_title'] = $dupmonBatch['api.RuleGroup.get']['values'][0]['title'];
    $dupmonBatch['rule_contact_type'] = $dupmonBatch['api.RuleGroup.get']['values'][0]['contact_type'];
    $this->assign('dupmonBatch', $dupmonBatch);
    parent::buildQuickForm();
  }

  public function postProcess() {
    civicrm_api3('dupmonBatch', 'delete', [
      'id' => $this->_batchId,
    ]);
    $statusMessage = E::ts('Batch %1 has been forgotten. No contact data was changed.', [
      '1' => $this->_batchId,
    ]);
    CRM_Core_Session::setStatus($statusMessage, E::ts('Batch forgotten'), 'success');

    $session = CRM_Core_Session::singleton();
    $redirect = $session->popUserContext();
    CRM_Utils_System::redirect($redirect);

    parent::postProcess();
  }

}
