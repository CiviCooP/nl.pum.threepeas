<div class="crm-content-block crm-block">
  <div id="help">
    The disabled projects are listed below. You can check the related cases and delete the project with or without the cases if required. Be careful!
  </div>
  <div id="disabled-project-wrapper" class="dataTables_wrapper">
    <table id="disabled-project-table" class="display">
      <thead>
      <tr>
        <th class="sorting-disabled" rowspan="1" colspan="1">Title</th>
        <th class="sorting-disabled" rowspan="1" colspan="1">Project Customer/Country</th>
        {if $request_type != 'Country'}
          <th class="sorting-disabled" rowspan="1" colspan="1">Project Manager</th>
        {else}
          <th class="sorting-disabled" rowspan="1" colspan="5">&nbsp;</th>
        {/if}
        <th class="sorting-disabled" rowspan="1" colspan="1">Start date</th>
        <th class="sorting-disabled" rowspan="1" colspan="1">End date</th>
        <th class="sorting-disabled" rowspan="1" colspan="1">No. of Cases</th>
        <th class="sorting_disabled" rowspan="1" colspan="1"></th>
      </tr>
      </thead>
      <tbody>
      {assign var="rowClass" value="odd-row"}
      {foreach from=$pumProjects item=project}
        <tr id="row1" class={$rowClass}>
          <td hidden="1">{$project.id}</td>
          <td>{$project.title}</td>
          {if ($project.country_name)}
            <td>{$project.country_name}</td>
          {else}
            <td>{$project.customer_name}</td>
          {/if}
          {if $request_type != 'Country'}
            <td>{$project.projectmanager_name}</td>
          {else}
            <td colspan="5">&nbsp;</td>
          {/if}
          <td>{$project.start_date|crmDate}</td>
          <td>{$project.end_date|crmDate}</td>
          <td>{$project.number_cases}</td>
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
</div>
