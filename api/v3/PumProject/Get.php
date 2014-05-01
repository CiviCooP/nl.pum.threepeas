<?php

/**
 * PumProject.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_pum_project_get_spec(&$spec) {
    $spec['project_id']['api_required'] = 0;
}

/**
 * PumProject.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_pum_project_get($params) {
    $returnValues = CRM_Threepeas_BAO_PumProject::getValues($params);

    return civicrm_api3_create_success($returnValues, $params, 'NewEntity', 'NewAction');
}

