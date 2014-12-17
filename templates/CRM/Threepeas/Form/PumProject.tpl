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
          {* customer project *}
          {if !empty($form.customer_id.value)}  
            <div class="crm-section">
              <div class="label">{$form.customer_id.label}</div>
              <div class="content">{$form.customer_id.value}</div>
              <div class="clear"></div>
            </div>
          {* country project *}    
          {else}
            <div class="crm-section">
              <div class="label">{$form.country_id.label}</div>
              <div class="content">{$form.country_id.value}</div>
              <div class="clear"></div>
            </div>
          {/if}
          {* only for customer project *}
          {if !empty($form.customer_id.value)}
            <div class="crm-section">
              <div class="label">{$form.projectmanager_id.label}</div>
              <div class="content">{$form.projectmanager_id.value}</div>
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
          {/if}
          <div class="crm-section">
            <div class="label">{$form.expected_results.label}</div>
            <div class="content">{$form.expected_results.value}</div>
            <div class="clear"></div>
          </div>  
          {* only for customer project *}
          {if !empty($form.customer_id.value)}
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
          {/if}    
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
          {* customer project only *}
          {if !empty($form.customer_id.value)}
            <div class="crm-section">
              <div class="label">{$form.projectmanager_id.label}</div>
              <div class="content">{$form.projectmanager_id.html}</div>
              <div class="clear"></div>
            </div>            
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
          {/if}
          <div class="crm-section">
            <div class="label">{$form.expected_results.label}</div>
            <div class="content">{$form.expected_results.value}</div>
            <div class="clear"></div>
          </div>  
          <div class="crm-section">
            <div class="label">{$form.qualifications.label}</div>
            <div class="content">{$form.qualifications.html}</div>
            <div class="clear"></div>
          </div>
          {* do not show coordinators if action is add *}
          {if $action ne 1}
            {* only customer project *}
            {if !empty($form.customer_id.value)}
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
            {/if}
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
    <div id="project-wrapper" class="crm-accordion-wrapper crm-ajax-accordion crm-project-accordion">
      <div id="project-application" class="crm-accordion-header">
        Project plan
      </div>
      <div class="crm-accordion-body" style="display: block">
        <div class="messages status no-popup">
          <div class="icon inform-icon"></div>
          Please fill out which activities you see necessary to achieve the projectgoal. Name the type of Main Activities, the planning of the project and corresponding budget. 
          Advice / Spring 2015 / 700,--.BLP / Autumn 2015 / 700,-- etc.
        </div>
        <div class="projectplan">
          <div id="projectplan-line" class="section-shown crm-contribution-additionalinfo-projectplan-form-block">
            <table id="projectplan-table" class="form-layout-compressed">
              <tbody>
                <tr class="crm-contribution-form-block-projectplan">
                  <td class="label">{$form.projectplan.label}</td>
                  <td>{$form.projectplan.html}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
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
