<div id="donor-link-wrapper" class="crm-accordion-wrapper crm-ajax-accordion crm-DonationApplication-accordion">
  <div id="donation-application" class="crm-accordion-header">
    {if isset($caseDetails.case_type) and $caseDetails.case_type eq 'Grant'}
      Add Link(s) to Grant Donation(s)
    {else}
      Add Link(s) to Donation(s)
    {/if}
  </div>
  <div class="crm-accordion-body" style="display: block">
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      {if $caseDetails.case_type eq 'Grant'}
        In the left box you will see all available grant donations. You can add or remove grant donations by moving them between the boxes. 
        In the second box you select the grant donation(s) to be used for reports (e.g. DGIS & Argidius). 
        In the third box you select the grant donation to be used by FA.
      {else}
        In the left box you will see all available donations. You can add or remove donations by moving them between the boxes. 
        In the second box you select the donation(s) to be used for reports (e.g. DGIS & Argidius). 
        In the third box you select the donation to be used by FA.
      {/if}
    </div>
    <div class="linked-donations">
      <div id="linked-donations" class="section-shown crm-contribution-additionalinfo-linked-donations-form-block">
        <table id="linked-donations-table" class="form-layout-compressed">
          <tbody>
            <tr class="crm-contribution-form-block-new-donation">
              <td class="label">{$form.new_link.label}</td>
              <td>{$form.new_link.html}</td>
              <td>{$form.fa_donor.html}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
