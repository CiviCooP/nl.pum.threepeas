<h3>{$pageTitle}</h3>
<div class="crm-content-block crm-block">
  <div class="crm-submit-buttons">
    <span class="crm-button">
      <input id="done-drill" class="form-button" type="button" value="Done" name="done" onclick="window.location='{$doneUrl}'">
    </span>
    {if $caseUrl}
      <span class="crm-button">
        <input id="add-case" class="form-button" type="button" value="Create case" name="add-case" onclick="window.location='{$caseUrl}'">
      </span>
    {/if}
  </div>            
  <div id="project-drill-wrapper" class="dataTables_wrapper">
    <table id="project-drill-table" class="display">
      <thead>
        <tr>
          <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.type}</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.objective}</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.client}</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.expert}</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.start_date}</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.end_date}</th>
          <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.status}</th>
          <th class="sorting-disabled" rowspan="1" colspan="1"></th>
        </tr>
      </thead>
      <tbody>
        {assign var="rowClass" value="odd-row"}
        {foreach from=$drillData item=row}
          <tr id="row1" class={$rowClass}>
            <td hidden="1">{$row.case_id}</td>
            <td hidden="1">{$row.activity_id}</td>
            <td hidden="1">{$row.client_id}</td>
            <td hidden="1">{$row.expert_id}</td>
            <td>{$row.type}</td>
            <td>{$row.objective}</td>
            <td>{$row.client}</td>
            <td>{$row.expert}</td>
            <td>{$row.start_date}</td>
            <td>{$row.end_date}</td>
            <td>{$row.status}</td>
            <td>
              <span>
                {foreach from=$row.actions item=actionLink}
                  {$actionLink}
                {/foreach}
              </span>
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>    
  </div>
  <div class="crm-submit-buttons">
    <span class="crm-button">
      <input id="done-drill" class="form-button" type="button" value="Done" name="done" onclick="window.location='{$doneUrl}'">
    </span>
    {if $caseUrl}
      <span class="crm-button">
        <input id="add-case" class="form-button" type="button" value="Create case" name="add-case" onclick="window.location='{$caseUrl}'">
      </span>
    {/if}
  </div>            
</div>