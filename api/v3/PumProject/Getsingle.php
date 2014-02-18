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
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_pum_project_getsingle($params) {
  if (array_key_exists('project_id', $params) && !empty($params['project_id'])) {
    $returnValues = CRM_Threepeas_PumProject::getProjectById($params['project_id']);
    return civicrm_api3_create_success($returnValues, $params, 'PumProject', 'Getsingle');
  } else {
    throw new API_Exception('Params has to contain a valid project_id');
  }
}

