<h3>{$action|capitalize} Program</h3>
<form id="program-edit" name="Program" method="post" action={$submitProgramUrl}>
    <div class="crm-block crm-form-block">
        <div class="crm-submit-buttons">
            {if $action eq 'view'}
                <span class="crm-button">
                    <input id="done-program" class="form-button" type="button" value="Done" name="done" onclick="window.location='{$doneUrl}'">
                </span>
            {else}
                <span class="crm-button">
                    <input id="save-done-program" class="validate form-submit default" type="submit" value="Save" name="saveProgram" accesskey="S">
                </span>
                <span class="crm-button">
                    <input id="save-divide-program" class="validate form-submit default" type="submit" value="Save and divide budget" name="saveProgram">
                </span>
                <span class="crm-button">
                    <input id="cancel-program" class="form-button" type="button" value="Cancel" name="cancel" onclick="window.location='{$cancelUrl}'">
                </span>
            {/if}
        </div>
        <table class="form-layout-compressed">
            <tbody>
                <tr>
                    <td class="label">{$labels.program_title}</td>
                    <td>{$programTitle}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.program_desc}</td>
                    <td>{$programDescription}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.program_manager}</td>
                    <td>{$programManager}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.budget}</td>
                    <td>{$programBudget}</td>                
                </tr>
                <tr>
                    <td class="label">{$labels.goals}</td>
                    <td>{$programGoals}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.requirements}</td>
                    <td>{$programRequirements}</td>
                </tr>
                <tr>
                    <td class="label">{$labels.start_date}</td>
                    {if $action eq 'view'}
                        <td>{$programStartDate}</td>
                    {else}
                        <td>
                            {assign var='elementDate' value="programStartDate"}
                            <input id="{$elementDate}" class="form-text" type="text" value="{$displayStartDate}" 
                                   name="start_date" format="dd-mm-yy" endoffset="20" startoffset="20" 
                                   formattype="searchDate" style="display: none;">
                            {include file="CRM/Threepeas/Page/program_jcal.tpl"}
                        </td>
                    {/if}
                </tr>
                <tr>
                    <td class="label">{$labels.end_date}</td>
                    {if $action eq 'view'}
                        <td>{$programEndDate}</td>
                    {else}
                        <td>
                            {assign var='elementDate' value="programEndDate"}
                            <input id="{$elementDate}" class="form-text" type="text" value="{$displayEndDate}" 
                                   name="end_date" format="dd-mm-yy" endoffset="20" startoffset="20" 
                                   formattype="searchDate" style="display: none;">
                            {include file="CRM/Threepeas/Page/program_jcal.tpl"}
                        </td>
                    {/if}
                </tr>
                <tr>
                    <td class="label">{$labels.is_active}</td>
                    <td>{$programIsActive}</td>
                </tr>
            </tbody>
        </table>
        <div class="crm-submit-buttons">
            {if $action eq 'view'}
                <span class="crm-button">
                    <input id="done-program" class="form-button" type="button" value="Done" name="done" onclick="window.location='{$doneUrl}'">
                </span>
            {else}
                <span class="crm-button">
                    <input id="save-done-program" class="validate form-submit default" type="submit" value="Save" name="saveProgram" accesskey="S">
                </span>
                <span class="crm-button">
                    <input id="save-divide-program" class="validate form-submit default" type="submit" value="Save and divide budget" name="saveProgram">
                </span>
                <span class="crm-button">
                    <input id="cancel-program" class="form-button" type="button" value="Cancel" name="cancel" onclick="window.location='{$cancelUrl}'">
                </span>
            {/if}
        </div>
    </div>
</form>

