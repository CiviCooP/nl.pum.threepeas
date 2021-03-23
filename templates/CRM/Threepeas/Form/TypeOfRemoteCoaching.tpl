{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{literal}
<script type="text/javascript">
/* function to show or hide fields number of participants and countries depending on type of remote coaching */
function show_hide_webinar(){
  cj( document ).ready(function() {
    if(cj('#type_remote_coaching option:selected').text() == 'Webinar single country' || cj('#type_remote_coaching option:selected').text() == 'Webinar multiple countries') {
      cj('#number_participants').parent().parent().show();
      cj('.advmultselect').parent().parent().show();
    } else {
      cj('#number_participants').parent().parent().hide();
      cj('.advmultselect').parent().parent().hide();
    }
  });
}

cj( document ).ready(function() {
  cj('#number_participants').parent().parent().hide();
  cj('.advmultselect').parent().parent().hide();

  show_hide_webinar();

  cj('#type_remote_coaching').change(function(){
    show_hide_webinar();
  });
});
</script>
{/literal}