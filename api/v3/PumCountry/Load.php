<?php
/**
 * PumCountry.Load API
 * Initial load of countries into contact type Country
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 21 May 2014 & 1 Jul 2014
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_pum_country_load($params) {
  /*
   * load countries
   */
  $countries = civicrm_api3('Country', 'Get', array('options' => array('limit' => 99999)));
  $returnValues['is_error'] = 0;
  $returnValues['message'] = 'Countries created or updated :';
  foreach ($countries['values'] as $countryId => $country) {
    $addedCountryId = _createCountry($countryId, $country['name']);
    if (!empty($addedCountryId)) {
      $returnValues['values'][$addedCountryId] = 'country_id '.$countryId.', name '.$country['name'];
    }
  }
  return civicrm_api3_create_success($returnValues, $params, 'PumCountry', 'Load');
}
/**
 * Function to set parameters required for country add
 * 
 * @param type $countryName
 * @return array $params
 */
function _setAddParams($countryName) {
  $threepeasConfig = CRM_Threepeas_Config::singleton();
  $params = array(
    'contact_type' => 'Organization',
    'contact_sub_type' => $threepeasConfig->countryContactType,
    'organization_name' => $countryName);
  return $params;
}
/**
 * Function to create or update a country contact
 * 
 * @param integer $countryId
 * @param string $countryName
 * @return integer $addedContactId
 */
function _createCountry($countryId, $countryName) {
  $addParams = _setAddParams($countryName, $countryId);
  $existContactId = _getExistingContactId($countryId, $countryName);
  if (!empty($existContactId)) {
    $addParams['contact_id'] = $existContactId;
  }
  $addedContact = civicrm_api3('Contact', 'Create', $addParams);
  _setCustomCountryId($addedContact['id'], $countryId);
  return $addedContact['id'];
}
/**
 * Function to set the custom field for countryId
 */
function _setCustomCountryId($entityId, $countryId) {
  $threepeasConfig = CRM_Threepeas_Config::singleton();
  $query = 'REPLACE INTO '.$threepeasConfig->countryCustomTable
    .' SET '.$threepeasConfig->countryCustomFieldColumn.' = %1, entity_id = %2';
  $params = array(1 => array($countryId, 'String'), 2 => array($entityId, 'Positive'));
  CRM_Core_DAO::executeQuery($query, $params);
}
/**
 * Function to get the contactId of the existing contact if there is one
 * 
 * @param integer $countryId
 * @param string $countryName
 * @return int $existingContactId
 */
function _getExistingContactId($countryId, $countryName) {
  $threepeasConfig = CRM_Threepeas_Config::singleton();
  if (empty($countryName) && empty($countryId)) {
    return 0;
  }
  if (!empty($countryId)) {
    $customId = 'custom_'.$threepeasConfig->countryCustomFieldId;
    $params = array($customId => $countryId, 'return' => 'id');
  } else {
    $params = array('organization_name' => $countryName, 'return' => 'id');
  }
  try {
    $existingContactId = civicrm_api3('Contact', 'Getvalue', $params);
    return $existingContactId;
  } catch (CiviCRM_API3_Exception $ex) {
    return 0;
  }
}

