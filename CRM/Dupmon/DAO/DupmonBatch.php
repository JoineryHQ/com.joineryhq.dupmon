<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from com.joineryhq.dupmon/xml/schema/CRM/Dupmon/dupmonBatch.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:1bf6d22f38001d1cb99ebb1a6dd0de51)
 */
use CRM_Dupmon_ExtensionUtil as E;

/**
 * Database access object for the DupmonBatch entity.
 */
class CRM_Dupmon_DAO_DupmonBatch extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_dupmon_batch';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique DupmonRuleMonitor ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * FK to Group (required in sql but not in api)
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $group_id;

  /**
   * FK to Dedupe Rule Group
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $rule_group_id;

  /**
   * Date/Time created
   *
   * @var string|null
   *   (SQL type: timestamp)
   *   Note that values will be retrieved from the database as a string.
   */
  public $created;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_dupmon_batch';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Dupmon Batches') : E::ts('Dupmon Batch');
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'group_id', 'civicrm_group', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'rule_group_id', 'civicrm_dedupe_rule_group', 'id');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('ID'),
          'description' => E::ts('Unique DupmonRuleMonitor ID'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_dupmon_batch.id',
          'table_name' => 'civicrm_dupmon_batch',
          'entity' => 'DupmonBatch',
          'bao' => 'CRM_Dupmon_DAO_DupmonBatch',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'group_id' => [
          'name' => 'group_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Group ID'),
          'description' => E::ts('FK to Group (required in sql but not in api)'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_dupmon_batch.group_id',
          'table_name' => 'civicrm_dupmon_batch',
          'entity' => 'DupmonBatch',
          'bao' => 'CRM_Dupmon_DAO_DupmonBatch',
          'localizable' => 0,
          'FKClassName' => 'CRM_Contact_DAO_Group',
          'add' => NULL,
        ],
        'rule_group_id' => [
          'name' => 'rule_group_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Rule Group ID'),
          'description' => E::ts('FK to Dedupe Rule Group'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_dupmon_batch.rule_group_id',
          'table_name' => 'civicrm_dupmon_batch',
          'entity' => 'DupmonBatch',
          'bao' => 'CRM_Dupmon_DAO_DupmonBatch',
          'localizable' => 0,
          'FKClassName' => 'CRM_Dedupe_DAO_DedupeRuleGroup',
          'add' => NULL,
        ],
        'created' => [
          'name' => 'created',
          'type' => CRM_Utils_Type::T_TIMESTAMP,
          'title' => E::ts('Created'),
          'description' => E::ts('Date/Time created'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_dupmon_batch.created',
          'default' => 'CURRENT_TIMESTAMP',
          'table_name' => 'civicrm_dupmon_batch',
          'entity' => 'DupmonBatch',
          'bao' => 'CRM_Dupmon_DAO_DupmonBatch',
          'localizable' => 0,
          'add' => NULL,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'dupmon_batch', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'dupmon_batch', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
