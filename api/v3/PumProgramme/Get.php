<?php
/**
 * PumProgramme.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_pum_programme_get($params) {
    $returnValues = CRM_Threepeas_BAO_PumProgramme::getValues($params);
    return civicrm_api3_create_success($returnValues, $params, 'PumProgramme', 'Get');
} 


