<?php

/**
 * PumProject.Getsingle API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_pum_project_getsingle_spec(&$spec) {
  $spec['project_id']['api.required'] = 1;
}

/**
 * PumProject.Getsingle API
 * 
 * Returns a single project record based on any of the fields in the 
 * table. 
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CiviCRM_API3_Exception when more than one record was found
 */
function civicrm_api3_pum_project_getsingle($params) {
  if (array_key_exists('project_id', $params) && !empty($params['project_id'])) {
    $returnValues = CRM_Threepeas_BAO_PumProject::getValues(array('id' => $params['project_id']));
    return civicrm_api3_create_success($returnValues, $params, 'PumProject', 'Getsingle');
  } else {
    throw new API_Exception('Parameter project_id is required and can not be empty');
  }
}

