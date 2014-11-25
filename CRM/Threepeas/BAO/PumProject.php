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
    if (!isset($pumProject->title) || empty($pumProject->title)) {
      $pumProject->title = self::generate_title($pumProject);
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
    $optionParams['return'] = 'value';
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
   * Function to get project title only with id
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 7 Oct 2014
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
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 13 Nov 2014
   * @param type $case_id
   * @access public
   * @static
   */
  public static function create_country_project_for_case($case_id) {
    if (self::is_cap_case($case_id) == TRUE && self::is_country_client($case_id) == TRUE) {
      $next_year = date('Y') + 1;
      $start_date = $next_year.'0101';
      $end_date = $next_year.'1231';
      $project_params = array(
        'is_active' => 1,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'country_id' => CRM_Threepeas_BAO_PumCaseProject::get_case_client_id($case_id));
      $created_project = self::add($project_params);
      $case_project_params = array(
        'case_id' => $case_id,
        'project_id' => $created_project['id'],
        'is_active' => 1
      );
      CRM_Threepeas_BAO_PumCaseProject::add($case_project_params);
    }
  }
  /**
   * Function to check if case is country action plan
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 13 Nov 2014
   * @param int $case_id
   * @return boolean
   * @access protected
   * @static
   */
  protected static function is_cap_case($case_id) {
    $threepeas_config = CRM_Threepeas_Config::singleton();
    $params = array(
      'id' => $case_id, 
      'return' => 'case_type_id');
    $cap_case_type = $threepeas_config->get_cap_case_type_id();
    $case_type_id = civicrm_api3('Case', 'Getvalue', $params);
    $type_parts = explode(CRM_Core_DAO::VALUE_SEPARATOR, $case_type_id);
    if (isset($type_parts[1])) {
      $case_type_id = $type_parts[1];
    }
    if ($case_type_id == $cap_case_type) {
      return TRUE;
    }
    return FALSE;
  }
  /**
   * Function to check if the client of a case is a country
   * 
   * @param int $case_id
   * @return boolean
   * @access protected
   * @static
   */
  protected static function is_country_client($case_id) {
    $case_client_id = CRM_Threepeas_BAO_PumCaseProject::get_case_client_id($case_id);
    if (!empty($case_client_id)) {
      $params = array(
        'id' => $case_client_id,
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
    return FALSE;
  }
  /**
   * Function to generate project title by default
   * 
   * @param obj $pum_project
   * @return string $title
   * @access protected
   * @static
   */
  protected static function generate_title($pum_project) {
    $title = '';
    if (isset($pum_project->country_id) && !empty($pum_project->country_id)) {
      $contact_id = $pum_project->country_id;
    } else {
      if (isset($pum_project->customer_id) && !empty($pum_project->customer_id)) {
        $contact_id = $pum_project->customer_id;
      }
    }
    if (isset($contact_id) && !empty($contact_id)) {
      
    }
    if (!empty($contact_id)) {
      $contact_name = civicrm_api3('Contact', 'Getvalue', array('id' => $contact_id, 'return' => 'display_name'));
      $title = 'Project '.$contact_name.'-'.$pum_project->id;
    } else {
      $title = 'Project <onbekend> -'.$pum_project->id;
    }
    return $title;
  } 
}
