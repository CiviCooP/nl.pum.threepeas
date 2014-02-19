<div class="crm-content-block crm-block">
    <div id="help">
        The existing Programs are listed below. You can add, edit, drill down or delete them from this screen. 
    </div>
    <div class="action-link">
        <a class="button new-option" href="{$pumProgramUrl}&action=add">
            <span><div class="icon add-icon"></div>Add Program</span>
        </a>
    </div>
    <div id="program_wrapper" class="dataTables_wrapper">
        <table id="program-table" class="display">
            <thead>
                <tr>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Title</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Program Manager</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Budget</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Start date</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">End date</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Enabled</th>
                    <th class="sorting_disabled" rowspan="1" colspan="1"></th>
                </tr>
            </thead>
            <tbody>
                {assign var="rowClass" value="odd-row"}
                {foreach from=$pumPrograms item=program}
                    <tr id="row1" class={$rowClass}>
                        <td hidden="1">{$program.id}</td>
                        <td hidden="1">{$program.contact_id_manager}</td>
                        <td>{$program.title}</td>
                        <td>{$program.manager_name}</td>
                        <td>{$program.budget}</td>
                        <td>{$program.start_date}</td>
                        <td>{$program.end_date}</td>
                        <td>{$program.is_active}</td>
                        <td>
                            <span>
                                <a class="action-item" title="View program details" href="{$pumProgramUrl}&action=view&pid={$program.id}">View</a>
                                <a class="action-item" title="Edit program" href="{$pumProgramUrl}&action=edit&pid={$program.id}">Edit</a>
                                <a class="action-item" title="Divide budget" href="{$divideProgramUrl}&pid={$program.id}&src=programlist">Divide budget</a>
                                <a class="action-item" title="Drill down program" href="{$drillProgramUrl}&pid={$program.id}">Drill down</a>
                                <a class="action-item" title="Disable program" href="{$delProgramUrl}&pumAction=disable&programId={$program.id}&pumEntity=program">Disable</a>
                                <a class="action-item" title="Delete program" href="{$delProgramUrl}&pumAction=delete&programId={$program.id}&pumEntity=program">Delete</a>
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
        <a class="button new-option" href="{$pumProgramUrl}&action=add">
            <span><div class="icon add-icon"></div>Add Program</span>
        </a>
    </div>
</div>
