<h3>{$action|capitalize} Program</h3>
<form id="program-edit" name="Program" method="post" action={$submitUrl}>
    <div class="crm-block crm-form-block">
        <div class="crm-submit-buttons">
            <span class="crm-button">
                <input id="save-program" class="validate form-submit default" type="submit" value="Save" name="saveProgram" accesskey="S">
            </span>
            <span class="crm-button">
                <input id="cancel-program" class="cancel-form-submit" type="submit" value="Cancel" name="cancelProgram">
            </span>
        </div>
        <table class="form-layout-compressed">
            <tbody>
                <tr>
                    <td class="label"><label for="program-title">Title<span class="crm-marker" title="This field is required.">*</span></label></td>
                    <td><input id="program-title" class= "form-text huge required"type="text" size="80" maxlength="80" value={$programTitle}></td>
                </tr>
                <tr>
                    <td class="label"><label for="program-description">Description</label></td>
                    <td><textarea id="program-description" class="form-textarea" name="description" cols="80" rows="3"></textarea>
                </tr>
                <tr>
                    <td class="label"><label for="program-manager">Manager</label></td>
                    <td>
                        <select id="program-manager" class="form-select" name"programSelect">
                            <option value="Lowieke de Vos">Lowieke de Vos</option>
                            <option value="Truus de Mier">Truus de Mier</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="crm-submit-buttons">
            <span class="crm-button">
                <input id="save-program" class="validate form-submit default" type="submit" value="Save" name="saveProgram" accesskey="S">
            </span>
            <span class="crm-button">
                <input id="cancel-program" class="cancel-form-submit" type="submit" value="Cancel" name="cancelProgram">
            </span>
        </div>
    </div>
</form>

