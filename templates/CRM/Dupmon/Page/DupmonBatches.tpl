<div class="help">
  {capture assign="settingsLink"}{crmURL p="civicrm/admin/dupmon/settings" q="reset=1" h=0}{/capture}
  <p>Dedupe Monitor scans regularly for duplicate candidates across all contacts. Duplicate candidates are grouped for review into batches shown below.</p>
  {crmButton href="$settingsLink" class="edit" title="Configure" icon=fa-gear}{ts}Configure Dedupe Monitor{/ts}{/crmButton}
  <div class="clear"></div>
</div>

<div class="crm-content-block crm-block">
  {if $rows}
    <div id="dupmonBatches">
      <p></p>
      <div class="form-item">
        {strip}
          {include file="CRM/common/enableDisableApi.tpl"}
          {include file="CRM/common/jsortable.tpl"}
          <table cellpadding="0" cellspacing="0" border="0" class="row-highlight">
            <thead class="sticky">
              <th>{ts}ID{/ts}</th>
              <th>{ts}Rule{/ts}</th>
              <th>{ts}Contact type{/ts}</th>
              <th>{ts}Scanned{/ts}</th>
              <th>{ts}Scanned contacts{/ts}</th>
              <th></th>
            </thead>
            {foreach from=$rows item=row}
              <tr id="dupmonBatch-{$row.id}" class="crm-entity {cycle values="odd-row,even-row"}">
                <td>{$row.id}</td>
                <td>{$row.rule_title}</td>
                <td>{$row.rule_contact_type}</td>
                <td>{$row.created|crmDate:"shortdate"} {$row.created|crmDate:"%I:%M:%S %P"}</td>
                <td>{$row.size}</td>
                <td>
                  <a class="crm-hover-button action-item" href="{crmURL p="civicrm/admin/dupmon/dedupebatch" q="id=`$row.id`"}">Dedupe</a>
                  <a class="crm-hover-button action-item" href="{crmURL p="civicrm/admin/dupmon/deletebatch" q="reset=1&id=`$row.id`"}">Forget batch</a>
                </td>
              </tr>
            {/foreach}
          </table>
        {/strip}
      </div>
    </div>
  {else}
    <div class="messages status no-popup">
      {icon icon="fa-info-circle"}{/icon}
      {ts}No batches found. More batches may be created after the next Scheduled Jobs run.{/ts}
    </div>
  {/if}
</div>
