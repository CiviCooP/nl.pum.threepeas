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
    if(cj('#type_remote_coaching option:selected').text() == 'Webinar multiple countries') {
      cj('#number_participants').parent().parent().show();
      cj('.advmultiselect').parent().parent().show();
    } else if (cj('#type_remote_coaching option:selected').text() == 'Webinar single country') {
      cj('#number_participants').parent().parent().show();
      cj('.advmultiselect').parent().parent().hide();
    } else {
      cj('#number_participants').parent().parent().hide();
      cj('.advmultiselect').parent().parent().hide();
    }

    cj('#number_participants').keypress(function (e) {
      var allowedChars = '0123456789';
      function contains(stringValue, charValue) {
          return stringValue.indexOf(charValue) > -1;
      }
      var invalidKey = e.key.length === 1 && !contains(allowedChars, e.key) || e.key === '.' && contains(e.target.value, '.');
      if(invalidKey) {
        e.preventDefault();
      }
    });
  });
}

function sortSelectOptions(selector, skip_first) {
    var options = (skip_first) ? cj(selector + ' option:not(:first)') : cj(selector + ' option');
    var arr = options.map(function(_, o) { return { t: cj(o).text(), v: o.value, s: cj(o).prop('selected') }; }).get();
    arr.sort(function(o1, o2) {
      var t1 = o1.t.toLowerCase(), t2 = o2.t.toLowerCase();
      return t1 > t2 ? 1 : t1 < t2 ? -1 : 0;
    });
    options.each(function(i, o) {
        o.value = arr[i].v;
        cj(o).text(arr[i].t);
        if (arr[i].s) {
            cj(o).attr('selected', 'selected').prop('selected', true);
        } else {
            cj(o).removeAttr('selected');
            cj(o).prop('selected', false);
        }
    });
}

cj( document ).ready(function() {
  cj('#number_participants').parent().parent().hide();
  cj('.advmultiselect').parent().parent().hide();
  sortSelectOptions('#countries-t', false);

  show_hide_webinar();

  cj('#type_remote_coaching').change(function() {
    show_hide_webinar();
  });
  cj('#TypeOfRemoteCoaching .advmultiselect input').click(function() {
    sortSelectOptions('#countries-t', false);
  });
  cj('#countries-f').dblclick(function() {
    sortSelectOptions('#countries-t', false);
  });
});
</script>
{/literal}