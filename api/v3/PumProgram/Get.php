<?php
/**
 * PumProgram.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_pum_program_get($params) {
    $returnValues = CRM_Threepeas_PumProgram::getAllActivePrograms();
    return civicrm_api3_create_success($returnValues, $params, 'PumProgram', 'Get');
} 


