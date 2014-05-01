<h3>{$pageTitle}</h3>
<div class="crm-content-block crm-block">
  <div class="crm-submit-buttons">
    <span class="crm-button">
      <input id="done-drill" class="form-button" type="button" value="Done" name="done" onclick="window.location='{$doneUrl}'">
    </span>
  </div>

  <div id="programme-drill-wrapper" class="dataTables_wrapper">
    <table id="programme-drill-table" class="display">
      <thead>
        <tr>
          <th class="sorting-disabled" rowspan="1" colspan="1">Project Title</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Project Officer</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Project Start Date</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Project End Date</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Project Enabled</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Activity Subject</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Activity Type</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">Activity Status</th>
        </tr>
      </thead>
      <tbody>
        {assign var="rowClass" value="odd-row"}
        {foreach from=$drillData item=row}
          <tr id="row1" class={$rowClass}>
            <td hidden="1">{$row.project_id}</td>
            <td hidden="1">{$row.case_id}</td>
            <td hidden="1">{$row.project_officer_id}</td>
            <td>{$row.project_title}</td>
            <td>{$row.project_officer_name}</td>
            <td>{$row.project_start_date|crmDate}</td>
            <td>{$row.project_end_date|crmDate}</td>
            <td>{$row.project_active}</td>
            <td>{$row.case_subject}</td>
            <td>{$row.case_type}</td>
            <td>{$row.case_status}</td>
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
  <div class="crm-submit-buttons">
    <span class="crm-button">
      <input id="done-drill" class="form-button" type="button" value="Done" name="done" onclick="window.location='{$doneUrl}'">
    </span>
  </div>            
</div>