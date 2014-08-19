<br />
<div id="contribution-project-wrapper" class="crm-accordion-wrapper crm-ajax-accordion crm-ContributionProject-accordion">
  <div id="contribution-project" class="crm-accordion-header">Number of Projects</div>
  <div class="crm-accordion-body" style="display: block">
    <div class="contribution-project-threepeas">
      <div id="contribution-project-threepeas" class="section-shown crm-contribution-additionalinfo-contribution-project-threepeas-form-block">
        <table class="form-layout-compressed">
          <tbody>
            <tr class="crm-contribution-form-block-number-projects-header">
              <td class="crm-contribution-form-block-number-projects-select">{$form.numberProjects.label}</td>
              <td>{$form.numberProjects.html}</td>  
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  cj('#contribution-project-wrapper').insertBefore('#softCredit');
</script>