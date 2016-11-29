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
    $pumProject->orderBy('id');
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
   * @throws Exception when params empty
   * @access public
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a PumProject');
    }
    $pumProject = new CRM_Threepeas_BAO_PumProject();
    if (isset($params['id'])) {
      $pumProject->id = $params['id'];
      $pumProject->find(true);
      // pre hook if edit
      $op = "edit";
      self::storeValues($pumProject, $prePumProject);
      CRM_Utils_Hook::pre($op, 'PumProject', $pumProject->id, $prePumProject);
    } else {
      $op = 'create';
    }
    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $pumProject->$paramKey = $paramValue;
      }
    }
    if (isset($pumProject->customer_id)) {
      $pumProject->anamon_id = CRM_Threepeas_BAO_PumCaseRelation::getAnamonId($pumProject->customer_id);
      $pumProject->country_coordinator_id = CRM_Threepeas_BAO_PumCaseRelation::getCountryCoordinatorId($pumProject->customer_id);
      $pumProject->project_officer_id = CRM_Threepeas_BAO_PumCaseRelation::getProjectOfficerId($pumProject->customer_id);
      $pumProject->sector_coordinator_id = CRM_Threepeas_BAO_PumCaseRelation::getSectorCoordinatorId($pumProject->customer_id);
    }
    $pumProject->save();
    // post hook
    CRM_Utils_Hook::post($op, 'PumProject', $pumProject->id, $pumProject);
    if (!isset($pumProject->title) || empty($pumProject->title)) {
      $pumProject->title = self::generateTitle($pumProject);
      $pumProject->save();
    }
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
   * @throws Exception when projectId empty
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
     * can not delete if any cases for project or any contributions for project
     */
    $projectCases = CRM_Threepeas_BAO_PumCaseProject::getValues(array('project_id' => $projectId));
    $projectDonations = CRM_Threepeas_BAO_PumDonorLink::getValues(array(
      'entity' => 'Project',
      'entity_id' => $projectId));

    if (!empty($projectCases) || !empty($projectDonations)) {
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
    try {
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
      $optionParams['return'] = 'value';
      try {
        $resultLine['case_type'] = civicrm_api3('OptionValue', 'Getvalue', $optionParams);
      } catch (CiviCRM_API3_Exception $e) {
        $resultLine['case_type'] = 'onbekend';
      }
      $optionParams['option_group_id'] = $threepeasConfig->caseStatusOptionGroupId;
      $optionParams['value'] = $case['status_id'];
      $resultLine['case_status'] = civicrm_api3('OptionValue', 'Getvalue', $optionParams);
    } catch (CiviCRM_API3_Exception $ex) {
    }
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
   * Function to get project title only with id
   * 
   * @param int $pumProjectId
   * @return string $pumProject->title
   * @access public
   * @static
   */
  public static function getProjectTitleWithId($pumProjectId) {
    if (empty($pumProjectId)) {
      return '';
    }
    $pumProject = new CRM_Threepeas_BAO_PumProject();
    $pumProject->id = $pumProjectId;
    if ($pumProject->find(true)) {
      return $pumProject->title;
    } else {
      return '';
    }
  }
  /**
   * Function to create a country project for a case if not exists
   * 
   * @param int $caseId
   * @access public
   * @static
   */
  public static function createCountryProjectForCase($caseId) {
    if (self::isCapCase($caseId) == TRUE && self::isCountryClient($caseId) == TRUE) {
      $nextYear = date('Y') + 1;
      $startDate = $nextYear.'0101';
      $endDate = $nextYear.'1231';
      $projectParams = array(
        'is_active' => 1,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'country_id' => CRM_Threepeas_Utils::getCaseClientId($caseId));
      $createdProject = self::add($projectParams);
      $caseProjectParams = array(
        'case_id' => $caseId,
        'project_id' => $createdProject['id'],
        'is_active' => 1
      );
      CRM_Threepeas_BAO_PumCaseProject::add($caseProjectParams);
    }
  }
  /**
   * Function to check if case is country action plan
   * 
   * @param int $caseId
   * @return boolean
   * @access protected
   * @static
   */
  protected static function isCapCase($caseId) {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $params = array(
      'id' => $caseId,
      'return' => 'case_type_id');
    $capCaseType = $threepeasConfig->getCapCaseTypeId();
    $caseTypeId = civicrm_api3('Case', 'Getvalue', $params);
    $typeParts = explode(CRM_Core_DAO::VALUE_SEPARATOR, $caseTypeId);
    if (isset($typeParts[1])) {
      $caseTypeId = $typeParts[1];
    }
    if ($caseTypeId == $capCaseType) {
      return TRUE;
    }
    return FALSE;
  }
  /**
   * Function to check if the client of a case is a country
   * 
   * @param int $caseId
   * @return boolean
   * @access protected
   * @static
   */
  protected static function isCountryClient($caseId) {
    $caseClientId = CRM_Threepeas_Utils::getCaseClientId($caseId);
    if (!empty($caseClientId)) {
      return CRM_Threepeas_Utils::contactIsCountry($caseClientId);
    } else {
      return FALSE;
    }
  }
  /**
   * Function to generate project title by default
   * 
   * @param obj $pumProject
   * @return string $title
   * @access protected
   * @static
   */
  protected static function generateTitle($pumProject) {
    if (isset($pumProject->country_id) && !empty($pumProject->country_id)) {
      $contactId = $pumProject->country_id;
    } else {
      if (isset($pumProject->customer_id) && !empty($pumProject->customer_id)) {
        $contactId = $pumProject->customer_id;
      }
    }
    if (isset($contactId) && !empty($contactId)) {
      
    }
    if (!empty($contactId)) {
      $contactName = civicrm_api3('Contact', 'Getvalue', array('id' => $contactId, 'return' => 'display_name'));
      $title = 'Project '.$contactName.'-'.$pumProject->id;
    } else {
      $title = 'Project <onbekend> -'.$pumProject->id;
    }
    return $title;
  }

  /**
   * Method to get a string with the roles of the user on a given project
   * possible are:
   * - projectmanager
   * - name of a relation from PumCaseRelation (country coord, sector coord, anamon etc.)
   * - expert
   *
   * @param array $params
   * @return string
   * @access public
   * @static
   */
  public static function getUserRoles($params) {
    $myRoles = array();
    $caseRelationConfig = CRM_Threepeas_CaseRelationConfig::singleton();
    $relationTypes = $caseRelationConfig->getRelationshipTypes();
    if (isset($params['project_id']) && isset($params['user_id'])) {
      if (self::isProjectManager($params['project_id'], $params['user_id']) == TRUE) {
        $myRoles[] = ts('Projectmanager');
      }
      foreach ($relationTypes as $roleLabel => $roleText) {
        $roleId = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($params['customer_id'], $roleLabel);
        if ($roleId == $params['user_id']) {
          $roleString = self::buildMyRoleTextFromLabel($roleLabel);
          if (!in_array($roleString, $myRoles)) {
            $myRoles[] = $roleString;
          }
        }
      }
      if (self::isExpertOnProject($params['project_id'], $params['user_id']) == TRUE) {
        if (!in_array('Expert', $myRoles)) {
          $myRoles[] = 'Expert';
        }
      }
      if (self::isActiveOnCasesInProject($params['project_id'], $params['user_id']) == TRUE) {
        $myRoles[] = 'Main activity role';
      }
    }
    return implode('; ', $myRoles);
  }

  /**
   * Method to check if the contact has an active role on a case in the project
   *
   * @param int $projectId
   * @param int $contactId
   * @return bool
   * @access public
   * @static
   */
  public static function isActiveOnCasesInProject($projectId, $contactId) {
    $query = 'SELECT COUNT(*) AS activeCount
      FROM civicrm_case_project cproj
      JOIN civicrm_relationship rel ON cproj.case_id = rel.case_id
      WHERE rel.case_id IS NOT NULL AND rel.is_active=%1 AND contact_id_b = %2 AND project_id = %3';
    $queryParams = array(
      1 => array(1, 'Integer'),
      2 => array($contactId, 'Integer'),
      3 => array($projectId, 'Integer'));
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    if ($dao->fetch()) {
      if ($dao->activeCount > 0) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Method to check if user is projectmanager of project
   *
   * @param int $projectId
   * @param int $userId
   * @return bool
   * @access public
   * @static
   */
  public static function isProjectManager($projectId, $userId) {
    $project = new CRM_Threepeas_BAO_PumProject();
    $project->id = $projectId;
    $project->find(true);
    if ($project->projectmanager_id == $userId) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Method to determine if the user is expert on a case linked to the project
   *
   * @param int $projectId
   * @param int $userId
   * @return bool
   * @access public
   * @static
   */
  public static function isExpertOnProject($projectId, $userId) {
    $projectCases = CRM_Threepeas_BAO_PumCaseProject::getValues(array('project_id' => $projectId));
    foreach ($projectCases as $projectCase) {
      $expertId = CRM_Threepeas_BAO_PumCaseRelation::getCaseExpert($projectCase['case_id']);
      if ($expertId == $userId) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Method to build a string for a role label
   * (so sector_coordinator becomes Sector Coordinator)
   *
   * @param string $roleLabel
   * @return string
   * @access public
   * @static
   */
  public static function buildMyRoleTextFromLabel($roleLabel) {
    $labelStrings = array();
    $labelParts = explode('_', $roleLabel);
    if (is_array($labelParts)) {
      foreach ($labelParts as $labelPart) {
        $labelStrings[] = ucfirst($labelPart);
      }
    }
    return implode(' ', $labelStrings);
  }

  /**
   * Method to get all active projects for contact (customer or country)
   *
   * @param int $contactId
   * @return array
   * @access public
   * @static
   */
  public static function getContactProjects($contactId) {
    $result = array();
    $projectParams = array('is_active' => 1);
    if (CRM_Threepeas_Utils::contactIsCountry($contactId) == TRUE) {
      $projectParams['country_id'] = $contactId;
    } else {
      $projectParams['customer_id'] = $contactId;
    }
    $contactProjects = self::getValues($projectParams);
    foreach ($contactProjects as $contactProject) {
      $result[$contactProject['id']] = $contactProject;
    }
    return $result;
  }

  public static function createProjectFromWebform($caseId) {
    // Check whether a case is already linked to a project.
    $projectId = CRM_Threepeas_BAO_PumCaseProject::getProjectIdWithCaseId($caseId);
    if ($projectId) {
      return;
    }

    $config = CRM_Threepeas_Config::singleton();
    /*
     * retrieve project data from custom group Project Information
     */
    $customQuery = "SELECT * FROM ".$config->getProjectCustomGroupTableName()." WHERE entity_id = %1";
    $customParams = array(1 => array($caseId, 'Integer'));
    $customData = CRM_Core_DAO::executeQuery($customQuery, $customParams);
    $projectParams = self::setProjectParamsFromWebform($caseId, $customData);
    $createdProject = self::add($projectParams);
    $projectCaseParams = array(
      'case_id' => $caseId,
      'project_id' => $createdProject['id'],
      'is_active' => 1);
    CRM_Threepeas_BAO_PumCaseProject::add($projectCaseParams);
  }

  /**
   * Method to set params for project coming from custom data dao
   *
   * @param int $caseId
   * @param object $dao
   * @return array
   * @access private
   * @static
   */
  private static function setProjectParamsFromWebform($caseId, $dao) {
    $fields = array("reason", "activities", "expected_results");
    $params = array(
      'is_active' => 1,
      'customer_id' => CRM_Threepeas_Utils::getCaseClientId($caseId)
    );
    foreach ($fields as $field) {
      $daoPropertyName = self::getColumnNameForCustomField($field);
      if (isset($dao->$daoPropertyName)) {
        $params[$field] = $dao->$daoPropertyName;
      }
    }
    return $params;
  }

  /**
   * Method to get column name for project custom field name
   *
   * @param $fieldName
   * @return string|bool
   * @access private
   * @static
   */
  private static function getColumnNameForCustomField($fieldName) {
    $config = CRM_Threepeas_Config::singleton();
    $projectCustomFields = $config->getProjectCustomFields();
    foreach ($projectCustomFields as $customFieldId => $customField) {
      if ($fieldName == $customField['name']) {
        return $customField['column_name'];
      }
    }
    return FALSE;
  }

  /**
   * Method to update project with custom data from webform
   *
   * @param $caseId
   * @param $customDataParams
   * @throws Exception
   */
  public static function updateProjectWithCustomData($caseId, $customDataParams) {
    if (!empty($caseId)) {
      $projectParams = array();
      $projectParams['id'] = CRM_Threepeas_BAO_PumCaseProject::getProjectIdWithCaseId($caseId);
      $fields = array("reason", "activities", "expected_results");
      foreach ($fields as $field) {
        $columnName = self::getColumnNameForCustomField($field);
        foreach ($customDataParams as $customData) {
          if ($customData['column_name'] == $columnName) {
            if ($field == "activities") {
              $projectParams['work_description'] = $customData['value'];
            } else {
              $projectParams[$field] = $customData['value'];
            }
          }
        }
      }
      self::add($projectParams);
    }
  }

  /**
   * Method to return a single project with id
   *
   * @param int $projectId
   * @return array|string|void
   * @access public
   * @static
   */
  public static function getSingleProjectById($projectId) {
    if (empty($pumProjectId)) {
      return '';
    }
    $pumProject = new CRM_Threepeas_BAO_PumProject();
    $pumProject->id = $pumProjectId;
    if ($pumProject->find(true)) {
      $row = self::storeValues($pumProject, $row);
      return $row;
    } else {
      return array();
    }
  }
}
