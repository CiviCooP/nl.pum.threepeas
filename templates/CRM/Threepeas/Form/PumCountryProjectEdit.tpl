<div class="crm-section">
  <div class="label">{$form.title.label}</div>
  <div class="content">{$form.title.html}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.programme_id.label}</div>
  <div class="content">{$form.programme_id.html}</div>
  <div class="clear"></div>
</div>
{* only show customer in update mode *}
{if $action eq 2}
  <div class="crm-section">
    <div class="label">{$form.country_id.label}</div>
    <div class="content">{$form.country_id.value}</div>
    <div class="clear"></div>
  </div>
{else}
  <div class="crm-section">
    <div class="label">{$form.country_id.label}</div>
    <div class="content">{$form.country_id.html}</div>
    <div class="clear"></div>
  </div>
{/if}
<div class="crm-section">
  <div class="label">{$form.expected_results.label}</div>
  <div class="content">{$form.expected_results.html}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.qualifications.label}</div>
  <div class="content">{$form.qualifications.html}</div>
  <div class="clear"></div>
</div>
{* do not show coordinators if action is add *}
{if $action ne 1}
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
{/if}
<div class="crm-section">
  <div class="label">{$form.start_date.label}</div>
  <div class="content">{include file="CRM/common/jcalendar.tpl" elementName=start_date}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.end_date.label}</div>
  <div class="content">{include file="CRM/common/jcalendar.tpl" elementName=end_date}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.is_active.label}</div>
  <div class="content">{$form.is_active.html}</div>
  <div class="clear"></div>
</div>
