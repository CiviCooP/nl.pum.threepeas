<?php
/**
 * BAO PumProject for dealing with projects (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 16 Apr 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under AGPL-3.0
 */
class CRM_Threepeas_BAO_PumProject extends CRM_Threepeas_DAO_PumProject {

  /**
   * Function to get values
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 16 Apr 2014
   * @param array $params name/value pairs with field names/values
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $result = array();
    $pumProject = new CRM_Threepeas_BAO_PumProject();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $paramKey => $paramValue) {
        if (isset($fields[$paramKey])) {
          $pumProject->$paramKey = $paramValue;
        }
      }
    }
    $pumProject->find();
    while ($pumProject->fetch()) {
      $row = array();
      self::storeValues($pumProject, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }
  /**
   * Function to add or update pumProject
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 16 Apr 2014
   * @param array $params 
   * @return array $result
   * @access public
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a PumProject');
    }
    $pumProject = new CRM_Threepeas_BAO_PumProject();
    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $pumProject->$paramKey = $paramValue;
      }
    }
    $pumProject->save();
    if (isset($pumProject->title) && !empty($pumProject->title)) {
      self::addProjectOptionValue($pumProject->id, $pumProject->title);
    }
    self::storeValues($pumProject, $result);
    return $result;
  }

  /**
   * Function to delete PumProject
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 16 Apr 2014
   * @param int $pumProjectId 
   * @return boolean
   * @access public
   * @static
   */
  public static function deleteById($pumProjectId) {
    if (empty($pumProjectId)) {
      throw new Exception('pumProjectId can not be empty when attempting to delete one');
    }
    
    CRM_Utils_Hook::pre('delete', 'PumProject', $pumProjectId, CRM_Core_DAO::$_nullArray);
    
    $pumProject = new CRM_Threepeas_BAO_PumProject();
    $pumProject->id = $pumProjectId;
    /*
     * delete records from case_project with project_id
     */
    CRM_Threepeas_BAO_PumCaseProject::deleteByProjectId($pumProject->id);
    self::deleteProjectOptionValue($pumProject->id);
    /*
     * delete linked donation links when programme is deleted
     */
    CRM_Threepeas_BAO_PumDonorLink::deleteByEntityId('Project', $pumProject->id);
    $pumProject->delete();
    
    CRM_Utils_Hook::post('delete', 'PumProject', $pumProject->id, $pumProject);
    return TRUE;
  }
  /**
   * Function to check if there is already a project with the incoming title
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 30 Apr 2014
   * @param string $projectTitle
   * @return boolean
   * @access public
   * @static
   */
  public static function checkTitleExists($projectTitle) {
    $projects = self::getValues(array('title' => $projectTitle));
    if (empty($projects)) {
      return FALSE;
    } else {
      return TRUE;
    }
  }
  /**
   * Function to check if project can be deleted
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 30 Apr 2014
   * @param int $projectId
   * @return boolean $canBeDeleted
   * @access public
   * @static
   */
  public static function checkCanBeDeleted($projectId) {
    if (empty($projectId)) {
      return TRUE;
    }
    $canBeDeleted = TRUE;
    /*
     * can not delete if any cases for project
     */
    $projectCases = CRM_Threepeas_BAO_PumCaseProject::getValues(array('project_id' => $projectId));
    if (!empty($projectCases)) {
      $canBeDeleted = FALSE;
    }
    return $canBeDeleted;
  }
  /**
   * Function to count projects for a customer
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 7 May 2014
   * @param int $customerId
   * @return int $countProjects
   * @access public
   * @static
   */
  public static function countCustomerProjects($customerId, $type) {
    $countProjects = 0;
    if (!empty($customerId) && is_numeric($customerId)) {
      $threepeasConfig = CRM_Threepeas_Config::singleton();
      if ($type == $threepeasConfig->customerContactType) {
        $countQry = "SELECT COUNT(*) AS countProjects FROM civicrm_project WHERE customer_id = %1";
      }
      if ($type == $threepeasConfig->countryContactType) {
        $countQry = "SELECT COUNT(*) AS countProjects FROM civicrm_project WHERE country_id = %1";        
      }
      $countParams = array(1 => array($customerId, 'Integer'));
      $dao = CRM_Core_DAO::executeQuery($countQry, $countParams);
      if ($dao->fetch()) {
        $countProjects = $dao->countProjects;
      }
    }
    return $countProjects;
  }
  /**
   * Function to retrieve all cases for a project
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 7 May 2014
   * @param int $projectId
   * @return array $result
   * @access public
   * @static
   */
  public static function getCasesByProjectId($projectId) {
    $result = array();
    if (empty($projectId) || !is_numeric($projectId)) {
      return $result;
    }
    /*
     * select all entity_ids (case_ids) with $projectId
     */
    $projectCasesParams = array(
      'is_active' => 1,
      'project_id' => $projectId);
    $projectCases = CRM_Threepeas_BAO_PumCaseProject::getValues($projectCasesParams);
    foreach ($projectCases as $projectCase) {
      $result[$projectCase['case_id']] = self::getCaseResultLine($projectCase['case_id']);
    }
    return $result;
  }
  /**
   * Function to get a case line for a case id
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 7 May 2014
   * @param int $caseId
   * @return array $resultLine
   * @access private
   * @static
   */
  private static function getCaseResultLine($caseId) {
    $resultLine = array();
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $case = civicrm_api3('Case', 'Getsingle', array('id' => $caseId, 'is_deleted' => 0));
    foreach ($case['client_id'] as $caseClient) {
      $caseClientId = $caseClient;
    }
    $resultLine['case_id'] = $case['id'];
    $resultLine['subject'] = $case['subject'];
    $resultLine['start_date'] = $case['start_date'];
    $resultLine['end_date'] = $case['end_date'];
    $resultLine['client_id'] = $caseClientId;
    $resultLine['expert_id'] = self::getCaseRoleContactId($caseId, $threepeasConfig->expertRelationshipTypeId);
    $optionParams['option_group_id'] = $threepeasConfig->caseTypeOptionGroupId;
    $optionParams['value'] = $case['case_type_id'];
    $optionParams['return'] = 'id';
    try {
      $resultLine['case_type'] = civicrm_api3('OptionValue', 'Getvalue', $optionParams);
    } catch (CiviCRM_API3_Exception $e) {
      $resultLine['case_type'] = 'onbekend';
    }
    $optionParams['option_group_id'] = $threepeasConfig->caseStatusOptionGroupId;
    $optionParams['value'] = $case['status_id'];
    $resultLine['case_status'] = civicrm_api3('OptionValue', 'Getvalue', $optionParams);
    return $resultLine;
  }
  /**
   * Function to retrieve the Expert for a case
   */
  private static function getCaseRoleContactId($caseId, $relationshipTypeId) {
    $roleContactId = 0;
    if (empty($caseId) || empty($relationshipTypeId)) {
      return $roleContactId;
    }
    $query = 'SELECT contact_id_b FROM civicrm_relationship WHERE case_id = %1 '
      .'AND relationship_type_id = %2';
    $params = array(1 => array($caseId, 'Integer'), 2 => array($relationshipTypeId, 'Integer'));
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    if ($dao->fetch()) {
      $roleContactId = $dao->contact_id_b;
    }
    return $roleContactId;
  }
  /**
   * Function to add a created project to the Option Group for projects
   * if it does not exist already
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 19 May 2014
   * @param int $projectId
   * @param string $projectTitle
   * @access private
   * @static
   */
  private static function addProjectOptionValue($projectId, $projectTitle) {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    if (!empty($projectId)) {
      $params = array('option_group_id' => $threepeasConfig->projectOptionGroupId, 'value' => $projectId);
      $checkValue = civicrm_api3('OptionValue', 'Getcount', $params);
      if ($checkValue == 0) {
        $createParams = array(
          'option_group_id'   =>  $threepeasConfig->projectOptionGroupId,
          'value'             =>  $projectId,
          'label'             =>  $projectTitle,
          'is_active'         =>  1,
          'is_reserved'       =>  1
        );
        civicrm_api3('OptionValue', 'Create', $createParams);
      }
    }
  }  
  /**
   * Function to delete a created project to the Option Group for projects
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 19 May 2014
   * @param int $projectId
   * @access private
   * @static
   */
  private static function deleteProjectOptionValue($projectId) {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    if (!empty($projectId)) {
      $params = array(
        'option_group_id' => $threepeasConfig->projectOptionGroupId, 
        'value' => $projectId, 
        'return' => 'id');
      $optionValueId = civicrm_api3('OptionValue', 'Getvalue', $params);
      civicrm_api3('OptionValue', 'Delete', array('id' => $optionValueId));
    }
  }
  /**
   * Function to delete all projects and case projects for contact (customer/country)
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 23 Jun 2014
   * @param int $contactId
   * @param string $type
   * @access public
   * @static
   */
  public static function deleteByContactId($contactId, $type) {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    if (!empty($contactId)) {
      $pumProject = new CRM_Threepeas_BAO_PumProject();
      switch ($type) {
        case $threepeasConfig->customerContactType:
          $pumProject->customer_id = $contactId;
          break;
        case $threepeasConfig->countryContactType:
          $pumProject->country_id = $contactId;
      }
      $pumProject->find();
      while ($pumProject->fetch()) {
        self::deleteById($pumProject->id);
      }
    }
  }
  /**
   * Function to return the type of project (Country or Customer)
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 9 Jul 2014
   * @param int $pumProjectId
   * @return string $projectType
   * @access public
   * @static
   */
  public static function getProjectType($pumProjectId) {
    $projectType = 'Customer';
    if (!empty($pumProjectId)) {
      $pumProject = new CRM_Threepeas_BAO_PumProject();
      $pumProject->id = $pumProjectId;
      $pumProject->find(TRUE);
      if (!empty($pumProject->country_id)) {
        $projectType = 'Country';
      }      
    }
    return $projectType;
  }
  /**
   * Function to retrieve the project officer for a customer
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 1 Sep 2014
   * @param int $customerId
   * @return int $projectOfficerId
   * @access public
   * @static
   */
  public static function getProjectOfficer($customerId, $type) {
    if ($type == 'customer') {
      $contactId = self::getCustomerCountryId($customerId);
    } else {
      $contactId = $customerId;
    }
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $projectOfficerId = self::getRelationshipContactId($contactId, $threepeasConfig->projectOfficerRelationshipTypeId);
    return $projectOfficerId;
  }
  /**
   * Function to retrieve the country officer for a customer
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 1 Sep 2014
   * @param int $customerId
   * @return int $countryCoordinatorId
   * @access public
   * @static
   */
  public static function getCountryCoordinator($customerId, $type) {
    if ($type == 'customer') {
      $contactId = self::getCustomerCountryId($customerId);
    } else {
      $contactId = $customerId;
    }
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $countryCoordinatorId = self::getRelationshipContactId($contactId, $threepeasConfig->countryCoordinatorRelationshipTypeId);
    
    return $countryCoordinatorId;
  }
  /**
   * Function to get the opposite contact_id of an active relation
   * so if contact_id_a is found, contact_id_b is returned and the other way around
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 3 Sep 2014
   * @param int $sourceContactId
   * @param int $relationshipTypeId
   * @return int $contactId
   */
  private static function getRelationshipContactId($sourceContactId, $relationshipTypeId) {
    $contactId = 0;
    $params = array(
      'is_active' => 1,
      'relationship_type_id' => $relationshipTypeId,
      'contact_id_a' => $sourceContactId
    );
    try {
      $foundRelations = civicrm_api3('Relationship', 'Get', $params);
    } catch (CiviCRM_API3_Exception $ex) {
    }
    if ($foundRelations['count'] > 0) {
      foreach ($foundRelations['values'] as $foundRelation) {
        $contactId = $foundRelation['contact_id_b'];
      }
    } else {
      $params = array(
        'is_active' => 1,
        'relationship_type_id' => $relationshipTypeId,
        'contact_id_b' => $sourceContactId
      );
      try {
        $foundRelations = civicrm_api3('Relationship', 'Get', $params);
        foreach ($foundRelations['values'] as $foundRelation) {
          $contactId = $foundRelation['contact_id_a'];
        }
      } catch (CiviCRM_API3_Exception $ex) {
      }
    }
    return $contactId;
  }
  /**
   * Function to retrieve the representative for a project
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 1 Sep 2014
   * @param int $customerId
   * @return int $representativeId
   * @access public
   * @static
   */
  public static function getRepresentative($customerId) {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $representativeId = self::getRelationshipContactId($customerId, $threepeasConfig->representativeRelationshipTypeId);
    /*
     * if no customer representative found, retrieve from country
     */
    if (empty($representativeId)) {
      $countryId = self::getCustomerCountryId($customerId);
      $representativeId = self::getRelationshipContactId($countryId, $threepeasConfig->representativeRelationshipTypeId);
    }
    return $representativeId;
  }
  /**
   * Function to retrieve the sector coordinator for a project
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 1 Sep 2014
   * @param int $customerId
   * @return int $sectorCoordinatorId
   * @access public
   * @static
   */
  public static function getSectorCoordinator($customerId) {
    $sectorCoordinatorId = 0;
    $entityTagParams = array('entity_table' => 'civicrm_contact', 'entity_id' => $customerId);
    $apiEntityTag = civicrm_api3('EntityTag', 'Get', $entityTagParams);
    foreach ($apiEntityTag['values'] as $customerTag) {
      $enhancedParams = array('is_active' => 1, 'tag_id' => $customerTag['tag_id'], 'return' => 'coordinator_id');
      try {
        $sectorCoordinatorId = civicrm_api3('TagEnhanced', 'Getvalue', $enhancedParams);
      } catch (CiviCRM_API3_Exception $ex) {
      }
    }
    return $sectorCoordinatorId;
  }
  /**
   * Function to get the country required for a customer
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 1 Sep 2014
   * @param int $customerId
   * @return int $countryId
   * @access private
   * @static
   */
  private static function getCustomerCountryId($customerId) {
    $countryId = 0;
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    if (!empty($customerId)) {
      $contactData = civicrm_api3('Contact', 'Getsingle', array('contact_id' => $customerId));
      if (isset($contactData['country_id']) && !empty($contactData['country_id'])) {
        $params = array(
          'custom_'.$threepeasConfig->countryCustomFieldId => $contactData['country_id'], 
          'return' => 'id');
        $countryId = civicrm_api3('Contact', 'Getvalue', $params);
      }
    }
    return $countryId;
  }
  /**
   * Function to set the default case roles for the PUM cases
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 3 Sep 2014
   * @param int $caseId
   * @param int $customerId
   */
  public static function setDefaultCaseRoles($caseId, $customerId, $caseStartDate) {
    if (!empty($caseId) && !empty($customerId)) {
      $threepeas = CRM_Threepeas_Config::singleton();
      $countryCoordinatorId = self::getCountryCoordinator($customerId, 'customer');
      self::setCaseRelation($customerId, $countryCoordinatorId, $caseId, 
        $threepeas->countryCoordinatorRelationshipTypeId, $caseStartDate);
      
      $projectOfficerId = self::getProjectOfficer($customerId, 'customer');
      self::setCaseRelation($customerId, $projectOfficerId, $caseId, 
        $threepeas->projectOfficerRelationshipTypeId, $caseStartDate);
      
      $representativeId = self::getRepresentative($customerId);
      self::setCaseRelation($customerId, $representativeId, $caseId, 
        $threepeas->representativeRelationshipTypeId, $caseStartDate);
      
      $sectorCoordinatorId = self::getSectorCoordinator($customerId);
      self::setCaseRelation($customerId, $sectorCoordinatorId, $caseId, 
        $threepeas->sectorCoordinatorRelationshipTypeId, $caseStartDate);
    }
  }
  /**
   * Function to create a relation
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 4 Sep 2014
   * @param int $contactAId
   * @param int $contactBId
   * @param int $caseId
   * @param int $relationshipTypeId
   * @param string $startDate
   * @access private
   * @static
   */
  private static function setCaseRelation($contactAId, $contactBId, $caseId, 
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
}
