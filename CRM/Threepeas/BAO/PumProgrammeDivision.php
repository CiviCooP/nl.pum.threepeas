<?php
/**
 * BAO PumProgrammeDivision for dealing with programme budget divisions (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 18 Apr 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
 */
class CRM_Threepeas_BAO_PumProgrammeDivision extends CRM_Threepeas_DAO_PumProgrammeDivision {

  /**
   * Function to get values
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 18 Apr 2014
   * @param array $params name/value pairs with field names/values
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $result = array();
    $pumProgrammeDivision = new CRM_Threepeas_BAO_PumProgrammeDivision();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $paramKey => $paramValue) {
        if (isset($fields[$paramKey])) {
          $pumProgrammeDivision->$paramKey = $paramValue;
        }
      }
    }
    $pumProgrammeDivision->find();
    while ($pumProgrammeDivision->fetch()) {
      $row = array();
      self::storeValues($pumProgrammeDivision, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }
  /**
   * Function to add or update programme division
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 18 Apr 2014
   * @param array $params 
   * @return array $result
   * @access public
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a PumProgrammeDivision');
    }
    $pumProgrammeDivision = new CRM_Threepeas_BAO_PumProgrammeDivision();
    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $pumProgrammeDivision->$paramKey = $paramValue;
      }
    }
    $pumProgrammeDivision->save();
    self::storeValues($pumProgrammeDivision, $result);
    return $result;
  }

  /**
   * Function to delete programme division
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 16 Apr 2014
   * @param int $pumProgrammeDivisionId 
   * @return boolean
   * @access public
   * @static
   */
  public static function deleteById($pumProgrammeDivisionId) {
    if (empty($pumProgrammeDivisionId)) {
      throw new Exception('$pumProgrammeDivisionId can not be empty when attempting to delete one');
    }
    $pumProgrammeDivision = new CRM_Threepeas_BAO_PumProgrammeDivision();
    $pumProgrammeDivision->id = $pumProgrammeDivisionId;
    $pumProgrammeDivision->delete();
    return TRUE;
  }
  /**
   * Function to check if a line exists for a programme/country
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 29 Apr 2014
   * @param int $countryId
   * @param int $programmeId
   * @return boolean $exists
   */
  public static function checkCountryExists($countryId, $programmeId) {
    $exists = FALSE;
    if (!empty($programmeId) && !empty($countryId)) {
      $params = array('programme_id' => $programmeId, 'country_id' => $countryId);
      $records = self::getValues($params);
      if (!empty($records)) {
        $exists = TRUE;
      }
    }
    return $exists;
  }
}
