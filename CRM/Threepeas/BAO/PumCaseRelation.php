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
   * @param int $caseId
   * @param int $clientId
   * @param date $caseStartDate
   * @param int $caseTypeId
   * @throws Exception when function not found
   * @access public
   * @static
   */
  public static function createDefaultCaseRoles($caseId, $clientId, $caseStartDate, $caseTypeId) {
    $caseRelationConfig = CRM_Threepeas_CaseRelationConfig::singleton();
    $caseType = self::getCaseTypeLabel($caseTypeId);
    $caseRoles = $caseRelationConfig->getCaseTypeRelations($caseType);
    foreach ($caseRoles as $caseRoleLabel => $caseRoleActive) {
      if ($caseRoleActive == 1) {
        $caseRoleId = self::callCaseRoleMethod($caseRoleLabel, $clientId);
        self::createCaseRelation($caseId, $clientId, $caseRoleId, $caseStartDate, $caseRoleLabel);
      }
    }
  }
  /**
   * Function to set sector coordinator role for case from activity
   * 
   * @param obj $objectRef
   * @throws Exception when $objectRef is not an object
   * @access public
   * @static
   */
  public static function setSectorCoordinatorFromActivity($objectRef) {
    if (!is_object($objectRef)) {
      throw new Exception('Function set_sector_coordinator_assessment_rep in '
        . 'CRM_Threepeas_BAO_PumCaseRelation expects object as param.');
    }
    $caseRelationConfig = CRM_Threepeas_CaseRelationConfig::singleton();
    if (isset($objectRef->status_id) && $objectRef->status_id =
      $caseRelationConfig->getActivityStatusCompleted()) {
      if (isset($objectRef->case_id) && !empty($objectRef->case_id)) {
        self::setSectorCoordinatorForCase($objectRef->case_id);
      }
    }
  }
  /**
   * Function to get coordinator/rep/etc.
   * 
   * @param int $contactId
   * @param string $caseRoleLabel
   * @return int $caseRoleId
   * @access public
   * @static
   */
  public static function getRelationId($contactId, $caseRoleLabel) {
    $caseRoleId = self::callCaseRoleMethod($caseRoleLabel, $contactId);
    return $caseRoleId;
  }
  /**
   * Function to set sector coordinator for case
   * 
   * @param int $caseId
   * @access protected
   * @static
   */
  protected static function setSectorCoordinatorForCase($caseId) {
    $clientId = CRM_Threepeas_Utils::getCaseClientId($caseId);
    $sectorCoordinatorId = self::getSectorCoordinatorId($clientId);
    $caseStartDate = self::getCaseStartDate($caseId);
    self::createCaseRelation($caseId, $clientId, $sectorCoordinatorId, $caseStartDate,
      'sector_coordinator');
  }
  /**
   * Function to get start_date fo case
   * 
   * @param int $caseId
   * @return date $caseStartDate
   * @access protected
   * @static
   */
  protected static function getCaseStartDate($caseId) {
    $params = array(
      'case_id' => $caseId,
      'return' => 'start_date');
    $caseStartDate = civicrm_api3('Case', 'Getvalue', $params);
    return $caseStartDate;
  }
  /**
   * Function to get the relationship for a specific type from a specific contact
   * for example, country coordinator for a customer or country coordinator for a 
   * country
   * 
   * @param string $caseRoleLabel
   * @param int $sourceContactId
   * @return int $foundContactId
   * @access protected
   * @static
   */
  protected static function getDefaultRelation($caseRoleLabel, $sourceContactId) {
    $foundContactId = 0;
    $caseRelationConfig = CRM_Threepeas_CaseRelationConfig::singleton();
    $relationshipTypeId = $caseRelationConfig->getRelationshipTypeId($caseRoleLabel);
    $relationships = self::getActiveRelationships($relationshipTypeId, $sourceContactId);
    foreach ($relationships as $relationship) {
      if (!isset($relationship['case_id'])) {
        $foundContactId = $relationship['contact_id_b'];
        break;
      }
    }
    return $foundContactId;
  }
  /**
   * Function to get active relationships
   * 
   * @param int $relationshipTypeId
   * @param int $sourceContactId
   * @return array $relationships['values']
   */
  protected static function getActiveRelationships($relationshipTypeId, $sourceContactId) {
    $params = array(
      'is_active' => 1,
      'relationship_type_id' => $relationshipTypeId,
      'contact_id_a' => $sourceContactId,
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
   * @param int $caseId
   * @param int $contactIdA
   * @param int $contactIdB
   * @param string $startDate
   * @param string $caseRoleLabel
   * @access protected
   * @static
   */
  protected static function createCaseRelation($caseId, $contactIdA, $contactIdB,
    $startDate, $caseRoleLabel) {
    if (!empty($contactIdA) && !empty($contactIdB)) {
      $params = self::setCaseRelationParams($caseId, $contactIdA, $contactIdB,
        $startDate, $caseRoleLabel);
      if (self::caseRelationExists($params) == FALSE) {
        self::createRelationshipRecord($params);
      }
    }
  }
  /**
   * Function to check if the to be created case relation already exists
   * 
   * @param array $params
   * @return boolean
   */
  protected static function caseRelationExists($params) {
    try {
      $caseRelationCount = civicrm_api3('Relationship', 'Getcount', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
    if ($caseRelationCount == 0) {
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
  protected static function createRelationshipRecord($params) {
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
   * @param int $caseId
   * @param int $contactIdA
   * @param int $contactIdB
   * @param date $startDate
   * @param label $caseRoleLabel
   * @return array
   */
  protected static function setCaseRelationParams($caseId, $contactIdA, $contactIdB,
    $startDate, $caseRoleLabel) {
    $caseRelationConfig = CRM_Threepeas_CaseRelationConfig::singleton();
    $relationshipTypeId = $caseRelationConfig->getRelationshipTypeId($caseRoleLabel);
    $params = array(
      'contact_id_a' => $contactIdA,
      'contact_id_b' => $contactIdB,
      'case_id' => $caseId,
      'relationship_type_id' => $relationshipTypeId);
    if (!empty($startDate)) {
      $params['start_date'] = date('Ymd', strtotime($startDate));
    }
    return $params;
  }
  /**
   * Function to retrieve case_type label for case_type_id
   * 
   * @param int $caseTypeId
   * @return string $caseType
   * @throws Exception when option group case_type not found
   * @throws Exception when case_type_id not found in option_value
   * @access public
   * @static
   */
  public static function getCaseTypeLabel($caseTypeId) {
    $paramsOptionGroup = array('name' => 'case_type', 'return' => 'id');
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $paramsOptionGroup);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find an option group with name case_type, error '
        . 'from API OptionGroup Getvalue: '.$ex->getMessage());
    }
    $paramsOptionValue = array(
      'option_group_id' => $optionGroupId,
      'value' => $caseTypeId,
      'return' => 'label');
    try {
      $caseType = civicrm_api3('OptionValue', 'Getvalue', $paramsOptionValue);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find an option value for case_type_id '.$caseTypeId.
        ', error from API OptionValue Getvalue: '.$ex->getMessage());
    }
    return $caseType;
  }
  /**
   * Function to merge function name and call processing function
   * 
   * @param type $caseRoleLabel
   * @throws Exception when function not found in class
   */
  protected static function callCaseRoleMethod($caseRoleLabel,$clientId) {
    $explodedLabels = explode('_', $caseRoleLabel);
    foreach ($explodedLabels as $key => $label) {
      $explodedLabels[$key] = ucfirst($label);
    }
    $roleLabel = implode($explodedLabels);
    $methodName = 'get'.$roleLabel.'Id';
    if (method_exists('CRM_Threepeas_BAO_PumCaseRelation', $methodName)) {
      return self::$methodName($clientId);
    } else {
      throw new Exception('Could not find method '.$methodName.' in class CRM_Threepeas_BAO_PumCaseRelation');
    }
  }
  /**
   * Function to get country coordinator from country
   * @param int $clientId
   * @return int $countryCoordinatorId
   * @access protected
   * @static
   */
  protected static function getCountryCoordinatorId($clientId) {
    if (CRM_Threepeas_Utils::contactIsCountry($clientId) == FALSE) {
      $countryId = self::getCustomerCountry($clientId);
    } else {
      $countryId = $clientId;
    }
    if (!empty($countryId)) {
      $countryCoordinatorId = self::getDefaultRelation('country_coordinator', $countryId);
    } else {
      $countryCoordinatorId = 0;
    }
    return $countryCoordinatorId;
  }
  /**
   * Function to get anamon from country
   * 
   * @param int $clientId
   * @return int $anamonId
   * @access protected
   * @static
   */
  protected static function getAnamonId($clientId) {
    $countryId = self::getCustomerCountry($clientId);
    if (!empty($countryId)) {
      $anamonId = self::getDefaultRelation('anamon', $countryId);
    } else {
      $anamonId = 0;
    }
    return $anamonId;
  }
  /**
   * Function to get project officer from country
   * 
   * @param int $clientId
   * @return int $projectOfficerId
   * @access protected
   * @static
   */
  protected static function getProjectOfficerId($clientId) {
    if (CRM_Threepeas_Utils::contactIsCountry($clientId) == FALSE) {
      $countryId = self::getCustomerCountry($clientId);
    } else {
      $countryId = $clientId;
    }
    if (!empty($countryId)) {
      $projectOfficerId = self::getDefaultRelation('project_officer', $countryId);
    } else {
      $projectOfficerId = 0;
    }
    return $projectOfficerId;
  }
  /**
   * Function to get sector coordinator from customer
   * 
   * @param int $contactId
   * @return int $sectorCoordinatorId
   * @access protected
   * @static
   */
  protected static function getSectorCoordinatorId($contactId) {
    $sectorCoordinatorId = 0;
    $contactTags = self::getContactTags($contactId);
    foreach ($contactTags as $contactTag) {
      if (self::isSectorTag($contactTag['tag_id']) == TRUE) {
        $sectorCoordinatorId = self::getEnhancedTagCoordinator($contactTag['tag_id']);
      }
    }
    return $sectorCoordinatorId;
  }
  /**
   * Function to get recruitment team member from customer
   * (temp not used)
   * 
   * @param int $contactId
   * @return int $recruitmentTeamId
   * @access protected
   * @static
   */
  protected static function getRecruitmentTeamId($contactId) {
    $recruitmentTeamId = 0;
    return $recruitmentTeamId;
  }
  /**
   * Function to get the coordinator for a tag
   * 
   * @param int $tagId
   * @return int $coordinatorId
   * @access protected
   * @static
   */
  protected static function getEnhancedTagCoordinator($tagId) {
    $params = array(
      'is_active' => 1,
      'tag_id' => $tagId,
      'return' => 'coordinator_id');
    try {
      $coordinatorId = civicrm_api3('TagEnhanced', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $coordinatorId = 0;
    }
    return $coordinatorId;
  }
  /**
   * Function to determine if tag is a sector tag
   * 
   * @param int $tagId
   * @return boolean
   * @access protected
   * @static
   */
  protected static function isSectorTag($tagId) {
    if (empty($tagId)) {
      return FALSE;
    }
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $sectorTree = $threepeasConfig->getSectorTree();
    if (in_array($tagId, $sectorTree)) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  /**
   * Function to get contact tags for contact
   * 
   * @param int $contactId
   * @return array
   * @throws Exception when error from API EntityTag Get
   * @access protected
   * @static
   */
  protected static function getContactTags($contactId) {
    $params = array(
      'entity_table' => 'civicrm_contact',
      'entity_id' => $contactId,
      'options' => array('limit' => 99999));
    try {
      $contactTags = civicrm_api3('EntityTag', 'Get', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Error retrieving contact tags with API EntityTag Get: '.$ex->getMessage());
    }
    return $contactTags['values'];
  }
  /**
   * Function to get authorised contact from customer
   * 
   * @param int $contactId
   * @return int $authorisedContactId
   * @access protected
   * @static
   */
  protected static function getAuthorisedContactId($contactId) {
    $authorisedContactId = self::getDefaultRelation('authorised_contact', $contactId);
    return $authorisedContactId;
  }
  /**
   * Function to get grant coordinator from customer or country if not on customer
   * 
   * @param int $contactId
   * @return int $grantCoordinatorId
   * @access protected
   * @static
   */
  protected static function getGrantCoordinatorId($contactId) {
    $grantCoordinatorId = self::getDefaultRelation('grant_coordinator', $contactId);
    if (empty($grantCoordinatorId)) {
      $countryId = self::getCustomerCountry($contactId);
      $grantCoordinatorId = self::getDefaultRelation('grant_coordinator', $countryId);
    }
    return $grantCoordinatorId;
  }
  /**
   * Function to get representative from customer or country if not on customer
   * 
   * @param int $contactId
   * @return int $representativeId
   * @access protected
   * @static
   */
  protected static function getRepresentativeId($contactId) {
    $representativeId = self::getDefaultRelation('representative', $contactId);
    if (empty($representativeId)) {
      $countryId = self::getCustomerCountry($contactId);
      $representativeId = self::getDefaultRelation('representative', $countryId);
    }
    return $representativeId;
  }
  /**
   * Function to get ceo
   * (contactId as param is not required but passed because of the generic method
   * call in callCaseRoleMethod)
   * 
   * @param int $contactId
   * @return int
   * @access protected
   * @static
   */
  protected static function getCeoId($contactId) {
    $caseRelationConfig = CRM_Threepeas_CaseRelationConfig::singleton();
    $pumCeo = $caseRelationConfig->getPumCeo();
    return $pumCeo['contact_id'];
  }
  /**
   * Function to get cfo
   * (contactId as param is not required but passed because of the generic method
   * call in callCaseRoleMethod)
   * 
   * @param int $contactId
   * @return int
   * @access protected
   * @static
   */
  protected static function getCfoId($contactId) {
    $caseRelationConfig = CRM_Threepeas_CaseRelationConfig::singleton();
    $pumCfo = $caseRelationConfig->getPumCfo();
    return $pumCfo['contact_id'];
  }
  /**
   * Function to get contact id of country of a customer
   * 
   * @param int $customerId
   * @return int $countryId
   * @throws Exception when contact for customer not found
   * @throws Exception when contact for country not found
   */
  protected static function getCustomerCountry($customerId) {
    try {
      $contact = civicrm_api3('Contact', 'Getsingle', array('id' => $customerId));
    } catch (CiviCRM_API3_Exception $ex) {
      $countryId = 0;
    }
    if (isset($contact['country_id'])) {
      $threepeasConfig = CRM_Threepeas_Config::singleton();
      $params = array(
        'custom_'.$threepeasConfig->countryCustomFieldId => $contact['country_id'],
        'return' => 'id');
      try {
        $countryId = civicrm_api3('Contact', 'Getvalue', $params);
      } catch (CiviCRM_API3_Exception $ex) {
        $countryId = 0;
      }
    }
    return $countryId;
  }

  /**
   * Method to get the expert for a case (assumption is only one per case)
   *
   * @param int $caseId
   * @return int|boolean
   * @throws Exception when relation ship type expert not found
   * @access public
   * @static
   */
  public static function getCaseExpert($caseId) {
    if (empty($caseId)) {
      return FALSE;
    }
    $relationshipTypeParams = array(
      'name_a_b' => 'Expert',
      'return' => 'id');
    try {
      $expertRelationshipTypeId = civicrm_api3('RelationshipType', 'Getvalue', $relationshipTypeParams);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception(ts('Could not find relationship type Expert, error from API RelationshipType Getvalue: '
        .$ex->getMessage()));
    }
    $relationshipParams = array(
      'relationship_type_id' => $expertRelationshipTypeId,
      'case_id' => $caseId,
      'is_active' => 1,
      'return' => 'contact_id_b');
    try {
      $expertId = civicrm_api3('Relationship', 'Getvalue', $relationshipParams);
      return $expertId;
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }
}