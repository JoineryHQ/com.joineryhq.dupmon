<?php

use CRM_Dupmon_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Dupmon_Form_Settings extends CRM_Core_Form {

  private $_ruleGroups;
  private $_ruleMonitors;
  public static $settingFilter = array('group' => 'dupmon');
  public static $extensionName = 'dupmon';
  private $_submittedValues = array();
  private $_settings = array();

  public function __construct(
    $state = NULL,
    $action = CRM_Core_Action::NONE,
    $method = 'post',
    $name = NULL
  ) {

    $this->setSettings();

    parent::__construct(
      $state = NULL,
      $action = CRM_Core_Action::NONE,
      $method = 'post',
      $name = NULL
    );
  }

  /**
   * Define the list of settings we are going to allow to be set on this form.
   *
   */
  public function setSettings() {
    if (empty($this->_settings)) {
      $this->_settings = self::getSettings();
    }
  }

  public static function getSettings() {
    $settings = civicrm_api3('setting', 'getfields', array('filters' => self::$settingFilter));
    return $settings['values'];
  }

  public function preProcess() {
    $ruleGroupGet = civicrm_api3('ruleGroup', 'get', [
      'options' => [
        'limit' => 0,
        'sort' => "contact_type, used, title",
      ],
    ]);
    $this->_ruleGroups = $ruleGroupGet['values'];
    $ruleMonitorGet = civicrm_api3('dupmonRuleMonitor', 'get', [
      'options' => [
        'limit' => 0,
      ],
    ]);
    $this->_ruleMonitors = $ruleMonitorGet['values'];
  }

  public function buildQuickForm() {

    $this->assign('ruleGroups', $this->_ruleGroups);

    $limitGroupIdOptions = [0 => E::ts('- none -')] + CRM_Contact_BAO_GroupContact::buildOptions('group_id');

    foreach ($this->_ruleGroups as $ruleGroup) {
      $this->addElement('checkbox', 'enable-monitor-rule-group-' . $ruleGroup['id']);
      $this->add(
        // field type
        'select',
        // field name
        'limit-group-rule-group-' . $ruleGroup['id'],
        // field label
        '',
        // options
        $limitGroupIdOptions,
        // what is this arg?
        NULL,
        // html attributes
        NULL
      );
    }

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ),
    ));

    $this->_buildQuickFormSettings();
    parent::buildQuickForm();
  }

  public function _buildQuickFormSettings() {
    $settings = $this->_settings;
    $settingElementNames = [];
    foreach ($settings as $name => $setting) {
      if (isset($setting['quick_form_type'])) {
        switch ($setting['html_type']) {
          case 'Select':
            $this->add(
              // field type
              $setting['html_type'],
              // field name
              $setting['name'],
              // field label
              $setting['title'],
              $this->getSettingOptions($setting),
              NULL,
              $setting['html_attributes']
            );
            break;

          case 'CheckBox':
            $this->addCheckBox(
              // field name
              $setting['name'],
              // field label
              $setting['title'],
              array_flip($this->getSettingOptions($setting))
            );
            break;

          case 'Radio':
            $this->addRadio(
              // field name
              $setting['name'],
              // field label
              $setting['title'],
              $this->getSettingOptions($setting)
            );
            break;

          default:
            $add = 'add' . $setting['quick_form_type'];
            if ($add == 'addElement') {
              $this->$add($setting['html_type'], $name, E::ts($setting['title']), CRM_Utils_Array::value('html_attributes', $setting, array()));
            }
            else {
              $this->$add($name, E::ts($setting['title']));
            }
            break;
        }
        if (!empty($setting['formRules'])) {
          foreach ($setting['formRules'] as $formRuleType => $formRuleMessage) {
            $this->addRule($setting['name'], $formRuleMessage, $formRuleType);
          }
        }
        $settingElementNames[] = $setting['name'];
      }
      $descriptions[$setting['name']] = E::ts($setting['description']);

      if (!empty($setting['X_form_rules_args'])) {
        $rules_args = (array) $setting['X_form_rules_args'];
        foreach ($rules_args as $rule_args) {
          array_unshift($rule_args, $setting['name']);
          call_user_func_array(array($this, 'addRule'), $rule_args);
        }
      }
    }
    $this->assign("descriptions", $descriptions);

    // export form elements
    $this->assign('settingElementNames', $settingElementNames);
  }

  public function postProcess() {
    $values = $this->exportValues();
    foreach ($this->_ruleGroups as $ruleGroupId => $ruleGroup) {
      $monitorParams = [
        'is_active' => (bool) ($values['enable-monitor-rule-group-' . $ruleGroupId] ?? FALSE),
        'limit_group_id' => $values['limit-group-rule-group-' . $ruleGroupId] ? $values['limit-group-rule-group-' . $ruleGroupId] : 'null',
      ];
      $this->_saveGroupMonitor($ruleGroupId, $monitorParams);
    }
    $this->saveSettings();

    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/dupmon/settings', 'reset=1'));

    parent::postProcess();
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   */
  public function saveSettings() {
    $settings = $this->_settings;
    $submittedValues = $this->exportValues();

    $values = array_intersect_key($submittedValues, $settings);
    civicrm_api3('setting', 'create', $values);

    // Save any that are not submitted, as well (e.g., checkboxes that aren't checked).
    $unsettings = array_fill_keys(array_keys(array_diff_key($settings, $submittedValues)), NULL);
    civicrm_api3('setting', 'create', $unsettings);

    CRM_Core_Session::setStatus(" ", E::ts('Settings saved.'), "success");
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  public function setDefaultValues() {
    $result = civicrm_api3('setting', 'get', array('return' => array_keys($this->_settings)));
    $domainID = CRM_Core_Config::domainID();
    $ret = CRM_Utils_Array::value($domainID, $result['values']);

    foreach ($this->_ruleMonitors as $ruleMonitor) {
      $ret['enable-monitor-rule-group-' . $ruleMonitor['rule_group_id']] = (bool) $ruleMonitor['is_active'];
      $ret['limit-group-rule-group-' . $ruleMonitor['rule_group_id']] = ($ruleMonitor['limit_group_id'] ?? '');
    }
    return $ret;
  }

  private function _saveGroupMonitor(int $ruleGroupId, $params) {
    // Get any exisitng rule monitor for this rule (data structure allows maximum
    // one monitor per group, but there may also be 0, because we make no effort
    // to create a new "monitor" record for every new dedupe rule.
    // However this, form, upon saving, happens to ensure existence of a monitor
    // for every dedupe rule that exists at the time.
    $ruleMonitorGet = civicrm_api3('dupmonRuleMonitor', 'get', [
      'rule_group_id' => $ruleGroupId,
    ]);
    // If $ruleMonitorGet['id'] is undefined, it means no such monitor exists,
    // so we'll be creating one.
    $params['id'] = ($ruleMonitorGet['id'] ?? NULL);
    $params['rule_group_id'] = $ruleGroupId;
    civicrm_api3('dupmonRuleMonitor', 'create', $params);
  }

  public function getSettingOptions($setting) {
    if (!empty($setting['X_options_callback']) && is_callable($setting['X_options_callback'])) {
      return call_user_func($setting['X_options_callback']);
    }
    else {
      return CRM_Utils_Array::value('X_options', $setting, array());
    }
  }

}
