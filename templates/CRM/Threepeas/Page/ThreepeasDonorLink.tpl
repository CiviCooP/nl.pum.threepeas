<div id="donor-link-wrapper" class="crm-accordion-wrapper crm-ajax-accordion crm-DonationApplication-accordion">
  <div id="donation-application" class="crm-accordion-header">Donation Details</div>
  <div class="crm-accordion-body" style="display: block">
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      This section displays the linked donations.
    </div>    
    <div class="linked-donations">
      <div id="linked-donations" class="section-shown crm-contribution-additionalinfo-linked-donations-form-block">
        <table class="form-layout-compressed">
          <tbody>
            {foreach from=$linkedDonations item=linkedDonation}
              <tr class="crm-contribution-form-block-linked-donations-row">
                <td hidden="1">{$linkedDonation.entity_id}</td>
                <td hidden="1">{$linkedDonation.donation_entity_id}</td>
                <td>{$linkedDonation.donor}</td>
                <td>{$linkedDonation.amount}</td>
                <td>{$linkedDonation.status}</td>
                <td>
                  {if $action ne "view"}
                    <span>
                      <a class="action-item" title="Remove link" href="
                        {$delDonorLinkUrl}}">Remove Link</a>
                    </span>
                  {/if}
                </td>
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
