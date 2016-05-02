<h3>{$formHeader}</h3>
{* HEADER *}
<div class="crm-block crm-form-block">
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
  <table class="form-layout-compressed">
    <tbody>
      <div class="crm-section">
        <div class="label">{$form.title.label}</div>
        <div class="content">{$form.title.value}</div>
        <div class="clear"></div>
      </div>
      <div class="crm-section">
        <div class="label">{$form.programme_name.label}</div>
        <div class="content">{$form.programme_name.value}</div>
        <div class="clear"></div>
      </div>
      <div class="crm-section">
        <div class="label">{$form.customer_name.label}</div>
        <div class="content">{$form.customer_name.value}</div>
        <div class="clear"></div>
      </div>
      {* cusrtomer project *}
      {if $projectType eq "customer"}
          <div class="crm-section">
            <div class="label">{$form.projectmanager_name.label}</div>
            <div class="content">{$form.projectmanager_name.value}</div>
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
      {/if}
      <div class="crm-section">
        <div class="label">{$form.expected_results.label}</div>
        <div class="content">{$form.expected_results.value}</div>
        <div class="clear"></div>
      </div>
      <div class="crm-section">
        <div class="label">{$form.projectplan.label}</div>
        <div class="content">{$form.projectplan.value}</div>
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
        <div class="label">{$form.number_cases.label}</div>
        <div class="content">{$form.number_cases.value}</div>
        <div class="clear"></div>
      </div>
    </tbody>
  </table>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
