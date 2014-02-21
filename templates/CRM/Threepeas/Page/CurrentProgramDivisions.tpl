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
        {foreach from=$pumProgramDivisions item=programDivision}
            <tr id="row1" class={$rowClass}>
                <td hidden="1">{$programDivision.id}</td>
                <td hidden="1">{$programDivison.program_id}</td>
                <td>{$programDivision.country}</td>
                <td>{$programDivision.max_projects}</td>
                <td>{$programDivision.min_projects}</td>
                <td>{$programDivision.max_budget}</td>
                <td>{$programDivision.min_budget}</td>
                <td>
                    {if $action ne "view"}
                        <span>
                            <a class="action-item" title="Delete division" href="{$delProgramDivisionUrl}&action=delete&pid={$programDivision.id}">Delete</a>
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
