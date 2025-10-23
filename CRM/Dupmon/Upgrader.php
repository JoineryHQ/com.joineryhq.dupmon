<?php

// phpcs:disable
use CRM_Dupmon_ExtensionUtil as E;

// phpcs:enable

/**
 * Collection of upgrade steps.
 */
class CRM_Dupmon_Upgrader extends CRM_Extension_Upgrader_Base {
  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   *
   * Note that if a file is present sql\auto_install that will run regardless of this hook.
   */
  // public function install(): void {
  //   $this->executeSqlFile('sql/my_install.sql');
  // }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   */
  public function postInstall(): void {
    // Create ruleMonitors for all non-supervised dedupe rules (supervised
    // rules are likely to generate many false-positives, so we don't
    // monitor them by default.
    $result = civicrm_api3('RuleGroup', 'get', [
      'sequential' => 1,
      'used' => ['!=' => "Supervised"],
      'options' => ['limit' => 0],
      'api.DupmonRuleMonitor.create' => ['rule_group_id' => "\$value.id"],
    ]);
    // Create a dashlet for scan alerts
    $sql = "SELECT count(*) FROM civicrm_dashboard WHERE name='dupmonAlert'";
    $res = CRM_Core_DAO::singleValueQuery($sql);
    if ($res <= 0) {
      $sqlParams = [
        '1' => [CRM_Core_Config::domainID(), 'String'],
      ];
      $sql = "  
          INSERT INTO `civicrm_dashboard` (
            `domain_id`, `name`, `label`, `url`, `permission`, `permission_operator`, `fullscreen_url`, `is_active`, `is_reserved`, `cache_minutes`, `directive`
          )
          VALUES (
            %1, 'dupmonAlert', 'Dedupe Monitor Alert', 'civicrm/dupmon/alert?reset=1', 'merge duplicate contacts', NULL, 'civicrm/dupmon/alert?reset=1&context=dashletFullscreen', '1', '1', '60', NULL
          )
        ";
      CRM_Core_DAO::executeQuery($sql, $sqlParams);
    }
  }

  /**
   * Uninstall actions.
   * Note that if a file is present sql\auto_uninstall that will run regardless of this hook.
   */
  public function uninstall(): void {
    // Delete all dupmonBatch Groups.
    $this->executeSql("DELETE FROM civicrm_group WHERE name LIKE 'DedupeMonitorBatch_%' AND is_hidden");
    // Delete dupmonAlert dashlet
    $dashboardGet = civicrm_api3('dashboard', 'get', [
      'name' => 'dupmonAlert',
    ]);
    foreach ($dashboardGet['values'] as $dashboard) {
      $dashboardGet = civicrm_api3('dashboard', 'delete', [
        'id' => $dashboard['id'],
      ]);
    }
  }

  /**
   * Example: Run a simple query when a module is enabled.
   */
  // public function enable(): void {
  //  CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  // public function disable(): void {
  //   CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  // }

  /**
   * Modify civicrm_dupmon_rule_monitor table with new columns.
   *
   * @return TRUE on success
   */
  public function upgrade_0001(): bool {
    $this->ctx->log->info('Applying update 0001');
    CRM_Core_DAO::executeQuery("
      ALTER TABLE civicrm_dupmon_rule_monitor
      ADD limit_group_id int unsigned COMMENT 'FK to civicrm_group.id (limit monitor to contacts in this group, if specified)',
      ADD `is_active` tinyint DEFAULT 1 COMMENT 'Is this monitor active?',
      ADD FOREIGN KEY (limit_group_id) REFERENCES civicrm_group (id) ON DELETE SET NULL
    ");
    return TRUE;
  }

  /**
   * Create unique index for civicrm_dupmon_rule_monitor.rule_group_id
   *
   * @return TRUE on success
   */
  public function upgrade_0002(): bool {
    $this->ctx->log->info('Applying update 0003');
    CRM_Core_DAO::executeQuery("
      ALTER TABLE civicrm_dupmon_rule_monitor
      ADD UNIQUE INDEX `rule_group_id`(rule_group_id)
    ");
    return TRUE;
  }

  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   */
  // public function upgrade_4201(): bool {
  //   $this->ctx->log->info('Applying update 4201');
  //   // this path is relative to the extension base dir
  //   $this->executeSqlFile('sql/upgrade_4201.sql');
  //   return TRUE;
  // }

  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   */
  // public function upgrade_4202(): bool {
  //   $this->ctx->log->info('Planning update 4202'); // PEAR Log interface
  //   $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
  //   $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
  //   $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
  //   return TRUE;
  // }
  // public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  // public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  // public function processPart3($arg5) { sleep(10); return TRUE; }

  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   */
  // public function upgrade_4203(): bool {
  //   $this->ctx->log->info('Planning update 4203'); // PEAR Log interface
  //   $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
  //   $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
  //   for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
  //     $endId = $startId + self::BATCH_SIZE - 1;
  //     $title = E::ts('Upgrade Batch (%1 => %2)', array(
  //       1 => $startId,
  //       2 => $endId,
  //     ));
  //     $sql = '
  //       UPDATE civicrm_contribution SET foobar = apple(banana()+durian)
  //       WHERE id BETWEEN %1 and %2
  //     ';
  //     $params = array(
  //       1 => array($startId, 'Integer'),
  //       2 => array($endId, 'Integer'),
  //     );
  //     $this->addTask($title, 'executeSql', $sql, $params);
  //   }
  //   return TRUE;
  // }

}
