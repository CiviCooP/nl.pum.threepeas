{* Threepeas - funded by PUM *}
{* Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>         *}
{* 30 April 2014                                              *}

<h3>{$formHeader}</h3>
{* HEADER *}
<div class="crm-block crm-form-block">
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
  <table class="form-layout-compressed">
    <tbody>
      {* view mode (action = 4) *}
      {if $action eq 4}
        <div class="crm-section">
          <div class="label">{$form.title.label}</div>
            <div class="content">{$form.title.value}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
          <div class="label">{$form.programme_id.label}</div>
          <div class="content">{$form.programme_id.value}</div>
          <div class="clear"></div>
        </div>
        {if !empty($form.customer_id.value)}  
          <div class="crm-section">
            <div class="label">{$form.customer_id.label}</div>
            <div class="content">{$form.customer_id.value}</div>
            <div class="clear"></div>
          </div>
        {else}
          <div class="crm-section">
            <div class="label">{$form.country_id.label}</div>
            <div class="content">{$form.country_id.value}</div>
            <div class="clear"></div>
          </div>
        {/if}
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
          <div class="label">{$form.expected_results.label}</div>
          <div class="content">{$form.expected_results.value}</div>
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
          <div class="label">{$form.representative.label}</div>
          <div class="content">{$form.representative.value}</div>
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
          <div class="label">{$form.is_active.label}</div>
          <div class="content">{$form.is_active.value}</div>
          <div class="clear"></div>
        </div>
      {else}
        {* any other mode than view *}
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
         {if !empty($form.customer_id.value)}
            <div class="crm-section">
             <div class="label">{$form.customer_id.label}</div>
             <div class="content">{$form.customer_id.value}</div>
             <div class="clear"></div>
            </div>
         {else}
            <div class="crm-section">
             <div class="label">{$form.country_id.label}</div>
             <div class="content">{$form.country_id.value}</div>
             <div class="clear"></div>
            </div>
         {/if}
       {else}
          <div class="crm-section">
            <div class="label">{$form.country_id.label}</div>
            <div class="content">{$form.country_id.html}</div>
            <div class="clear"></div>
        </div>
        {/if}
        <div class="crm-section">
          <div class="label">{$form.reason.label}</div>
          <div class="content">{$form.reason.html}</div>
          <div class="clear"></div>
        </div>  
        <div class="crm-section">
          <div class="label">{$form.work_description.label}</div>
          <div class="content">{$form.work_description.html}</div>
          <div class="clear"></div>
        </div>  
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
            <div class="label">{$form.sector_coordinator.label}</div>
            <div class="content">{$form.sector_coordinator.value}</div>
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
            <div class="label">{$form.representative.label}</div>
            <div class="content">{$form.representative.value}</div>
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
      {/if}
    </tbody>
  </table>
  {if $action eq 4}   
    {include file="CRM/Threepeas/Page/DonorLinkView.tpl"}
  {else}
    {include file="CRM/Threepeas/Page/DonorLinkEdit.tpl"}
  {/if}
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
