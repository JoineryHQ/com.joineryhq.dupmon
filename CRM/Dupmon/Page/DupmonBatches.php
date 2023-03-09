<?php
use CRM_Dupmon_ExtensionUtil as E;

class CRM_Dupmon_Page_DupmonBatches extends CRM_Core_Page {

  public function run() {
    // Clean up any empty batches.
    CRM_Dupmon_Util::cleanupEmptyBatches();

    // If any batches remain, compile them for tabular review.
    $dupmonBatchGet = civicrm_api3('DupmonBatch', 'get', [
      'sequential' => 1,
      'api.GroupContact.getcount' => ['group_id' => "\$value.group_id"],
      'api.RuleGroup.get' => ['id' => "\$value.rule_group_id", 'return' => ['title', 'contact_type']],
      'options' => ['limit' => 0],
    ]);
    if ($dupmonBatchGet['count']) {
      foreach ($dupmonBatchGet['values'] as $dupmonBatch) {
        $dupmonBatch['size'] = $dupmonBatch['api.GroupContact.getcount'];
        $dupmonBatch['rule_title'] = $dupmonBatch['api.RuleGroup.get']['values'][0]['title'];
        $dupmonBatch['rule_contact_type'] = $dupmonBatch['api.RuleGroup.get']['values'][0]['contact_type'];
        $rows[] = $dupmonBatch;
      }
      $this->assign('rows', $rows);
    }

    parent::run();
  }

}
