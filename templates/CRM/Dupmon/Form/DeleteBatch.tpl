{* Confirmation of dupmonBatch delete *}
<div class="crm-block crm-content-block crm-dupmon-delete-form-block">
  <div class="messages status no-popup">
    {icon icon="fa-info-circle"}{/icon}
    {ts}Are you sure you want to forget this batch? This action merely discards the batched scan results, and makes no changes to any contact records.{/ts}
  </div>

  <table cellpadding="0" cellspacing="0" border="0" class="row-highlight">
    <tr><th>{ts}ID{/ts}</th><td>{$dupmonBatch.id}</td></tr>
    <tr><th>{ts}Rule{/ts}</th><td>{$dupmonBatch.rule_title}</td></tr>
    <tr><th>{ts}Contact type{/ts}</th><td>{$dupmonBatch.rule_contact_type}</td></tr>
    <tr><th>{ts}Scanned{/ts}</th><td>{$dupmonBatch.created|crmDate:"shortdate"} {$dupmonBatch.created|crmDate:"Time"}</td></tr>
   </table>

  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl"}
  </div>

</div>
