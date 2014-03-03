<h3>{$action|capitalize} Program</h3>
<form id="programme-edit" name="Programme" method="post" action={$submitProgrammeUrl}>
    
    {* hidden fields to pass on entity and action to processing class *}
    <input type="hidden" name= "pumEntity" value="programme">
    <input type="hidden" name="pumAction" value={$action}>
    {if $action ne 'add'}
        <input type="hidden" name="programmeId" value={$programmeId}>
    {/if}
    
    <div class="crm-block crm-form-block">
        <div class="crm-submit-buttons">
            {if $action eq 'view'}
                <span class="crm-button">
                    <input id="done-programme" class="form-button" type="button" value="Done" name="done" onclick="window.location='{$doneUrl}'">
                </span>
            {else}
                <span class="crm-button">
                    <input id="save-done-programme" class="validate form-submit default" type="submit" value="Save" name="saveProgramme" accesskey="S">
                </span>
                <span class="crm-button">
                    <input id="save-divide-programme" class="validate form-submit default" type="submit" value="Save and divide budget" name="saveProgramme">
                </span>
                <span class="crm-button">
                    <input id="cancel-programme" class="form-button" type="button" value="Cancel" name="cancel" onclick="window.location='{$cancelUrl}'">
                </span>
            {/if}
        </div>
        <table class="form-layout-compressed">
            <tbody>
                <tr>
                    <td class="label">{$labels.programmeTitle}</td>
                    <td>{$programmeTitle}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.programmeDescription}</td>
                    <td>{$programmeDescription}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.programmeManager}</td>
                    <td>{$programmeManager}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.budget}</td>
                    <td>{$programmeBudget}</td>                
                </tr>
                <tr>
                    <td class="label">{$labels.goals}</td>
                    <td>{$programmeGoals}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.requirements}</td>
                    <td>{$programmeRequirements}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.startDate}</td>
                    {if $action eq 'view'}
                        <td>{$programmeStartDate}</td>
                    {else}
                        <td>
                            {assign var='elementDate' value="programmeStartDate"}
                            <input id="{$elementDate}" class="form-text" type="text" value="{$displayStartDate}" 
                                   name="programmeStartDate" format="dd-mm-yy" endoffset="20" startoffset="20" 
                                   formattype="searchDate" style="display: none;">
                            {include file="CRM/Threepeas/Page/pum_jcal.tpl"}
                        </td>
                    {/if}
                </tr>
                <tr>
                    <td class="label">{$labels.endDate}</td>
                    {if $action eq 'view'}
                        <td>{$programmeEndDate}</td>
                    {else}
                        <td>
                            {assign var='elementDate' value="programmeEndDate"}
                            <input id="{$elementDate}" class="form-text" type="text" value="{$displayEndDate}" 
                                   name="programmeEndDate" format="dd-mm-yy" endoffset="20" startoffset="20" 
                                   formattype="searchDate" style="display: none;">
                            {include file="CRM/Threepeas/Page/pum_jcal.tpl"}
                        </td>
                    {/if}
                </tr>
                <tr>
                    <td class="label">{$labels.isActive}</td>
                    <td>{$programmeIsActive}</td>
                </tr>
            </tbody>
        </table>
        {if $action eq 'view'}
            {include file="CRM/Threepeas/Page/CurrentProgrammeDivisions.tpl"}
        {/if}                
        <div class="crm-submit-buttons">
            {if $action eq 'view'}
                <span class="crm-button">
                    <input id="done-programme" class="form-button" type="button" value="Done" name="done" onclick="window.location='{$doneUrl}'">
                </span>
            {else}
                <span class="crm-button">
                    <input id="save-done-programme" class="validate form-submit default" type="submit" value="Save" name="saveProgramme" accesskey="S">
                </span>
                <span class="crm-button">
                    <input id="save-divide-programme" class="validate form-submit default" type="submit" value="Save and divide budget" name="saveProgramme">
                </span>
                <span class="crm-button">
                    <input id="cancel-programme" class="form-button" type="button" value="Cancel" name="cancel" onclick="window.location='{$cancelUrl}'">
                </span>
            {/if}
        </div>
    </div>
</form>
