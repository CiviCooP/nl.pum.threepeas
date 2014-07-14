<br />
<div id="donor-link-wrapper" class="crm-accordion-wrapper crm-ajax-accordion crm-DonationApplication-accordion">
  <div id="donation-application" class="crm-accordion-header">Donation Application</div>
  <div class="crm-accordion-body" style="display: block">
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      This section displays the linked programmes, projects and/or main activities.
    </div>    
    <div class="pumprogramme">
      <div id=pumprogramme" class="section-shown crm-contribution-additionalinfo-pumprogramme-form-block">
        <table class="form-layout-compressed">
          <tbody>
            <tr class="crm-contribution-form-block-programm-header">
              <td class="label">{$form.programmeHeader}</td>
            </tr>
            <tr class="crm-contribution-form-block-programme-row">
              <td>
                {foreach from=$form.programmeRows item=programme}
                  {$programme}<br />
                {/foreach}
                {$form.programmeSelect}
              </td>
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