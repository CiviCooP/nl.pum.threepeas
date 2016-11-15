<?php
/**
 * BAO PumCaseProject for dealing with case_projects (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 19 May 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the AGPL-3.0
 */
class CRM_Threepeas_BAO_PumCaseProject extends CRM_Threepeas_DAO_PumCaseProject {

  /**
   * Function to get values
   * 
   * @param array $params name/value pairs with field names/values
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $result = array();
    $pumCaseProject = new CRM_Threepeas_BAO_PumCaseProject();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $paramKey => $paramValue) {
        if (isset($fields[$paramKey])) {
          $pumCaseProject->$paramKey = $paramValue;
        }
      }
    }
    $pumCaseProject->find();
    while ($pumCaseProject->fetch()) {
      $row = array();
      self::storeValues($pumCaseProject, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }
  /**
   * Function to add or update pumCaseProject
   * 
   * @param array $params
   * @return array $result
   * @throws Exception when params empty
   * @access public
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a PumCaseProject');
    }
    if (isset($params['id'])) {
      $op = 'edit';
    } else {
      $op = 'create';
    }
    $pumCaseProject = new CRM_Threepeas_BAO_PumCaseProject();
    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $pumCaseProject->$paramKey = $paramValue;
      }
    }
    $pumCaseProject->save();
    // post hook
    CRM_Utils_Hook::post($op, 'PumCaseProject', $pumCaseProject->id, $pumCaseProject);
    self::storeValues($pumCaseProject, $result);
    return $result;
  }

  /**
   * Function to disable PumCaseProject by ProjectID
   * 
   * @param type $projectId
   */
  public static function disableByProjectId($projectId) {
    if (!empty($projectId)) {
      $pumCaseProject = new CRM_Threepeas_BAO_PumCaseProject();
      $pumCaseProject->project_id = $projectId;
      $pumCaseProject->find();
      while ($pumCaseProject->fetch()) {
        self::add(array('id' => $pumCaseProject->id, 'is_active' => 0));
      }
    }
  }
  /**
   * Function to delete PumCaseProject by ProjectID
   * 
   * @param type $projectId
   */
  public static function deleteByProjectId($projectId) {
    if (!empty($projectId)) {
      $pumCaseProject = new CRM_Threepeas_BAO_PumCaseProject();
      $pumCaseProject->project_id = $projectId;
      $pumCaseProject->find();
      while ($pumCaseProject->fetch()) {
        $pumCaseProject->delete();
      }
    }
  }
  /**
   * Function to enable PumCaseProject by ProjectID
   * 
   * @param type $projectId
   * @access public
   * @static
   */
  public static function enableByProjectId($projectId) {
    if (!empty($projectId)) {
      $pumCaseProject = new CRM_Threepeas_BAO_PumCaseProject();
      $pumCaseProject->project_id = $projectId;
      $pumCaseProject->find();
      while ($pumCaseProject->fetch()) {
        self::add(array('id' => $pumCaseProject->id, 'is_active' => 1));
      }
    }
  }
  /**
   * Function to disable PumCaseProject by CaseID
   * 
   * @param type $caseId
   * @access public
   * @static
   */
  public static function disableByCaseId($caseId) {
    if (!empty($caseId)) {
      $pumCaseProject = new CRM_Threepeas_BAO_PumCaseProject();
      $pumCaseProject->case_id = $caseId;
      $pumCaseProject->find();
      while ($pumCaseProject->fetch()) {
        self::add(array('id' => $pumCaseProject->id, 'is_active' => 0));
      }
    }
  }
  public static function countCasesForProject($projectId) {
    $numberCases = 0;
    if (!empty($projectId)) {
      $pumCaseProject = new CRM_Threepeas_BAO_PumCaseProject();
      $pumCaseProject->project_id = $projectId;
      $numberCases = $pumCaseProject->count();
    }
    return $numberCases;
  }

  /**
   * Method to get projectId with CaseId
   *
   * @param $caseId
   * @return bool
   * @access public
   * @static
   */
  public static function getProjectIdWithCaseId($caseId) {
    $pumCaseProject = new CRM_Threepeas_BAO_PumCaseProject();
    $pumCaseProject->case_id = $caseId;
    $pumCaseProject->find();
    if ($pumCaseProject->fetch()) {
      return $pumCaseProject->project_id;
    } else {
      return FALSE;
    }
  }
}
