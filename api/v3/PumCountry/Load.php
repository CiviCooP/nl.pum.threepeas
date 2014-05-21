<?php
/**
 * PumCountry.Load API
 * Initial load of countries into contact type Country
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 21 May 20-14
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
  $returnValues[0] = 'Countries created :';
  foreach ($countries['values'] as $countryId => $country) {
    $createdCountryId = _createCountry($countryId, $country['name']);
    if (!empty($createdCountryId)) {
      $returnValues[$createdCountryId] = 'country_id '.$countryId.', name '.$country['name'];
    }
  }
  return civicrm_api3_create_success($returnValues, $params, 'PumCountry', 'Load');
}
function _createCountry($countryId, $countryName) {
  $createdCountryId = 0;
  $params = array('contact_sub_type' => 'Country', 'organization_name' => $countryName);
  $exists = civicrm_api3('Contact', 'Getcount', $params);
  if ($exists == 0) {
    $createParams = array(
      'contact_type' => 'Organization',
      'contact_sub_type' => 'Country',
      'organization_name' => $countryName);
    $createdContact = civicrm_api3('Contact', 'Create', $createParams);
    if (isset($createdContact['id'])) {
      $createdCountryId = $createdContact['id'];
    }
  }
  return $createdCountryId;
}

