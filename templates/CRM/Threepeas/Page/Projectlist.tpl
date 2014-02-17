<div class="crm-content-block crm-block">
    <div id="help">
        The existing Projects are listed below. You can add, edit, drill down or delete them from this screen. 
    </div>
    <div class="action-link">
        <a class="button new-option" href="{$pumProjectUrl}&action=add">
            <span><div class="icon add-icon"></div>Add Projects</span>
        </a>
    </div>
    <div id="project_wrapper" class="dataTables_wrapper">
        <table id="project-table" class="display">
            <thead>
                <tr>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Title</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Parent Program</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Sector Coordinator</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Country Coordinator</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Project Officer</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Start date</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">End date</th>
                    <th class="sorting-disabled" rowspan="1" colspan="1">Enabled</th>
                    <th class="sorting_disabled" rowspan="1" colspan="1"></th>
                </tr>
            </thead>
            <tbody>
                {assign var="rowClass" value="odd-row"}
                {foreach from=$pumProjects item=project}
                    <tr id="row1" class={$rowClass}>
                        <td hidden="1">{$project.id}</td>
                        <td hidden="1">{$project.program_id}</td>
                        <td hidden="1">{$project.sector_coordinator_id}</td>
                        <td hidden="1">{$project.country_coordinator_id}</td>
                        <td hidden="1">{$project.project_officer_id}</td>                        
                        <td>{$project.title}</td>
                        <td>{$project.program_name}</td>
                        <td>{$project.sector_coordinator_name}</td>
                        <td>{$project.country_coordinator_name}</td>
                        <td>{$project.project_officer_name}</td>
                        <td>{$project.start_date}</td>
                        <td>{$project.end_date}</td>
                        <td>{$project.is_active}</td>
                        <td>
                            <span>
                                <a class="action-item" title="View project details" href="{$pumProjectUrl}&action=view&pid={$project.id}">View</a>
                                <a class="action-item" title="Edit project" href="{$pumProjectUrl}&action=edit&pid={$project.id}">Edit</a>
                                <a class="action-item" title="Drill down project" href="{$drillProjectUrl}&pid={$project.id}">Drill down</a>
                                <a class="action-item" title="Delete program" href="{$delProjectUrl}&action=delete&pid={$project.id}&entity=project">Delete</a>
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
        <a class="button new-option" href="{$pumProjectUrl}&action=add">
            <span><div class="icon add-icon"></div>Add Projects</span>
        </a>
    </div>
</div>
