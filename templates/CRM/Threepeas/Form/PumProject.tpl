{* Threepeas - funded by PUM *}
{* Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>         *}
{* 30 April 2014                                              *}

<h3>{$formHeader}</h3>
{* HEADER *}
{assign var=showAll value=1}
<div class="crm-block crm-form-block">
  {if $permission ne 6}
    {if $action eq 2}
      <div class="messages status no-popup">
        <div class="icon inform-icon"></div>
        You are not authorised to update projects
      </div>
    {/if}
    {if $action eq 1}
      {assign var=showAll value=0}
      <div class="messages status no-popup">
        <div class="icon inform-icon"></div>
        You are not authorised to add projects
      </div>
    {/if}
  {/if}
  {if $showAll eq 1}
    <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="top"}
    </div>
    <table class="form-layout-compressed">
      <tbody>
        {* view mode (action = 4) or no access rights *}
        {if $action eq 4 or $permission ne 6}
          {if empty($form.customer_id.value)}
            {include file="CRM/Threepeas/Form/PumCountryProjectView.tpl"}
          {else}
            {include file="CRM/Threepeas/Form/PumProjectView.tpl"}
          {/if}
        {else}
          {if empty($form.customer_id.value)}
            {include file="CRM/Threepeas/Form/PumCountryProjectEdit.tpl"}
          {else}
            {include file="CRM/Threepeas/Form/PumProjectEdit.tpl"}
          {/if}
        {/if}
      </tbody>
    </table>
    {if !empty($form.customer_id.value)}
      {include file="CRM/Threepeas/Form/PumProjectPlan.tpl"}
    {/if}
    {if $action eq 4 or $permission ne 6}   
      {include file="CRM/Threepeas/Page/DonorLinkView.tpl"}
    {else}
      {include file="CRM/Threepeas/Page/DonorLinkEdit.tpl"}
    {/if}
    {* FOOTER *}
    <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  {/if}
</div>
{literal}
  <script type="text/javascript">
    cj('#_qf_PumProject_next-top').click(function() {
      CRM.alert('Top Geklikt!');
    });
    cj('#_qf_PumProject_next-bottom').on("click", goToEdit);
    function goToEdit {
      window.location.href="http://www.civicoop.org";
    }
  </script>
{/literal}
