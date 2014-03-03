<div class="crm-content-block crm-block">
    <div id="help">
        The existing Programmes are listed below. You can add, edit, drill down or delete them from this screen. 
    </div>
    <div class="action-link">
        <a class="button new-option" href="{$pumProgrammeUrl}&action=add">
            <span><div class="icon add-icon"></div>Add Programme</span>
        </a>
    </div>
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
                        <td hidden="1">{$programme.contact_id_manager}</td>
                        <td>{$programme.title}</td>
                        <td>{$programme.manager_name}</td>
                        <td>{$programme.budget}</td>
                        <td>{$programme.start_date}</td>
                        <td>{$programme.end_date}</td>
                        <td>{$programme.is_active}</td>
                        <td>
                            <span>
                                <a class="action-item" title="View programme details" href="{$pumProgrammeUrl}&action=view&pid={$programme.id}">View</a>
                                <a class="action-item" title="Edit programme" href="{$pumProgrammeUrl}&action=edit&pid={$programme.id}">Edit</a>
                                <a class="action-item" title="Divide budget" href="{$divideProgrammeUrl}&pid={$programme.id}&src=programmelist">Divide budget</a>
                                <a class="action-item" title="Drill down programme" href="{$drillProgrammeUrl}&pid={$programme.id}">Drill down</a>
                                <a class="action-item" title="Disable programme" href="{$delProgrammeUrl}&pumAction=disable&programmeId={$programme.id}&pumEntity=programme">Disable</a>
                                <a class="action-item" title="Delete programme" href="{$delProgrammeUrl}&pumAction=delete&programmeId={$programme.id}&pumEntity=programme">Delete</a>
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
        <a class="button new-option" href="{$pumProgrammeUrl}&action=add">
            <span><div class="icon add-icon"></div>Add Programme</span>
        </a>
    </div>
</div>
