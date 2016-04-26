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
    $relationships = self::getActiveContactRelationships($relationshipTypeId, $sourceContactId);
    foreach ($relationships as $relationship) {
      $foundContactId = $relationship['contact_id_b'];
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
  protected static function getActiveContactRelationships($relationshipTypeId, $sourceContactId) {
    $params = array(
      'is_active' => 1,
      'case_id' => 'null',
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
   * @param date $startDate
   * @param string $caseRoleLabel
   * @access protected
   * @static
   */
  public static function createCaseRelation($caseId, $contactIdA, $contactIdB,
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
   * @param string $caseRoleLabel
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
   * Method to build method name to get role id from label
   *
   * @param string $caseRoleLabel
   * @return string $methodName
   * @access public
   */
  public static function buildMethodName($caseRoleLabel) {
    $explodedLabels = explode('_', $caseRoleLabel);
    foreach ($explodedLabels as $key => $label) {
      $explodedLabels[$key] = ucfirst($label);
    }
    $roleLabel = implode($explodedLabels);
    $methodName = 'get'.$roleLabel.'Id';
    return $methodName;
  }
  /**
   * Function to merge function name and call processing function
   * 
   * @param string $caseRoleLabel
   * @param int $clientId
   * @throws Exception when function not found in class
   */
  protected static function callCaseRoleMethod($caseRoleLabel, $clientId) {
    $methodName = self::buildMethodName($caseRoleLabel);
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
   * @access public
   * @static
   */
  public static function getSectorCoordinatorId($contactId) {
    $sector = self::getSectorForContactId($contactId);
    $sectorCoordinatorId = CRM_Contactsegment_BAO_ContactSegment::getRoleContactActiveOnDate('Sector Coordinator', $sector, date('Ymd'));
    if (!$sectorCoordinatorId) {
      $sectorCoordinatorId = 0;
    }
    return $sectorCoordinatorId;
  }

  /**
   * Method to find sector for contact
   * if extension nl.pum.mainsector is installed it will look for the one with is_main if the contact is expert,
   * else it will pick the first parent
   *
   * @param $contactId
   * @return int
   * @access public
   * @static
   */
  public static function getSectorForContactId($contactId) {
    // get sector based on role
    if (CRM_Threepeas_Utils::contactIsExpert($contactId) == TRUE) {
      $params = array(
        'contact_id' => $contactId,
        'is_active' => 1,
        'is_main' => 1,
        'role_value' => "Expert");
      $contactSegments = civicrm_api3('ContactSegment', 'Get', $params);
    } elseif (CRM_Threepeas_Utils::contactIsCustomer($contactId) == TRUE) {
        $params = array(
          'contact_id' => $contactId,
          'is_active' => 1,
          'role_value' => "Customer");
      $contactSegments = civicrm_api3('ContactSegment', 'Get', $params);
    } else {
      $params = array('contact_id' => $contactId, 'is_active' => 1);
      $contactSegments = civicrm_api3('ContactSegment', 'Get', $params);
      foreach ($contactSegments['values'] as $key => $contactSegment) {
        if ($contactSegment['role_value'] == "Expert" || $contactSegment['role_value'] == "Customer") {
          unset($contactSegments[$key]);
        }
      }
    }

    if (isset($contactSegments['values']) && !empty($contactSegments['values'])) {
      $result = $contactSegments['values'];
      return $contactSegments['values'][key($result)]['segment_id'];
    } else {
      return NULL;
    }
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

  /**
   * Method to retrieve the contacts where the parameter contact has the parameter role for
   * (so for example all customers or countries where the contact is active project officer for)
   *
   * @param int $contactId
   * @param string $roleLabel
   * @return array $result (ids of found contacts
   * @access public
   * @static
   */
  public static function isContactRelationFor($contactId, $roleLabel) {
    $result = array();
    if (empty($contactId) || empty($roleLabel)) {
      return $result;
    }
    $caseRelationConfig = CRM_Threepeas_CaseRelationConfig::singleton();
    $relationshipTypeId = $caseRelationConfig->getRelationshipTypeId($roleLabel);
    $relationshipParams = array(
      'case_id' => 'null',
      'is_active' => 1,
      'relationship_type_id' => $relationshipTypeId,
      'options' => array('limit' => 99999),
      'contact_id_b' => $contactId,
      'return' => 'contact_id_a');
    $foundRelationships = civicrm_api3('Relationship', 'Get', $relationshipParams);
    foreach ($foundRelationships['values'] as $foundRelationship) {
      $result[] = $foundRelationship['contact_id_a'];
    }
  return $result;
  }

  /**
   * Method to retrieve all customers the contact is sectorCoordinator for
   * (first get all tags the contact is coordinator for, then select all
   * customers with the same tag)
   *
   * @param int $contactId
   * @return array $result
   * @access public
   * @static
   */
  public static function isContactSectorCoordinatorFor($contactId) {
    // TODO: refactor function to contact segment
    $result = array();
    $sectorParams = array(
      'is_active' => 1,
      'coordinator_id' => $contactId);
    $sectorTags = CRM_Enhancedtags_BAO_TagEnhanced::getValues($sectorParams);
    foreach ($sectorTags as $sectorTag) {
      $entityTagParams = array(
        'entity_table' => 'civicrm_contact',
        'tag_id' => $sectorTag['tag_id']);
      $entityTags = civicrm_api3('EntityTag', 'Get', $entityTagParams);
      foreach ($entityTags['values'] as $entityTag) {
        $result[] = $entityTag['entity_id'];
      }
    }
    return $result;
  }

  /**
   * Method to find all active projects where contact has active role in active case(s)
   *
   * @param int $contactId
   * @return array $activeProjects
   * @access public
   * @static
   */
  public static function isContactActiveInCases($contactId) {
    $activeProjects = array();
    $query = 'SELECT DISTINCT(proj.id) as project_id
      FROM civicrm_relationship rel
      JOIN civicrm_case_project cproj ON rel.case_id = cproj.case_id
      JOIN civicrm_project proj ON cproj.project_id = proj.id
      WHERE rel.case_id IS NOT NULL AND rel.is_active=%1 AND proj.is_active=%1 AND rel.contact_id_b = %2';
    $queryParams = array(
      1 => array(1, 'Integer'),
      2 => array($contactId, 'Integer'));
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    while ($dao->fetch()) {
      $projects = CRM_Threepeas_BAO_PumProject::getValues(array('id' => $dao->project_id));
      foreach ($projects as $project) {
        $activeProjects[$project['id']] = $project;
      }
    }
    return $activeProjects;
  }

  /**
   * Method to find all contacts that have an active relation with a specific label
   * (so all Country Coordinators for example)
   *
   * @param string $roleLabel
   * @return array
   * @access public
   * @static
   */
  public static function getAllActiveRelationContacts($roleLabel) {
    $foundContacts = array();
    if (empty($roleLabel)) {
      return $foundContacts;
    }
    $caseRelationConfig = CRM_Threepeas_CaseRelationConfig::singleton();
    $relationshipTypeId = $caseRelationConfig->getRelationshipTypeId($roleLabel);
    $relationParams = array(
      'is_active' => 1,
      'case_id' => 'null',
      'options' => array('limit' => 99999),
      'relationship_type_id' => $relationshipTypeId);
    try {
      $relationContacts = civicrm_api3('Relationship', 'Get', $relationParams);
      foreach ($relationContacts['values'] as $relationContact) {
        $foundContacts[$relationContact['contact_id_b']] = $relationContact['contact_id_b'];
      }
    } catch (CiviCRM_API3_Exception $ex) {
      return $foundContacts;
    }
    return $foundContacts;
  }

  /**
   * Method to get all active SectorCoordinators
   *
   * @return array
   * @access public
   * @static
   */
  public static function getAllSectorCoordinators() {
    $foundContacts = array();
    $enhancedTags = CRM_Enhancedtags_BAO_TagEnhanced::getValues(array('is_active' => 1));
    foreach ($enhancedTags as $enhancedTag) {
      if (!empty($enhancedTag['coordinator_id'])) {
        $foundContacts[$enhancedTag['coordinator_id']] = $enhancedTag['coordinator_id'];
      }
    }
    return $foundContacts;
  }

  /**
   * Method to count all the main activities of the expert
   * (only if case type is one of configurable expert case types and if status is completed)
   *
   * @param int $expertId
   * @return int $countExpertCases
   * @access public
   * @static
   */
  public static function getExpertNumberOfCases($expertId) {
    $expertRelationshipType = CRM_Threepeas_Utils::getRelationshipTypeWithName('Expert');
    $expertRelationshipTypeId = $expertRelationshipType['id'];
    $expertRelationParams = array(
      'contact_id_b' => $expertId,
      'relationship_type_id' => $expertRelationshipTypeId,
      'return' => 'case_id',
      'options' => array('limit' => 99999));
    try {
      $expertRelations = civicrm_api3('Relationship', 'Get', $expertRelationParams);
      $expertCases = array();
      foreach ($expertRelations['values'] as $expertRelation) {
        $expertCases[] = $expertRelation['case_id'];
      }
      array_unique($expertCases);
      $countExpertCases = self::countExpertCases($expertCases);

    } catch (CiviCRM_API3_Exception $ex) {
      $countExpertCases = 0;
    }
    return $countExpertCases;
  }

  /**
   * Method to count the number of applicable cases for an expert
   *
   * @param array $expertCases
   * @return int $countExpertCases
   * @access protected
   * @static
   */
  protected static function countExpertCases($expertCases) {
    $countExpertCases = 0;
    if (!empty($expertCases)) {
      $extensionConfig = CRM_Threepeas_CaseRelationConfig::singleton();
      $validExpertCaseTypes = $extensionConfig->getExpertCaseTypes();
      $completedCaseStatus = $extensionConfig->getCaseStatusCompleted();
      foreach ($expertCases as $expertCaseId) {
        try {
          $caseData = civicrm_api3('Case', 'Getsingle', array('id' => $expertCaseId));
          if (in_array($caseData['case_type_id'], $validExpertCaseTypes)
            && $caseData['status_id'] == $completedCaseStatus) {
            $countExpertCases++;
          }
        } catch (CiviCRM_API3_Exception $ex) {}
      }
    }
    return $countExpertCases;
  }

  /**
   * Method checks if there are any restrictions for the expert
   *
   * @param int $expertId
   * @return bool $hasRestrictions
   * @access public
   * @static
   */
  public static function restrictionsForExpert($expertId) {
    $hasRestrictions = FALSE;
    $restrictionActivityType = CRM_Threepeas_Utils::getActivityTypeWithName('Restrictions');
    if (!empty($restrictionActivityType)) {
      $actQueryParams = array(
        1 => array(3, 'Integer'),
        2 => array(1, 'Integer'),
        3 => array($restrictionActivityType['value'], 'Integer'),
        4 => array($expertId, 'Integer'));
      $actQuery = 'SELECT COUNT(*) AS countRestrictions
        FROM civicrm_activity act
        JOIN civicrm_activity_contact cont ON act.id = cont.activity_id AND record_type_id = %1
        WHERE is_current_revision = %2 and activity_type_id = %3 and cont.contact_id = %4';
      $daoAct = CRM_Core_DAO::executeQuery($actQuery, $actQueryParams);
      if ($daoAct->fetch()) {
        if ($daoAct->countRestrictions > 0) {
          $hasRestrictions = TRUE;
        }
      }
    }
    return $hasRestrictions;
  }

  /**
   * Method to get the (first) relation contact id with label for case
   * @param int $caseId
   * @param string $relationLabel
   * @return int $relationContactId
   * @throw Exception when error in API
   * @access public
   * @static
   */
  public static function getRelationContactIdByCaseId($caseId, $relationLabel) {
    $relationContactId = NULL;
    if (empty($caseId) || empty($relationLabel)) {
      return $relationContactId;
    }
    $caseRelationConfig = CRM_Threepeas_CaseRelationConfig::singleton();
    $relationshipParams = array(
      'is_active' => 1,
      'relationship_type_id' => $caseRelationConfig->getRelationshipTypeId($relationLabel),
      'case_id' => $caseId);
    try {
      $relationshipData = civicrm_api3('Relationship', 'Get', $relationshipParams);
      if (!empty($relationshipData)) {
        foreach ($relationshipData['values'] as $relationShip) {
          $relationContactId = $relationShip['contact_id_b'];
          break;
        }
      }
    } catch (CiviCRM_API3_Exception $ex) {
      return $relationContactId;
    }
    return $relationContactId;
  }

  /**
   * Method to get the representative for a case (assumption is only one per case)
   *
   * @param int $caseId
   * @return int|boolean
   * @throws Exception when relation ship type expert not found
   * @access public
   * @static
   */
  public static function getCaseRepresentative($caseId) {
    if (empty($caseId)) {
      return FALSE;
    }
    $relationshipTypeParams = array(
      'name_a_b' => 'Representative is',
      'return' => 'id');
    try {
      $repRelationshipTypeId = civicrm_api3('RelationshipType', 'Getvalue', $relationshipTypeParams);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception(ts('Could not find relationship type Representative is, error from API RelationshipType Getvalue: '
        .$ex->getMessage()));
    }
    $relationshipParams = array(
      'relationship_type_id' => $repRelationshipTypeId,
      'case_id' => $caseId,
      'is_active' => 1,
      'return' => 'contact_id_b');
    try {
      $representativeId = civicrm_api3('Relationship', 'Getvalue', $relationshipParams);
      return $representativeId;
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to get sector coordinator contact id for expert
   *
   * @param $contactId
   * @return mixed
   * @access public
   * @static
   */
  public static function getSectorCoordinatorForExpert($contactId) {
    $sectors = civicrm_api3('ContactSegment', 'Get', array('contact_id' => $contactId, 'is_main' => 1));
    foreach ($sectors['values'] as $contactSegmentId => $contactSegment) {
      if (isset($contactSegment['segment_id']) && !empty($contactSegment['segment_id'])) {
        $params = array(
          'is_active' => 1,
          'role_value' => 'Sector Coordinator',
          'segment_id' => $contactSegment['segment_id'],
          'return' => 'contact_id'
        );
        try {
          return civicrm_api3('ContactSegment', 'Getvalue', $params);
        } catch (CiviCRM_API3_Exception $ex) {
        }
      }
    }
    return FALSE;
  }
}
