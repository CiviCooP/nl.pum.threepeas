<h3>{$pageTitle}</h3>
<div class="crm-content-block crm-block">
    <div class="crm-submit-buttons">
        <span class="crm-button">
            <input id="done-drill" class="form-button" type="button" value="Done" name="done" onclick="window.location='{$doneUrl}'">
        </span>
    </div>            
    <div id="project-drill-wrapper" class="dataTables_wrapper">
        <table id="project-drill-table" class="display">
            <thead>
                <tr>
                    <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.subject}</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.type}</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.status}</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.client}</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.activity}</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.activity_type}</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">{$productLabel.activity_status}</th>
                </tr>
            </thead>
            <tbody>
                {assign var="rowClass" value="odd-row"}
                {foreach from=$drillData item=row}
                    <tr id="row1" class={$rowClass}>
                        <td hidden="1">{$row.case_id}</td>
                        <td hidden="1">{$row.activity_id}</td>
                        <td hidden="1">{$row.client_id}</td>
                        <td>{$row.subject}</td>
                        <td>{$row.type}</td>
                        <td>{$row.status}</td>
                        <td>{$row.client}</td>
                        <td>{$row.activity}</td>
                        <td>{$row.activity_type}</td>
                        <td>{$row.activity_status}</td>
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
