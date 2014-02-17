<div class="crm-content-block crm-block">
    <div class="action-link">
        <a class="button new-option" href="{$pumProgramDivisionUrl}&action=add">
            <span><div class="icon add-icon"></div>Add Country</span>
        </a>
    </div>
    <div id="program_wrapper" class="dataTables_wrapper">
        <table id="program-table" class="display">
            <thead>
                <tr>
                    <th class="sorting" rowspan="1" colspan="1">Country</th>
                    <th class="sorting" rowspan="1" colspan="1">Max Projects</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Min Project</th>
                    <th class="sorting" rowspan="1" colspan="1">Max Budget</th>
                    <th class="sorting" rowspan="1" colspan="1">Min Budget</th>
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
                            <span>
                                <a class="action-item action-item-first" title="Edit division" href="{$pumProgramDivisionUrl}&action=edit&pid={$programDivsion.id}">Edit</a>
                                <a class="action-item" title="Delete dvision" href="{$delProgramDivisionUrl}&action=delete&pid={$programDivision.id}">Delete</a>
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
    <div class="action-link">
        <a class="button new-option" href="{$pumProgramDivisionUrl}&action=add">
            <span><div class="icon add-icon"></div>Add Country</span>
        </a>
    </div>
</div>

