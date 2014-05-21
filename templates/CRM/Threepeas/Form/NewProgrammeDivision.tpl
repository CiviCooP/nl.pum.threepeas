<h3>New Budget Division</h3>
<div id="new-division-wrapper" class="dataTables_wrapper">
  <table id="new-division-table" class="display">
    <tbody>
      <tr id="head-new-row" class="odd-row">
        <th class="sorting-disabled">{ts}Country{/ts}</th>
        <th class="sorting-disabled">{ts}Min Projects{/ts}</th>
        <th class="sorting-disabled">{ts}Max Projects{/ts}</th>
        <th class="sorting-disabled">{ts}Min Budget{/ts}</th>
        <th class="sorting-disabled">{ts}Max Budget{/ts}</th>
        <th class="sorting-disabled"></th>
      </tr>
      <tr id="new-row" class="even-row">
        <td>{$form.division_country.html}</td>
        <td>{$form.min_projects.html}</td>
        <td>{$form.max_projects.html}</td>
        <td>{$form.min_budget.html}</td>
        <td>{$form.max_budget.html}</td>
        <td>
          <span class="crm-button">
            <input id="save-division" class="validate form-submit default" type="submit" value="Add Line" name="_qf_PumProgramme_next" accesskey="S">
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>
