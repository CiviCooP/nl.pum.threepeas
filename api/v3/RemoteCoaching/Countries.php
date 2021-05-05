<?php
use CRM_Threepeas_ExtensionUtil as E;

/**
 * RemoteCoaching.Countries API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_remote_coaching_countries_spec(&$spec) {
  $spec['entity_id']['api.required'] = 1;
}

/**
 * RemoteCoaching.Countries API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_remote_coaching_countries($params) {
  if (array_key_exists('entity_id', $params) && !empty($params['entity_id'])) {
    $returnValues = array();

    $cg_typeremotecoaching = civicrm_api('CustomGroup', 'getsingle', array('version' => 3, 'sequential' => 1, 'name' => 'Type_of_Remote_Coaching'));
    $cf_typeremotecoaching = civicrm_api('CustomField', 'get', array('version' => 3, 'sequential' => 1, 'custom_group_id' => $cg_typeremotecoaching['id']));

    foreach($cf_typeremotecoaching['values'] as $key => $value){
      if($value['name'] == 'Type_of_Remote_Coaching'){
        $columns['remote_coaching'] = $value['column_name'];
        $values['remote_coaching'] = $value['id'];
      }
      if($value['name'] == 'Number_of_participants'){
        $columns['number_participants'] = $value['column_name'];
        $values['number_participants'] = $value['id'];
      }
      if($value['name'] == 'Participating_countries'){
        $columns['participating_countries'] = $value['column_name'];
        $values['participating_countries'] = $value['id'];
      }
    }


    $sql = "SELECT * FROM {$cg_typeremotecoaching['table_name']} WHERE `entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array((int)$params['entity_id'], 'Integer')));
    $i=0;

    $countries = civicrm_api('Country', 'get', array('version' => 3, 'sequential' => 1, 'sort' => 'name', 'rowCount' => 0));
    $countries_sorted = array();
    foreach($countries['values'] as $key => $value){
      $countries_sorted[$value['id']] = $value['name'];
    }

    while($dao->fetch()){
      $participating_countries = @unserialize($dao->$columns['participating_countries']);
      $participating_countries_sorted = array();
      $participating_countries_name = array();

      foreach($countries_sorted as $key => $value) {
        if(in_array($key,$participating_countries)){
          $participating_countries_sorted[] = $key;
          $participating_countries_name[] = $value;
        }
      }
      $returnValues[$i]['case_id'] = $dao->entity_id;
      $returnValues[$i]['type_of_remote_coaching'] = $dao->$columns['remote_coaching'];
      $returnValues[$i]['number_of_participants'] = $dao->$columns['number_participants'];
      $returnValues[$i]['participating_countries'] = $participating_countries_sorted;
      $returnValues[$i]['participating_countries_name'] = $participating_countries_name;
      $i++;
    }

    return civicrm_api3_create_success($returnValues, $params, 'RemoteCoaching', 'Countries');
  }
  else {
    throw new API_Exception(/*error_message*/ 'entity_id missing', /*error_code*/ 'entity_id missing');
  }
}
