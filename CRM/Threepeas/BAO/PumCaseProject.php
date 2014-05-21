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
   * Function to delete PumCaseProject
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 19 May 2014
   * @param int $pumCaseProjectId 
   * @return boolean
   * @access public
   * @static
   */
  public static function deleteById($pumCaseProjectId) {
    if (empty($pumCaseProjectId)) {
      throw new Exception('pumCaseProjectId can not be empty when attempting to delete one');
    }
    $pumCaseProject = new CRM_Threepeas_BAO_PumCaseProject();
    $pumCaseProject->id = $pumCaseProjectId;
    self::deleteProjectOptionValue($pumCaseProjectId);
    $pumCaseProject->delete();
    return TRUE;
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
        self::deleteById($pumCaseProject->id);
      }
    }
  }
}
