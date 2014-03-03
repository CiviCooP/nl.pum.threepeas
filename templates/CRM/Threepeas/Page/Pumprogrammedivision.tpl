<h3>Programme Details And Budget Division</h3>
<form id="programme-division" name="ProgrammeDivision" method="post" action={$submitUrl}>
    <input type="hidden" name="programmeId" value={$programmeId}>
    <div class="crm-block crm-form-block">
        <div class="action-link">
            <div id="programme-wrapper" class="dataTables_wrapper">
                <table id="programme-table" class="form-layout-compressed">
                    <tbody>
                        <tr>
                            <td>Programme title:</td>
                            <td>{$programmeTitle}</td>
                        </tr>
                        <tr>    
                            <td>Programme manager:</td>
                            <td>{$programmeManager}</td>
                        </tr>
                        <tr>
                            <td>Programme budget:</td>
                            <td>{$programmeBudget}</td>
                        </tr>
                    </tbody>
                </table>
                <a class="button" href="{$doneUrl}">
                    <span><div class="form-button"></div>Done</span>
                </a>
            </div>
        </div>
        <div id="programme-division-wrapper" class="dataTables_wrapper">
            <table id="new-division-table" class="display">
                <tbody>
                    <tr id="head-new-row" class="odd-row">
                        <td>Country</td>
                        <td>Min Projects</td>
                        <td>Max Projects</td>
                        <td>Min Budget</td>
                        <td>Max Budget</td>
                        <td></td>
                    </tr>
                    <tr id="new-row" class="even-row">
                        <td>{$newCountry}</td>
                        <td>{$newMinProjects}</td>
                        <td>{$newMaxProjects}</td>
                        <td>{$newMinBudget}</td>
                        <td>{$newMaxBudget}</td>
                        <td>
                            <span class="crm-button">
                                <input id="save-programme-division" class="validate form-submit default" type="submit" value="Add" name="saveProgrammeDivision" accesskey="S">
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        {include file="CRM/Threepeas/Page/CurrentProgrammeDivisions.tpl"}
        <div class="action-link">
            <a class="button" href="{$doneUrl}">
                <span><div class="form-button"></div>Done</span>
            </a>
        </div>
    </div>
</form>

