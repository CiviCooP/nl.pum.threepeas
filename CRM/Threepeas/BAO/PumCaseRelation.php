<?php
/**
 * BAO PumCaseRelation for PUM Default Case Relations
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 11 November 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under AGPL-3.0
 */
class CRM_Threepeas_BAO_PumCaseRelation {
  /**
   * Function to set the default relations for a case
   * 
   * @param int $case_id
   * @param int $client_id
   * @param date $case_start_date
   * @param int $case_type_id
   * @throws Exception when function not found
   * @access public
   * @static
   */
  public static function createDefaultCaseRoles($case_id, $client_id, $case_start_date, $case_type_id) {
    $case_relation_config = CRM_Threepeas_CaseRelationConfig::singleton();
    $case_type = self::getCaseTypeLabel($case_type_id);
    $case_roles = $case_relation_config->getCaseTypeRelations($case_type);
    foreach ($case_roles as $case_role_label => $case_role_active) {
      if ($case_role_active == 1) {
        $case_role_id = self::callCaseRoleMethod($case_role_label, $client_id);
        self::setCaseRelation($case_id, $client_id, $case_role_id, $case_start_date, $case_type_id);
      }
    }
  }
  /**
   * Function to get the relationship for a specific type from a specific contact
   * for example, country coordinator for a customer or country coordinator for a 
   * country
   * 
   * @param string $relationship_short_name
   * @param int $source_contact_id
   * @return int $found_contact_id
   * @access protected
   * @static
   */
  protected static function getDefaultRelation($relationship_short_name, $source_contact_id) {
    $case_relation_config = CRM_Threepeas_CaseRelationConfig::singleton();
    $relationship_type_id = $case_relation_config->getRelationShipTypeId($relationship_short_name);
    $relationships = self::getActiveRelationships($relationship_type_id, $source_contact_id);
    foreach ($relationships as $relationship) {
      if (!isset($relationship['case_id'])) {
        $found_contact_id = $relationship['contact_id_b'];
        break;
      }
    }
    return $found_contact_id;
  }
  /**
   * Function to get active relationships
   * 
   * @param int $relationship_type_id
   * @param int $source_contact_id
   * @return array $relationships['values']
   */
  protected static function getActiveRelationships($relationship_type_id, $source_contact_id) {
    $params = array(
      'is_active' => 1,
      'relationship_type_id' => $relationship_type_id,
      'contact_id_a' => $source_contact_id,
      'options' => array('sort' => 'start_date DESC'));
    try {
      $relationships = civicrm_api3('Relationship','Get', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $relationships['values'] = array();
    }
    return $relationships['values'];
  }
  /**
   * Function to create a relation
   * 
   * @param int $contactAId
   * @param int $contactBId
   * @param int $caseId
   * @param int $relationshipTypeId
   * @param string $startDate
   * @access protected
   * @static
   */
  protected static function setCaseRelation($contactAId, $contactBId, $caseId, 
    $relationshipTypeId, $startDate) {
    if (!empty($contactAId) && !empty($contactBId)) {
      $params = array(
        'contact_id_a' => $contactAId,
        'contact_id_b' => $contactBId,
        'case_id' => $caseId,
        'relationship_type_id' => $relationshipTypeId);
      if (!empty($startDate)) {
        $params['start_date'] = date('Ymd', strtotime($startDate));
      }
      try {
        civicrm_api3('Relationship', 'Create', $params);
      } catch (CiviCRM_API3_Exception $ex) {
      }
    }
  }
  /**
   * Function to retrieve case_type label for case_type_id
   * 
   * @param int $case_type_id
   * @return string $case_type
   * @throws Exception when option group case_type not found
   * @throws Exception when case_type_id not found in option_value
   * @access protected
   * @static
   */
  protected static function getCaseTypeLabel($case_type_id) {
    $params_option_group = array('name' => 'case_type', 'return' => 'id');
    try {
      $option_group_id = civicrm_api3('OptionGroup', 'Getvalue', $params_option_group);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find an option group with name case_type, error '
        . 'from API OptionGroup Getvalue: '.$ex->getMessage());
    }
    $params_option_value = array(
      'option_group_id' => $option_group_id,
      'value' => $case_type_id,
      'return' => 'label');
    try {
      $case_type = civicrm_api3('OptionValue', 'Getvalue', $params_option_value);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find an option value for case_type_id '.$case_type_id.
        ', error from API OptionValue Getvalue: '.$ex->getMessage());
    }
    return $case_type;
  }
  /**
   * Function to merge function name and call processing function
   * 
   * @param type $case_role_label
   * @throws Exception when function not found in class
   */
  protected static function callCaseRoleMethod($case_role_label,$client_id) {
    $method_name = 'get_'.$case_role_label.'_id';
    if (method_exists('CRM_Threepeas_BAO_PumCaseRelation', $method_name)) {
      return self::$method_name($client_id);
    } else {
      throw new Exception('Could not find method '.$method_name.' in class CRM_Threepeas_BAO_PumCaseRelation');
    }
  }
  /**
   * Function to get country coordinator from customer, and from customer
   * country if not present on customer
   * 
   * @param int $case_id
   * @param int $client_id
   * @param date $case_start_date
   * @param int $case_type_id
   * @return int $country_coordinator_id
   * @access protected
   * @static
   */
  protected static function get_country_coordinator_id($client_id) {
    $country_id = self::getCustomerCountry($client_id);
    if (!empty($country_id)) {
      $country_coordinator_id = self::getDefaultRelation('country_coordinator', $country_id);
    } else {
      $country_coordinator_id = 0;
    }
    return $country_coordinator_id;
  }
  /**
   * Function to get contact id of country of a customer
   * 
   * @param int $customer_id
   * @return int $country_id
   * @throws Exception when contact for customer not found
   * @throws Exception when contact for country not found
   */
  protected static function getCustomerCountry($customer_id) {
    $country_id = 0;
    try {
      $contact = civicrm_api3('Contact', 'Getsingle', array('id' => $customer_id));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find contact with id '.$customer_id.
        ', error from API Contact Getsingle: '.$ex->getMessage());
    }
    if (isset($contact['country_id'])) {
      $threepeas_config = CRM_Threepeas_Config::singleton();
      $params = array(
        'custom_'.$threepeas_config->countryCustomFieldId => $contact['country_id'],
        'return' => 'id');
      try {
        $country_id = civicrm_api3('Contact', 'Getvalue', $params);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not find contact of subtype country with country id '.
          $contact['country_id'].', error from API Contact Getvalue: '.$ex->getMessage());
      }
    }
    return $country_id;
  }
}

