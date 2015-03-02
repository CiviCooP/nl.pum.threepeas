<div class="crm-content-block crm-block">
  <div id="help">
    The existing Programmes are listed below. You can add, edit, drill down or delete them from this screen. 
  </div>
  {if !empty($addUrl)}
    <div class="action-link">
      <a class="button new-option" href="{$addUrl}">
        <span><div class="icon add-icon"></div>New Programme</span>
      </a>
    </div>
  {/if}
  <div id="programme-wrapper" class="dataTables_wrapper">
    <table id="programme-table" class="display">
      <thead>
        <tr>
          <th class="sorting-disabled" rowspan="1" colspan="1">Title</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Programme Manager</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Budget</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Start date</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">End date</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Enabled</th>
          <th class="sorting_disabled" rowspan="1" colspan="1"></th>
        </tr>
      </thead>
      <tbody>
        {assign var="rowClass" value="odd-row"}
        {foreach from=$pumProgrammes item=programme}
          <tr id="row1" class={$rowClass}>
            <td hidden="1">{$programme.id}</td>
            <td hidden="1">{$programme.manger_id}</td>
            <td>{$programme.title}</td>
            <td>{$programme.manager_name}</td>
            <td>{$programme.budget}</td>
            <td>{$programme.start_date|crmDate}</td>
            <td>{$programme.end_date|crmDate}</td>
            <td>{$programme.is_active}</td>
            <td>
              <span>
                {foreach from=$programme.actions item=actionLink}
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
  {if !empty($addUrl)}
    <div class="action-link">
      <a class="button new-option" href="{$addUrl}">
        <span><div class="icon add-icon"></div>New Programme</span>
      </a>
    </div>
  {/if}
</div>