{* Threepeas - funded by PUM *}
{* Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>         *}
{* 20 April 2014                                              *}

<h3>{$formHeader}</h3>
{* HEADER *}
<div class="crm-block crm-form-block">
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
  <table class="form-layout-compressed">
    <tbody>
      {foreach from=$elementNames item=elementName}
        {if $elementName ne 'start_date' and $elementName ne 'end_date' and $elementName ne 'is_active'}
        <div class="crm-section">
            <div class="label">{$form.$elementName.label}</div>
            {if $action eq 4}
              <div class="content">{$form.$elementName.value}</div>
            {else}
              <div class="content">{$form.$elementName.html}</div>
            {/if}
            <div class="clear"></div>
          </div>
        {/if}
      {/foreach}
      {* start date *}
      <div class="crm-section">
        <div class="label">{$form.start_date.label}</div>
        {if $action eq 4}
          <div class="content">{$form.start_date.value|crmDate}</div>
        {else}
          <div class="content">{include file="CRM/common/jcalendar.tpl" elementName=start_date}</div>
        {/if}
        <div class="clear"></div>
      </div>
      {* end date *}
      <div class="crm-section">
        <div class="label">{$form.end_date.label}</div>
        {if $action eq 4}
          <div class="content">{$form.end_date.value|crmDate}</div>
        {else}
          <div class="content">{include file="CRM/common/jcalendar.tpl" elementName=end_date}</div>
        {/if}
        <div class="clear"></div>
      </div>
      <div class="crm-section">
        <div class="label">{$form.is_active.label}</div>
        {if $action eq 4}
          <div class="content">{$form.is_active.value}</div>
        {else}
          <div class="content">{$form.is_active.html}</div>
        {/if}
        <div class="clear"></div>
      </div>
    </tbody>
  </table>
  {* allow add budgetdivision if action is edit *}
  {if $action eq 2}
    {include file="CRM/Threepeas/Form/NewProgrammeDivision.tpl"}
  {/if}
  {* include condition details if they are there *}
  {if $action ne 1}
    {include file="CRM/Threepeas/Page/CurrentProgrammeDivision.tpl"}
  {/if}

  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
