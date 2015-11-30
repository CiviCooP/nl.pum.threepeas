<div id='expert-cases'>
  <div class='crm-summary-row expert-cases'>
    <div class='crm-label'>{ts}Main Activities Procus{/ts}</div>
    <div class='crm-content'>{$countExpertCases}</div>
  </div>
</div>
{literal}
  <script type='text/javascript'>
    cj("#tagLink").parent().parent().prepend(cj("#expert-cases").html());
    cj('#expert-cases').hide();
  </script>
{/literal}