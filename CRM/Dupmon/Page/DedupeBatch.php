<?php
use CRM_Dupmon_ExtensionUtil as E;

class CRM_Dupmon_Page_DedupeBatch extends CRM_Core_Page {

  public function run() {
    // This page does not display anything on its own. It will redirect to an
    // appropriate URL depending on the state of the batch.

    // Get the batchId from the request.
    $batchId = CRM_Utils_Request::retrieve('id', 'Int', $this, TRUE);

    if (CRM_Dupmon_Util::getBatchMatchesCount($batchId)) {
      // If we have some actual current matches, we'll redirect to the native san results.

      $dupmonBatch = civicrm_api3('DupmonBatch', 'getsingle', [
        'sequential' => 1,
        'id' => $batchId,
      ]);

      // Set redirect to native scan results for rgid/gid:
      $path = 'civicrm/contact/dedupefind';
      $query = [
        'reset' => 1,
        'action' => 'update',
        'rgid' => $dupmonBatch['rule_group_id'],
        'gid' => $dupmonBatch['group_id'],
      ];
      $redirect = CRM_Utils_System::url($path, $query);
    }
    else {
      // Otherwise, we'll clean up the empty batch and redirect to our batches list.

      // Explain to the user that the batch is empty.
      $message = E::ts('No possible duplicates were found using this batch. This usually means that the duplicates were already merged some time after the batch was created. This batch, being effectively empty, has now been forgotten.');
      $title = E::ts('No duplicates remain in this batch.');
      CRM_Core_Session::setStatus($message, $title, 'info', ['expires' => 0]);

      // Delete (forget) the batch.
      civicrm_api3('dupmonBatch', 'delete', [
        'id' => $batchId,
      ]);
      CRM_Dupmon_Util::debugLog('Auto-forgot empty batch, id=' . $batchId, __METHOD__);

      // Set redirect to our batches list.
      $redirect = CRM_Utils_System::url('civicrm/admin/dupmon/batches', 'reset=1');
    }
    CRM_Utils_System::redirect($redirect);

    parent::run();
  }

}
