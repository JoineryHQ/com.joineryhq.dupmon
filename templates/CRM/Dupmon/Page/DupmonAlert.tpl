<div class="crm-block crm-content-block">
    {if $alert}
      <div class="status">
        <p>
          {$alert}
        </p>
          {crmButton href="$reviewUrl" class="edit" title="Review" icon=fa-rocket}{ts}Review Duplicate Monitor Batches{/ts}{/crmButton}
          <div class="clear"></div>
      </div>
    {else}
      <div class="help">
        <p>{ts}No Dedupe Monitor Batches are pending review. You're all caught up!{/ts}</p>
      </div>
    {/if}
</div>