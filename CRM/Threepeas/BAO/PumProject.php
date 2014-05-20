<?php
/**
 * BAO PumProject for dealing with projects (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 16 Apr 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
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
    self::addProjectOptionValue($pumProject->id, $pumProject->title);
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
    $pumProject = new CRM_Threepeas_BAO_PumProject();
    $pumProject->id = $pumProjectId;
    /*
     * delete records from case_project with project_id
     */
    CRM_Threepeas_BAO_PumCaseProject::deleteByProjectId($pumProjectId);
    self::deleteProjectOptionValue($pumProjectId);
    $pumProject->delete();
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
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    if (!is_null($threepeasConfig->caseCustomTable) && !is_null($threepeasConfig->caseProjectColumn)) {
      $countQuery = 'SELECT COUNT(*) AS countCases FROM '.$threepeasConfig->caseCustomTable. ' WHERE '
        .$threepeasConfig->caseProjectColumn.' = '.$projectId;
      $dao = CRM_Core_DAO::executeQuery($countQuery);
      if ($dao->fetch()) {
        if ($dao->countCases > 0) {
          $canBeDeleted = FALSE;
        } 
      }
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
  public static function countCustomerProjects($customerId) {
    $countProjects = 0;
    if (!empty($customerId) && is_numeric($customerId)) {
      $countQry = "SELECT COUNT(*) AS countProjects FROM civicrm_project WHERE customer_id = %1";
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
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    /*
     * select all entity_ids (case_ids) with $projectId
     */
    $caseQuery = 'SELECT entity_id FROM '.$threepeasConfig->caseCustomTable.
      ' WHERE '.$threepeasConfig->caseProjectColumn.' = %1';
    $caseParams = array(1=>array($projectId, 'Integer'));
    $caseDao = CRM_Core_DAO::executeQuery($caseQuery, $caseParams);
    while ($caseDao->fetch()) {
      $result[$caseDao->entity_id] = self::getCaseResultLine($caseDao->entity_id);
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
    $resultLine['expert_id'] = CRM_Case_BAO_Case::getCaseRoles($caseClientId, $caseId, $threepeasConfig->expertRelationshipTypeId);
    $optionParams['option_group_id'] = $threepeasConfig->caseTypeOptionGroupId;
    $optionParams['value'] = $case['case_type_id'];
    $optionParams['return'] = 'id';
    $resultLine['case_type'] = civicrm_api3('OptionValue', 'Getvalue', $optionParams);
    $optionParams['option_group_id'] = $threepeasConfig->caseStatusOptionGroupId;
    $optionParams['value'] = $case['status_id'];
    $resultLine['case_status'] = civicrm_api3('OptionValue', 'Getvalue', $optionParams);
    return $resultLine;
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
}