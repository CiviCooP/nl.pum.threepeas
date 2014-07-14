<div id="SponsorLink-wrapper" class="crm-accordion-wrapper crm-ajax-accordion crm-SponsorLink-accordion">
  <div id="sponsor-details" class="crm-accordion-header">Sponsor Details</div>
  <div class="crm-accordion-body" style="display: block">
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