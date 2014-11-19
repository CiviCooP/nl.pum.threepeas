<div id="donor-link-wrapper" class="crm-accordion-wrapper crm-ajax-accordion crm-DonationApplication-accordion">
  <div id="donation-application" class="crm-accordion-header">
      Linked Donations
  </div>
  <div class="crm-accordion-body" style="display: block">
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      This section shows the donations currently linked to this {$linkEntity}
    </div>
    <div class="linked-donations">
      <div id="linked-donations" class="section-shown crm-contribution-additionalinfo-linked-donations-form-block">
        <table class="form-layout-compressed">
          <thead>
            <tr class="crm-contribution-form-block-linked-donations">
              <th>{ts}Contact{/ts}</th>
              <th>{ts}Amount{/ts}</th>
              <th>{ts}Status{/ts}</th>
              <th>{ts}Date{/ts}</th>
              <th>&nbsp</th>
              </tr>
          </thead>
          <tbody>
            {foreach from=$linkedDonations item=linkedDonation}
              <tr class="crm-contribution-form-block-linked-donations">
                <td>{$linkedDonation.contact}</td>
                <td>{$linkedDonation.amount}</td>
                <td>{$linkedDonation.status}</td>
                <td>{$linkedDonation.date}</td>
                <td>{$linkedDonation.view_link}</td>
                <td>{$linkedDonation.remove_link}</td>
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
