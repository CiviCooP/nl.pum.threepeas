var getUrlParameter = function getUrlParameter(sParam) {
  var sPageURL = window.location.search.substring(1),
      sURLVariables = sPageURL.split('&'),
      sParameterName,
      i;

  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split('=');

    if (sParameterName[0] === sParam) {
      return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
    }
  }
  return false;
};

var sleep = function(ms){
  let now = Date.now(),
      end = now + ms;
  while (now < end) {
    now = Date.now();
  }
};

cj('#Type_of_Remote_Coaching .button').click(function(e){
  e.preventDefault();
  window.location.href = "/civicrm/typeofremotecoaching?cid="+getUrlParameter('cid')+"&id="+getUrlParameter('id');
});

/* function to show values of 'Participating countries' (custom field of field group 'Type of Remote Coaching') on case */
cj( document ).ready(function() {
  var caseId = getUrlParameter('id');

  CRM.api('RemoteCoaching', 'countries', {'sequential': 0, 'entity_id': caseId},{
    success: function(data) {
      $i=0;
      if(data.values[0] !== undefined && data.values[0].hasOwnProperty('participating_countries_name')){
        cj.each(data.values[0].participating_countries_name, function(key, value) {
          if($i == 0){
            cj('#Type_of_Remote_Coaching .crm-accordion-body .crm-info-panel:nth-child(3) .html-adjust').append(value);
          } else {
            cj('#Type_of_Remote_Coaching .crm-accordion-body .crm-info-panel:nth-child(3) .html-adjust').append(','+value);
          }
          $i++;
        });
      }
    },
    error: function(data){

    }
  });
});