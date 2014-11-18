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
  public static function create_default_case_roles($case_id, $client_id, $case_start_date, $case_type_id) {
    $case_relation_config = CRM_Threepeas_CaseRelationConfig::singleton();
    $case_type = self::get_case_type_label($case_type_id);
    $case_roles = $case_relation_config->get_case_type_relations($case_type);
    foreach ($case_roles as $case_role_label => $case_role_active) {
      if ($case_role_active == 1) {
        $case_role_id = self::call_case_role_method($case_role_label, $client_id);
        self::create_case_relation($case_id, $client_id, $case_role_id, $case_start_date, $case_role_label);
      }
    }
  }
  /**
   * Function to set sector coordinator role for case from activity
   * 
   * @param obj $object_ref
   * @throws Exception when $object_ref is not an object
   * @access public
   * @static
   */
  public static function set_sector_coordinator_from_activity($object_ref) {
    if (!is_object($object_ref)) {
      throw new Exception('Function set_sector_coordinator_assessment_rep in '
        . 'CRM_Threepeas_BAO_PumCaseRelation expects object as param.');
    }
    $case_relation_config = CRM_Threepeas_CaseRelationConfig::singleton();
    if (isset($object_ref->status_id) && $object_ref->status_id = 
      $case_relation_config->get_activity_status_completed()) {
      if (isset($object_ref->case_id) && !empty($object_ref->case_id)) {
        self::set_sector_coordinator_for_case($object_ref->case_id);
      }
    }
  }
  /**
   * Function to get coordinator/rep/etc.
   * 
   * @param int $contact_id
   * @param string $case_role_label
   * @return int $case_role_id
   * @access public
   * @static
   */
  public static function get_relation_id($contact_id, $case_role_label) {
    $case_role_id = self::call_case_role_method($case_role_label, $contact_id);
    return $case_role_id;
  }
  /**
   * Function to set sector coordinator for case
   * 
   * @param int $case_id
   * @access protected
   * @static
   */
  protected static function set_sector_coordinator_for_case($case_id) {
    $client_id = self::get_case_client($case_id);
    $sector_coordinator_id = self::get_sector_coordinator_id($client_id);
    $case_start_date = self::get_case_start_date($case_id);
    self::create_case_relation($case_id, $client_id, $sector_coordinator_id, $case_start_date, 
      'sector_coordinator');
  }
  /**
   * Function to retrieve client_id of case
   * 
   * @param int $case_id
   * @return int $client_id
   * @access protected
   * @static
   */
  protected static function get_case_client($case_id) {
    $params = array(
      'case_id' => $case_id,
      'return' => 'client_id');
    $case_client_ids = civicrm_api3('Case', 'Getvalue', $params);
    foreach ($case_client_ids as $case_client_id) {
      $client_id = $case_client_id;
    }
    return $client_id;
  }
  /**
   * Function to get start_date fo case
   * 
   * @param int $case_id
   * @return date $case_start_date
   * @access protected
   * @static
   */
  protected static function get_case_start_date($case_id) {
    $params = array(
      'case_id' => $case_id,
      'return' => 'start_date');
    $case_start_date = civicrm_api3('Case', 'Getvalue', $params);
    return $case_start_date;
  }
  /**
   * Function to get the relationship for a specific type from a specific contact
   * for example, country coordinator for a customer or country coordinator for a 
   * country
   * 
   * @param string $case_role_label
   * @param int $source_contact_id
   * @return int $found_contact_id
   * @access protected
   * @static
   */
  protected static function get_default_relation($case_role_label, $source_contact_id) {
    $found_contact_id = 0;
    $case_relation_config = CRM_Threepeas_CaseRelationConfig::singleton();
    $relationship_type_id = $case_relation_config->get_relationship_type_id($case_role_label);
    $relationships = self::get_active_relationships($relationship_type_id, $source_contact_id);
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
  protected static function get_active_relationships($relationship_type_id, $source_contact_id) {
    $params = array(
      'is_active' => 1,
      'relationship_type_id' => $relationship_type_id,
      'contact_id_a' => $source_contact_id,
      'options' => array('sort' => 'start_date DESC', 'limit' => 99999));
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
   * @param int $case_id
   * @param int $contact_id_a
   * @param int $contact_id_b
   * @param string $start_date
   * @param string $case_role_label
   * @access protected
   * @static
   */
  protected static function create_case_relation($case_id, $contact_id_a, $contact_id_b, 
    $start_date, $case_role_label) {
    if (!empty($contact_id_a) && !empty($contact_id_b)) {
      $params = self::set_case_relation_params($case_id, $contact_id_a, $contact_id_b, 
        $start_date, $case_role_label);
      if (self::case_relation_exists($params) == FALSE) {
        self::create_relationship_record($params);
      }
    }
  }
  /**
   * Function to check if the to be created case relation already exists
   * 
   * @param array $params
   * @return boolean
   */
  protected static function case_relation_exists($params) {
    try {
      $case_relation_count = civicrm_api3('Relationship', 'Getcount', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
    if ($case_relation_count == 0) {
      return FALSE;
    } else {
      return TRUE;
    }
  }
  /**
   * Function to create relationship record with api
   * 
   * @param array $params
   * @throws Exception when error in create
   */
  protected static function create_relationship_record($params) {
    try {
      civicrm_api3('Relationship', 'Create', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not create Relationship of type '.$params['relationship_type_id']
        .' for case '.$params['case_id'].', error from API Relationship Create: '
        .$ex->getMessage());
    }
  }
  
  /**
   * Function to set parameters for case relation create
   * 
   * @param int $case_id
   * @param int $contact_id_a
   * @param int $contact_id_b
   * @param date $start_date
   * @param label $case_role_label
   * @return array
   */
  protected static function set_case_relation_params($case_id, $contact_id_a, $contact_id_b, 
    $start_date, $case_role_label) {
    $case_relation_config = CRM_Threepeas_CaseRelationConfig::singleton();
    $relationship_type_id = $case_relation_config->get_relationship_type_id($case_role_label);
    $params = array(
      'contact_id_a' => $contact_id_a,
      'contact_id_b' => $contact_id_b,
      'case_id' => $case_id,
      'relationship_type_id' => $relationship_type_id);
    if (!empty($start_date)) {
      $params['start_date'] = date('Ymd', strtotime($start_date));
    }
    return $params;
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
  protected static function get_case_type_label($case_type_id) {
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
  protected static function call_case_role_method($case_role_label,$client_id) {
    $method_name = 'get_'.$case_role_label.'_id';
    if (method_exists('CRM_Threepeas_BAO_PumCaseRelation', $method_name)) {
      return self::$method_name($client_id);
    } else {
      throw new Exception('Could not find method '.$method_name.' in class CRM_Threepeas_BAO_PumCaseRelation');
    }
  }
  /**
   * Function to get country coordinator from country
   * @param int $client_id
   * @return int $country_coordinator_id
   * @access protected
   * @static
   */
  protected static function get_country_coordinator_id($client_id) {
    if (self::is_contact_country($client_id) == FALSE) {
      $country_id = self::get_customer_country($client_id);
    } else {
      $country_id = $client_id;
    }
    if (!empty($country_id)) {
      $country_coordinator_id = self::get_default_relation('country_coordinator', $country_id);
    } else {
      $country_coordinator_id = 0;
    }
    return $country_coordinator_id;
  }
  /**
   * Function to determine if contact is a country
   * 
   * @param int $contact_id
   * @return boolean
   * @access protected
   * @static
   */
  protected static function is_contact_country($contact_id) {
    $params = array(
      'id' => $contact_id,
      'return' => 'contact_sub_type'
    );
    try {
      $contact_sub_types = civicrm_api3('Contact', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
    foreach ($contact_sub_types as $contact_sub_type) {
      $threepeas_config = CRM_Threepeas_Config::singleton();
      if ($contact_sub_type == $threepeas_config->countryContactType) {
        return TRUE;
      }
    }
  }
  /**
   * Function to get anamon from country
   * 
   * @param int $client_id
   * @return int $anamon_id
   * @access protected
   * @static
   */
  protected static function get_anamon_id($client_id) {
    $country_id = self::get_customer_country($client_id);
    if (!empty($country_id)) {
      $anamon_id = self::get_default_relation('anamon', $country_id);
    } else {
      $anamon_id = 0;
    }
    return $anamon_id;
  }
  /**
   * Function to get project officer from country
   * 
   * @param int $client_id
   * @param date $case_start_date
   * @access protected
   * @static
   */
  protected static function get_project_officer_id($client_id) {
    if (self::is_contact_country($client_id) == FALSE) {
      $country_id = self::get_customer_country($client_id);
    } else {
      $country_id = $client_id;
    }
    if (!empty($country_id)) {
      $project_officer_id = self::get_default_relation('project_officer', $country_id);
    } else {
      $project_officer_id = 0;
    }
    return $project_officer_id;
  }
  /**
   * Function to get sector coordinator from customer
   * 
   * @param int $client_id
   * @param date $case_start_date
   * @return int $sector_coordinator_id
   * @access protected
   * @static
   */
  protected static function get_sector_coordinator_id($contact_id) {
    $sector_coordinator_id = 0;
    $contact_tags = self::get_contact_tags($contact_id);
    foreach ($contact_tags as $contact_tag) {
      if (self::is_sector_tag($contact_tag['tag_id']) == TRUE) {
        $sector_coordinator_id = self::get_enhanced_tag_coordinator($contact_tag['tag_id']);
      }
    }
    return $sector_coordinator_id;
  }
  /**
   * Function to get the coordinator for a tag
   * 
   * @param int $tag_id
   * @return int $coordinator_id
   * @access protected
   * @static
   */
  protected static function get_enhanced_tag_coordinator($tag_id) {
    $params = array(
      'is_active' => 1,
      'tag_id' => $tag_id,
      'return' => 'coordinator_id');
    try {
      $coordinator_id = civicrm_api3('TagEnhanced', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $coordinator_id = 0;
    }
    return $coordinator_id;
  }
  /**
   * Function to determine if tag is a sector tag
   * 
   * @param int $tag_id
   * @return boolean
   * @access protected
   * @static
   */
  protected static function is_sector_tag($tag_id) {
    if (empty($tag_id)) {
      return FALSE;
    }
    $threepeas_config = CRM_Threepeas_Config::singleton();
    $sector_tree = $threepeas_config->getSectorTree();
    if (in_array($tag_id, $sector_tree)) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  /**
   * Function to get contact tags for contact
   * 
   * @param int $contact_id
   * @return array
   * @throws Exception when error from API EntityTag Get
   * @access protected
   * @static
   */
  protected static function get_contact_tags($contact_id) {
    $params = array(
      'entity_table' => 'civicrm_contact',
      'entity_id' => $contact_id,
      'options' => array('limit' => 99999));
    try {
      $contact_tags = civicrm_api3('EntityTag', 'Get', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Error retrieving contact tags with API EntityTag Get: '.$ex->getMessage());
    }
    return $contact_tags['values'];
  }
  /**
   * Function to get authorised contact from customer
   * 
   * @param int $client_id
   * @return int $authorised_contact_id
   * @access protected
   * @static
   */
  protected static function get_authorised_contact_id($client_id) {
    $authorised_contact_id = self::get_default_relation('authorised_contact', $client_id);
    return $authorised_contact_id;
  }
  /**
   * Function to get grant coordinator from customer or country if not on customer
   * 
   * @param int $client_id
   * @return int $grant_coordinator_id
   * @access protected
   * @static
   */
  protected static function get_grant_coordinator_id($client_id) {
    $grant_coordinator_id = self::get_default_relation('grant_coordinator', $client_id);
    if (empty($grant_coordinator_id)) {
      $country_id = self::get_customer_country($client_id);
      $grant_coordinator_id = self::get_default_relation('grant_coordinator', $country_id);
    }
    return $grant_coordinator_id;
  }
  /**
   * Function to get representative from customer or country if not on customer
   * 
   * @param int $client_id
   * @return int $representative_id
   * @access protected
   * @static
   */
  protected static function get_representative_id($client_id) {
    $representative_id = self::get_default_relation('representative', $client_id);
    if (empty($representative_id)) {
      $country_id = self::get_customer_country($client_id);
      $representative_id = self::get_default_relation('representative', $country_id);
    }
    return $representative_id;
  }
  /**
   * Function to get ceo
   * (client_id as param is not required but passed because of the generic method
   * call in callCaseRoleMethod)
   * 
   * @param int $client_id
   * @return int
   * @access protected
   * @static
   */
  protected static function get_ceo_id($client_id) {
    $case_relation_config = CRM_Threepeas_CaseRelationConfig::singleton();
    $pum_ceo = $case_relation_config->get_pum_ceo();
    return $pum_ceo['contact_id'];
  }
  /**
   * Function to get cfo
   * (client_id as param is not required but passed because of the generic method
   * call in callCaseRoleMethod)
   * 
   * @param int $client_id
   * @return int
   * @access protected
   * @static
   */
  protected static function get_cfo_id($client_id) {
    $case_relation_config = CRM_Threepeas_CaseRelationConfig::singleton();
    $pum_cfo = $case_relation_config->get_pum_cfo();
    return $pum_cfo['contact_id'];
  }
  /**
   * Function to get contact id of country of a customer
   * 
   * @param int $customer_id
   * @return int $country_id
   * @throws Exception when contact for customer not found
   * @throws Exception when contact for country not found
   */
  protected static function get_customer_country($customer_id) {
    try {
      $contact = civicrm_api3('Contact', 'Getsingle', array('id' => $customer_id));
    } catch (CiviCRM_API3_Exception $ex) {
      $country_id = 0;
    }
    if (isset($contact['country_id'])) {
      $threepeas_config = CRM_Threepeas_Config::singleton();
      $params = array(
        'custom_'.$threepeas_config->countryCustomFieldId => $contact['country_id'],
        'return' => 'id');
      try {
        $country_id = civicrm_api3('Contact', 'Getvalue', $params);
      } catch (CiviCRM_API3_Exception $ex) {
        $country_id = 0;
      }
    }
    return $country_id;
  }
}

