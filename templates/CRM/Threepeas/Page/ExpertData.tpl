<div id='expert-cases'>
  <div class='crm-summary-row expert-cases'>
    <div class='crm-label'>{ts}Main Activities Procus{/ts}</div>
    <div class='crm-content'>{$countExpertCases}</div>
  </div>
</div>
<div id='sector-coordinator'>
  <div class='crm-summary-row sector-coordinator'>
    <div class='crm-label'>{ts}Sector Coordinator{/ts}</div>
    <div class='crm-content'>{$sectorCoordinator}</div>
  </div>
</div>
{literal}
  <script type='text/javascript'>
    cj(".crm-contact_source").parent().parent().prepend(cj("#sector-coordinator").html());
    cj(".crm-contact_type_label").parent().parent().prepend(cj("#expert-cases").html());
    cj('#expert-cases').hide();
    cj('#sector-coordinator').hide();
  </script>
{/literal}