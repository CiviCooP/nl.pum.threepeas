<?php
set_time_limit(0);
/**
 * Relationship.CheckProject API
 * PUM issue 3287 daily check to process relationships for projects if required based
 * on start date or end date
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 2 June 2016
 */

function civicrm_api3_relationship_checkproject($params) {
  $returnValues = array();
  $validRelations = _get_valid_relations();
  $countryQuery = 'SELECT DISTINCT(country_id) AS contact_id, "country" AS project_type FROM civicrm_project WHERE country_id IS NOT NULL';
  $country = CRM_Core_DAO::executeQuery($countryQuery);
  while ($country->fetch()) {
    _process_contact($country, $validRelations);
  }
  $customerQuery = 'SELECT DISTINCT(customer_id) AS contact_id, "customer" AS project_type FROM civicrm_project WHERE customer_id IS NOT NULL';
  $customer = CRM_Core_DAO::executeQuery($customerQuery);
  while ($customer->fetch()) {
    _process_contact($customer, $validRelations);
  }
  return civicrm_api3_create_success($returnValues, $params, 'Relationship', 'CheckProject');
}

/**
 * Function to process each contact
 *
 * @param $contact
 * @param $validRelations
 */
function _process_contact($contact, $validRelations) {
  foreach ($validRelations as $validRelationshipTypeId => $columnName) {
    if ($contact->project_type == 'country') {
      $typeColumn = 'country_id';
    } else {
      $typeColumn = 'customer_id';
    }
    $contactRelationId = _get_contact_relation($contact, $columnName);
    if (empty($contactRelationId)) {
      $query = 'UPDATE civicrm_project SET '.$columnName.' = NULL WHERE '.$typeColumn.' = %1';
      $params = array(1 => array($contact->contact_id, 'Integer'));
    } else {
      $query = 'UPDATE civicrm_project SET '.$columnName.' = %1 WHERE '.$typeColumn.' = %2';
      $params = array(1 => array($contactRelationId, 'Integer'), 2 => array($contact->contact_id, 'Integer'));
    }
    CRM_Core_DAO::executeQuery($query, $params);
  }
}

/**
 * Function to get the relevant contact id of the relation
 *
 * @param $contact
 * @param $columnName
 * @return integer|bool
 */
function _get_contact_relation($contact, $columnName) {
  $foundContactId = FALSE;
  switch ($columnName) {
    case 'anamon_id':
      if ($contact->project_type == 'customer') {
        $foundContactId = CRM_Threepeas_BAO_PumCaseRelation::getAnamonId($contact->contact_id);
      }
    break;
    case 'country_coordinator_id':
      $foundContactId = CRM_Threepeas_BAO_PumCaseRelation::getCountryCoordinatorId($contact->contact_id);
      break;
    case 'project_officer_id':
      $foundContactId = CRM_Threepeas_BAO_PumCaseRelation::getProjectOfficerId($contact->contact_id);
      break;
    case 'sector_coordinator_id':
      if ($contact->project_type == 'customer') {
        $foundContactId = CRM_Threepeas_BAO_PumCaseRelation::getSectorCoordinatorId($contact->contact_id);
      }
  }
  return $foundContactId;
}

/**
 * Function to get the required valid relationship types with their column names
 *
 * @throws Exception when error from API
 * @return array
 */
function _get_valid_relations() {
  $result = array();
  $names = array(
    'Anamon' => 'anamon_id',
    'Country Coordinator is' => 'country_coordinator_id',
    'Project Officer for' => 'project_officer_id',
    'Sector Coordinator' => 'sector_coordinator_id');
  foreach ($names as $name => $column) {
    try {
      $relTypeId = civicrm_api3('RelationshipType', 'Getvalue', array('name_a_b' => $name, 'return' => 'id'));
      $result[$relTypeId] = $column;
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a single relationship type with the name '.$name.' in '.__METHOD__
        .', contact your system administrator. Error from API RelationshipType Getvalue: '.$ex->getMessage());
    }
  }
  return $result;
}
