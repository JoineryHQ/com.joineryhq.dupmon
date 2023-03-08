<?php
use CRM_Dupmon_ExtensionUtil as E;

class CRM_Dupmon_Page_DupmonAlert extends CRM_Core_Page {

  public function run() {
    // Clean up any empty batches.
    CRM_Dupmon_Util::cleanupEmptyBatches();

    // If any batches remain, print an alert.
    $dupmonBatchGet = civicrm_api3('DupmonBatch', 'get', [
      'sequential' => 1,
      'api.GroupContact.getcount' => ['group_id' => "\$value.group_id"],
      'options' => ['limit' => 0],
    ]);
    if ($dupmonBatchGet['count']) {
      $batchCount = $dupmonBatchGet['count'];
      $totalBatchSize = 0;
      foreach ($dupmonBatchGet['values'] as $dupmonBatch) {
        $totalBatchSize += $dupmonBatch['api.GroupContact.getcount'];
      }
      $alert = E::ts('Dedupe Monitor has duplicates to review: %1 batches, covering %2 scanned contacts.', [
        '1' => $batchCount,
        '2' => $totalBatchSize,
      ]);
      $url = CRM_Utils_System::url('civicrm/admin/dupmon/batches', 'reset=1', FALSE, NULL, FALSE, FALSE, TRUE);
      $this->assign('reviewUrl', $url);
      $this->assign('alert', $alert);
    }


    parent::run();
  }

}
