<div class="crm-content-block crm-block">
  <div id="help">
    The existing Projects are listed below. You can add, edit, drill down or delete them from this screen. 
  </div>
  {if $addUrl ne ''}
    <div class="action-link">
      <a class="button new-option" href="{$addUrl}">
        <span><div class="icon add-icon"></div>New Country Project</span>
      </a>
    </div>
  {/if}
  <div id="project_wrapper" class="dataTables_wrapper">
    <table id="project-table" class="display">
      <thead>
        <tr>
          <th class="sorting-disabled" rowspan="1" colspan="1">Title</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Parent Programme</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Project Customer/Country</th>
          {if $request_type != 'Country'}
            <th class="sorting-disabled" rowspan="1" colspan="1">Project Manager</th>
            <th class="sorting-disabled" rowspan="1" colspan="1">Sector Coordinator</th>
            <th class="sorting-disabled" rowspan="1" colspan="1">Representative</th>
            <th class="sorting-disabled" rowspan="1" colspan="1">Authorised</th>
            <th class="sorting-disabled" rowspan="1" colspan="1">Anamon</th>          
          {else}
            <th class="sorting-disabled" rowspan="1" colspan="5">&nbsp;</th>
          {/if}
          <th class="sorting-disabled" rowspan="1" colspan="1">Country Coordinator</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Project Officer</th>          
          <th class="sorting-disabled" rowspan="1" colspan="1">Start date</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">End date</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Enabled</th>
          <th class="sorting_disabled" rowspan="1" colspan="1"></th>
        </tr>
      </thead>
      <tbody>
        {assign var="rowClass" value="odd-row"}
        {foreach from=$pumProjects item=project}
          <tr id="row1" class={$rowClass}>
            <td hidden="1">{$project.id}</td>
            <td>{$project.title}</td>
            <td>{$project.programme_name}</td>
            {if ($project.country_name)}
              <td>{$project.country_name}</td>
            {else}
              <td>{$project.customer_name}</td>
            {/if}
            {if $request_type != 'Country'}
              <td>{$project.projectmanager_name}</td>
              <td>{$project.sector_coordinator}</td>
              <td>{$project.representative}</td>
              <td>{$project.authorised_contact}</td>
              <td>{$project.anamon}</td>           
            {else}
              <td colspan="5">&nbsp;</td>
            {/if}
            <td>{$project.country_coordinator}</td>
            <td>{$project.project_officer}</td>            
            <td>{$project.start_date|crmDate}</td>
            <td>{$project.end_date|crmDate}</td>
            <td>{$project.is_active}</td>
            <td>
              <span>
                {foreach from=$project.actions item=actionLink}
                  {$actionLink}
                {/foreach}
              </span>
            </td>
          </tr>
          {if $rowClass eq "odd-row"}
            {assign var="rowClass" value="even-row"}
          {else}
            {assign var="rowClass" value="odd-row"}                        
          {/if}
        {/foreach}
      </tbody>
    </table>    
  </div>
  {if $addUrl ne ''}
    <div class="action-link">
      <a class="button new-option" href="{$addUrl}">
        <span><div class="icon add-icon"></div>New Country Project</span>
      </a>
    </div>
  {/if}
</div>
