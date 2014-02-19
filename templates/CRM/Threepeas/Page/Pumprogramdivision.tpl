<h3>Program Details And Budget Division</h3>
<form id="program-division" name="ProgramDivision" method="post" action={$submitUrl}>
    <div class="crm-block crm-form-block">
        <div class="action-link">
            <div id="program-wrapper" class="dataTables_wrapper">
                <table id="program-table" class="form-layout-compressed">
                    <tbody>
                        <tr>
                            <td hidden="1">{$programId}</td>
                        </tr>
                        <tr>
                            <td>Program title:</td>
                            <td>{$programTitle}</td>
                        </tr>
                        <tr>    
                            <td>Program manager:</td>
                            <td>{$programManager}</td>
                        </tr>
                        <tr>
                            <td>Program budget:</td>
                            <td>{$programBudget}</td>
                        </tr>
                    </tbody>
                </table>
                <a class="button" href="{$doneUrl}">
                    <span><div class="form-button"></div>Done</span>
                </a>
            </div>
        </div>
        <div id="program-division-wrapper" class="dataTables_wrapper">
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
                                <input id="save-program-division" class="validate form-submit default" type="submit" value="Add" name="saveProgramDivision" accesskey="S">
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        {include file="CRM/Threepeas/Page/CurrentProgramDivisions.tpl"}
        <div class="action-link">
            <a class="button" href="{$doneUrl}">
                <span><div class="form-button"></div>Done</span>
            </a>
        </div>
    </div>
</form>

