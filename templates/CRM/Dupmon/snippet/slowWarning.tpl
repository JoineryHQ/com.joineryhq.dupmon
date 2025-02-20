{* This is a warning message, designed to be displayed at the top of CiviCRM's
 * native "Use Rule" form (i.e., navigate Contacts > Manage Duplicates, and there
 * select a rule, e.g. "Email (reserved)" and click "Use Rule").
 *}
{crmScope extensionKey='org.example.myextension'}
  {ts}This operation can slow or freeze your site. Please consider using the Dedupe Monitor Scanned Batches instead.{/ts}
  <div class="clear"></div>
  {crmButton href="/civicrm/admin/dupmon/batches?reset=1" class="edit" icon="fa-rocket"}{ts}View Scanned Batches{/ts}{/crmButton}
  <div class="clear"></div>
{/crmScope}