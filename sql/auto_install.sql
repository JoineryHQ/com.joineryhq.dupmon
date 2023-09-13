-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the existing tables - this section generated from drop.tpl
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_dupmon_rule_monitor`;
DROP TABLE IF EXISTS `civicrm_dupmon_rule_info`;
DROP TABLE IF EXISTS `civicrm_dupmon_batch`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civicrm_dupmon_batch
-- *
-- * Dedupe Monitor batch scan result
-- *
-- *******************************************************/
CREATE TABLE `civicrm_dupmon_batch` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique DupmonRuleMonitor ID',
  `group_id` int unsigned NOT NULL COMMENT 'FK to Group (required in sql but not in api)',
  `rule_group_id` int unsigned NOT NULL COMMENT 'FK to Dedupe Rule Group',
  `created` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT 'Date/Time created',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_dupmon_batch_group_id FOREIGN KEY (`group_id`) REFERENCES `civicrm_group`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_dupmon_batch_rule_group_id FOREIGN KEY (`rule_group_id`) REFERENCES `civicrm_dedupe_rule_group`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_dupmon_rule_info
-- *
-- * Relevant info for dedupe rule groups
-- *
-- *******************************************************/
CREATE TABLE `civicrm_dupmon_rule_info` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique DupmonRuleInfo ID',
  `rule_group_id` int unsigned NOT NULL COMMENT 'FK to Dedupe Rule Group',
  `hash` varchar(64) NOT NULL COMMENT 'hash of rule configuration',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_dupmon_rule_info_rule_group_id FOREIGN KEY (`rule_group_id`) REFERENCES `civicrm_dedupe_rule_group`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_dupmon_rule_monitor
-- *
-- * Dedupe Monitor rule configurations
-- *
-- *******************************************************/
CREATE TABLE `civicrm_dupmon_rule_monitor` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique DupmonRuleMonitor ID',
  `rule_group_id` int unsigned COMMENT 'FK to Dedupe Rule Group',
  `scan_limit` int unsigned COMMENT 'maximum number of contacts to scan with this rule ()',
  `min_cid` int unsigned DEFAULT 0 COMMENT 'minimum contact ID to scan with this rule (1 + max cid from previous scan)',
  `limit_group_id` int unsigned COMMENT 'FK to civicrm_group.id (limit monitor to contacts in this group, if specified)',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_dupmon_rule_monitor_rule_group_id FOREIGN KEY (`rule_group_id`) REFERENCES `civicrm_dedupe_rule_group`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_dupmon_rule_monitor_limit_group_id FOREIGN KEY (`limit_group_id`) REFERENCES `civicrm_group`(`id`) ON DELETE SET NULL
)
ENGINE=InnoDB;
