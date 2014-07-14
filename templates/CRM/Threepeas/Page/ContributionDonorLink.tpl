<br />
<div id="donor-link-wrapper" class="crm-accordion-wrapper crm-ajax-accordion crm-DonationApplication-accordion">
  <div id="donation-application" class="crm-accordion-header">Donation Application</div>
  <div class="crm-accordion-body" style="display: block">
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      This section displays the linked programmes, projects and/or main activities. You can link a new project, programme or main activity.
    </div>    
    <div class="linked-threepeas">
      <div id="linked-threepeas" class="section-shown crm-contribution-additionalinfo-linked-threepeas-form-block">
        <table class="form-layout-compressed">
          <tbody>
            <tr class="crm-contribution-form-block-linked-programmes-header">
              <td class="crm-contribution-form-block-linked-programmes-select">{$form.programmeSelect.label}</td>
              <td>{$form.programmeSelect.html}</td>  
              <td class="label">{$form.programmeCount.label}</td>
              <td>{$form.programmeCount.value}</td>
            </tr>
            <tr class="crm-contribution-form-block-linked-projects-header">
              <td class="crm-contribution-form-block-linked-projects-select">{$form.projectSelect.label}</td>
              <td>{$form.projectSelect.html}</td>
              <td class="label">{$form.projectCount.label}</td>
              <td>{$form.projectCount.value}</td>
            </tr>
            <tr class="crm-contribution-form-block-linked-cases-header">
              <td class="crm-contribution-form-block-linked-cases-select">{$form.caseSelect.label}</td>
              <td>{$form.caseSelect.html}</td>
              <td class="label">{$form.caseCount.label}</td>
              <td>{$form.caseCount.value}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  cj('#donor-link-wrapper').insertBefore('#softCredit');
</script>