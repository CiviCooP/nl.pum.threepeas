<?php

/**
 * PumProject.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_pum_project_create_spec(&$spec) {
  $spec['title']['api.required'] = 1;
}

/**
 * PumProject.Create API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_pum_project_create($params) {
  if (array_key_exists('title', $params)) {
    /*
     * check if customer_id or country_id is entered
     */
    if (!array_key_exists('customer_id', $params) && !array_key_exists('country_id', $params)) {
      throw new API_Exception('There has to be a customer_id OR a country when adding a project');
    }
    $pumProject = CRM_Threepeas_BAO_PumProject::add($params);  
    return civicrm_api3_create_success($pumProject, $params, 'PumProject', 'Create');
  } else {
    throw new API_Exception('There is no title in the params for the project you are trying to create');
  }
}

