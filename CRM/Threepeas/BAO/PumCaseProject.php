<?php
/**
 * BAO PumCaseProject for dealing with case_projects (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 19 May 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
 */
class CRM_Threepeas_BAO_PumCaseProject extends CRM_Threepeas_DAO_PumCaseProject {

  /**
   * Function to get values
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 19 May 2014
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
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 19 May 2014
   * @param array $params 
   * @return array $result
   * @access public
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a PumCaseProject');
    }
    $pumCaseProject = new CRM_Threepeas_BAO_PumCaseProject();
    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $pumCaseProject->$paramKey = $paramValue;
      }
    }
    $pumCaseProject->save();
    self::storeValues($pumCaseProject, $result);
    return $result;
  }
  /**
   * Function to disable PumCaseProject by ProjectID
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 20 May 2014
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
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 20 May 2014
   * @param type $projectId
   */
  public static function deleteByProjectId($projectId) {
    if (!empty($projectId)) {
      $pumCaseProject = new CRM_Threepeas_BAO_PumCaseProject();
      $pumCaseProject->project_id = $projectId;
      $pumCaseProject->find();
      while ($pumCaseProject->fetch()) {
        $pumCaseProject->delete;
      }
    }
  }
  /**
   * Function to enable PumCaseProject by ProjectID
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 23 June 2014
   * @param type $projectId
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
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 3 Jun 2014
   * @param type $caseId
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
  /**
   * Function to return the client_id of a case
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 17 Nov 2014
   * @param int $case_id
   * @return int $client_id
   * @access public
   * @static
   */
  public static function get_case_client_id($case_id) {
    $params = array(
      'id' => $case_id,
      'return' => 'client_id'
    );
    try {
      $case_clients = civicrm_api3('Case', 'Getvalue', $params);
      foreach ($case_clients as $case_client) {
        $client_id = $case_client;
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $client_id = 0;
    }
    return $client_id;
  }
}
