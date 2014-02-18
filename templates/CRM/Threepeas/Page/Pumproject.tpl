<h3>{$action|capitalize} Project</h3>
<form id="project-edit" name="Project" method="post" action={$submitProjectUrl}>
    
    {* hidden fields to pass on entity and action to processing class *}
    <input type="hidden" name= "pumEntity" value="project">
    <input type="hidden" name="pumAction" value={$action}>
    {if $action ne 'add'}
        <input type="hidden" name="projectId" value={$projectId}>
    {/if}
    
    <div class="crm-block crm-form-block">
        <div class="crm-submit-buttons">
            {if $action eq 'view'}
                <span class="crm-button">
                    <input id="done-project" class="form-button" type="button" value="Done" name="done" onclick="window.location='{$doneUrl}'">
                </span>
            {else}
                <span class="crm-button">
                    <input id="save-done-project" class="validate form-submit default" type="submit" value="Save" name="saveProject" accesskey="S">
                </span>
                <span class="crm-button">
                    <input id="cancel-project" class="form-button" type="button" value="Cancel" name="cancel" onclick="window.location='{$cancelUrl}'">
                </span>
            {/if}
        </div>
        <table class="form-layout-compressed">
            <tbody>
                <tr>
                    <td class="label">{$labels.projectProgram}</td>
                    <td>{$projectProgram}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.projectTitle}</td>
                    <td>{$projectTitle}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.projectReason}</td>
                    <td>{$projectReason}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.projectWorkDescription}</td>
                    <td>{$projectWorkDescription}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.projectQualifications}</td>
                    <td>{$projectQualifications}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.projectExpectedResults}</td>
                    <td>{$projectExpectedResults}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.projectSectorCoordinator}</td>
                    <td>{$projectSectorCoordinator}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.projectCountryCoordinator}</td>
                    <td>{$projectCountryCoordinator}</td>                
                </tr>
                <tr>
                    <td class="label">{$labels.projectOfficer}</td>
                    <td>{$projectOfficer}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.projectStartDate}</td>
                    {if $action eq 'view'}
                        <td>{$projectStartDate}</td>
                    {else}
                        <td>
                            {assign var='elementDate' value="projectStartDate"}
                            <input id="{$elementDate}" class="form-text" type="text" value="{$displayStartDate}" 
                                   name="projectStartDate" format="dd-mm-yy" endoffset="20" startoffset="20" 
                                   formattype="searchDate" style="display: none;">
                            {include file="CRM/Threepeas/Page/pum_jcal.tpl"}
                        </td>
                    {/if}
                </tr>
                <tr>
                    <td class="label">{$labels.projectEndDate}</td>
                    {if $action eq 'view'}
                        <td>{$projectEndDate}</td>
                    {else}
                        <td>
                            {assign var='elementDate' value="projectEndDate"}
                            <input id="{$elementDate}" class="form-text" type="text" value="{$displayEndDate}" 
                                   name="projectEndDate" format="dd-mm-yy" endoffset="20" startoffset="20" 
                                   formattype="searchDate" style="display: none;">
                            {include file="CRM/Threepeas/Page/pum_jcal.tpl"}
                        </td>
                    {/if}
                </tr>
                <tr>
                    <td class="label">{$labels.projectIsActive}</td>
                    <td>{$projectIsActive}</td>
                </tr>
            </tbody>
        </table>
        <div class="crm-submit-buttons">
            {if $action eq 'view'}
                <span class="crm-button">
                    <input id="done-project" class="form-button" type="button" value="Done" name="done" onclick="window.location='{$doneUrl}'">
                </span>
            {else}
                <span class="crm-button">
                    <input id="save-done-project" class="validate form-submit default" type="submit" value="Save" name="saveProject" accesskey="S">
                </span>
                <span class="crm-button">
                    <input id="cancel-project" class="form-button" type="button" value="Cancel" name="cancel" onclick="window.location='{$cancelUrl}'">
                </span>
            {/if}
        </div>
    </div>
</form>

