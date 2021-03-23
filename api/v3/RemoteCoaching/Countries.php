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
    $params_cg_remotecoachingtypes = array(
      'version' => 3,
      'sequential' => 1,
      'name' => 'Type_of_Remote_Coaching',
    );
    $result_cg_remotecoachingtypes = civicrm_api('CustomGroup', 'getsingle', $params_cg_remotecoachingtypes);

    $sql = "SELECT * FROM {$result_cg_remotecoachingtypes['table_name']} WHERE `entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array((int)$params['entity_id'], 'Integer')));
    $i=0;
    while($dao->fetch()){
      $returnValues[$i]['case_id'] = $dao->entity_id;
      $returnValues[$i]['type_of_remote_coaching'] = $dao->type_of_remote_coaching_607;
      $returnValues[$i]['number_of_participants'] = $dao->number_of_participants_608;
      $returnValues[$i]['participating_countries'] = @unserialize($dao->participating_countries_610);
      $i++;
    }
    return civicrm_api3_create_success($returnValues, $params, 'RemoteCoaching', 'Countries');
  }
  else {
    throw new API_Exception(/*error_message*/ 'entity_id missing', /*error_code*/ 'entity_id missing');
  }
}
