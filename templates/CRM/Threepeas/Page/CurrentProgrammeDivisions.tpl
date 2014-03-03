<h3>Current Budget Divisions</h3>
<table id="current-division-table" class="display">
    <thead>
        <tr>
            <th class="sorting-disabled" rowspan="1" colspan="1">Country</th>
            <th class="sorting-disabled" rowspan="1" colspan="1">Min Projects</th>
            <th class="sorting-disabled" rowspan="1" colspan="1">Max Project</th>
            <th class="sorting-disabled" rowspan="1" colspan="1">Min Budget</th>
            <th class="sorting-disabled" rowspan="1" colspan="1">Max Budget</th>
            <th class="sorting_disabled" rowspan="1" colspan="1"></th>
        </tr>
    </thead>
    <tbody>
        {assign var="rowClass" value="odd-row"}
        {foreach from=$pumProgrammeDivisions item=programmeDivision}
            <tr id="row1" class={$rowClass}>
                <td hidden="1">{$programmeDivision.id}</td>
                <td hidden="1">{$programmeDivision.program_id}</td>
                <td>{$programmeDivision.country}</td>
                <td>{$programmeDivision.max_projects}</td>
                <td>{$programmeDivision.min_projects}</td>
                <td>{$programmeDivision.max_budget}</td>
                <td>{$programmeDivision.min_budget}</td>
                <td>
                    {if $action ne "view"}
                        <span>
                            <a class="action-item" title="Delete division" href="{$delProgrammeDivisionUrl}&action=delete&pid={$programmeDivision.id}">Delete</a>
                        </span>
                    {/if}
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
