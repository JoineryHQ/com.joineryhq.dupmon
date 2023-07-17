<div class="help">
  {capture assign="batchesLink"}{crmURL p="civicrm/admin/dupmon/batches" q="reset=1" h=0}{/capture}
  <p>Dedupe Monitor scans regularly for duplicate candidates across all contacts. Dedupe Rules, if configured here for monitoring, will be applied during these scans.</p>
  {crmButton href="$batchesLink" class="edit" title="Configure" icon=fa-rocket}{ts}View Scanned Batches{/ts}{/crmButton}
  <div class="clear"></div>
</div>

<div class="crm-content-block crm-block">
  <div id="dupmonMonitors">
    <div class="form-item">
      <h3>{ts}Monitored Dedupe Rules{/ts}</h3>
      <div class="messages help">
        {icon icon="fa-info-circle"}{/icon}
        {ts}Note: It's not recommended to monitor Supervised dedupe rules, as they are usually designed to match a high number of false-positives.{/ts}
      </div>
      <table cellpadding="0" cellspacing="0" border="0" class="row-highlight">
        <tr>
          <th>{ts}Monitor?{/ts}</th>
          <th>{ts}Contact type{/ts}</th>
          <th>{ts}Used for{/ts}</th>
          <th>{ts}Title{/ts}</th>
        </tr>
        {foreach from=$ruleGroups item=ruleGroup}
          {capture assign=checkboxFieldName}enable-monitor-rule-group-{$ruleGroup.id}{/capture}
          <tr id="dupmon-monitor-rule-group-{$ruleGroup.id}" class="{cycle values="odd-row,even-row"}">
            <td>{$form.$checkboxFieldName.html}</td>
            <td>{$ruleGroups[$ruleGroup.id].contact_type}</td>
            <td>{$ruleGroups[$ruleGroup.id].used}</td>
            <td>{$ruleGroups[$ruleGroup.id].title}</td>
          </tr>
        {/foreach}
      </table>
    </div>
  </div>
  <div class="accordion" id="dupmon-advancedsettings-accordion">
    <div class="crm-accordion-wrapper collapsed">
      <div class="crm-accordion-header">
        {ts}Advanced Settings{/ts}
      </div>
      <div class="crm-accordion-body dupmon-advancedsettings-accordion-body">
        {foreach from=$settingElementNames item=settingElementName}
          <div class="crm-section">
            <div class="label">{$form.$settingElementName.label}</div>
            <div class="content">{$form.$settingElementName.html}<div class="description">{$descriptions.$settingElementName}</div></div>
            <div class="clear"></div>
          </div>
        {/foreach}

      </div>
    </div>
  </div>

  <div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>

