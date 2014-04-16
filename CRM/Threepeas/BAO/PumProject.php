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
    $pumProject->delete();
    return TRUE;
  }
}
