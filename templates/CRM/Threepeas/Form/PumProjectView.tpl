<div class="crm-section">
  <div class="label">{$form.title.label}</div>
  <div class="content">{$form.title.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.programme_id.label}</div>
  <div class="content">{$form.programme_id.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.customer_id.label}</div>
  <div class="content">{$form.customer_id.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.projectmanager_id.label}</div>
  <div class="content">{$form.projectmanager_id.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.reason.label}</div>
  <div class="content">{$form.reason.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.work_description.label}</div>
  <div class="content">{$form.work_description.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.expected_results.label}</div>
  <div class="content">{$form.expected_results.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.qualifications.label}</div>
  <div class="content">{$form.qualifications.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.sector_coordinator.label}</div>
  <div class="content">{$form.sector_coordinator.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.representative.label}</div>
  <div class="content">{$form.representative.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.authorised.label}</div>
  <div class="content">{$form.authorised.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.country_coordinator.label}</div>
  <div class="content">{$form.country_coordinator.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.project_officer.label}</div>
  <div class="content">{$form.project_officer.value}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.start_date.label}</div>
  <div class="content">{$form.start_date.value|crmDate}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.end_date.label}</div>
  <div class="content">{$form.end_date.value|crmDate}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.is_active.label}</div>
  <div class="content">{$form.is_active.value}</div>
  <div class="clear"></div>
</div>
{if $permission eq 6}
  <div class="action-link">
    <a id="project-edit-button" class="button new-option" href="{$editUrl}">
      <span><div class="icon edit-icon"></div>Edit this Project</span>
    </a>
  </div>
{/if}

