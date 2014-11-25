<?php
/**
 * BAO PumProgramme for dealing with programmes (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 18 Apr 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the AGPL-3.0
 */
class CRM_Threepeas_BAO_PumProgramme extends CRM_Threepeas_DAO_PumProgramme {

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
    $pumProgramme = new CRM_Threepeas_BAO_PumProgramme();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $paramKey => $paramValue) {
        if (isset($fields[$paramKey])) {
          $pumProgramme->$paramKey = $paramValue;
        }
      }
    }
    $pumProgramme->find();
    while ($pumProgramme->fetch()) {
      $row = array();
      self::storeValues($pumProgramme, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }
  /**
   * Function to add or update programme
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
      throw new Exception('Params can not be empty when adding or updating a PumProgramme');
    }
    $pumProgramme = new CRM_Threepeas_BAO_PumProgramme();
    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $pumProgramme->$paramKey = $paramValue;
      }
    }
    $pumProgramme->save();
    self::storeValues($pumProgramme, $result);
    return $result;
  }

  /**
   * Function to delete programme
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 16 Apr 2014
   * @param int $pumProgrammeId 
   * @return boolean
   * @access public
   * @static
   */
  public static function deleteById($pumProgrammeId) {
    if (empty($pumProgrammeId)) {
      throw new Exception('pumProgrammeId can not be empty when attempting to delete one');
    }
    
    CRM_Utils_Hook::pre('delete', 'PumProgramme', $pumProgrammeId, CRM_Core_DAO::$_nullArray);
    
    $pumProgramme = new CRM_Threepeas_BAO_PumProgramme();
    $pumProgramme->id = $pumProgrammeId;
    /*
     * delete linked donation links when programme is deleted
     */
    CRM_Threepeas_BAO_PumDonorLink::deleteByEntityId('Programme', $pumProgramme->id);
    $pumProgramme->delete();
    
    CRM_Utils_Hook::post('delete', 'PumProgramme', $pumProgramme->id, $pumProgramme);
    return TRUE;
  }
  /**
   * Function to check if Programme can be deleted
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 18 Apr 2014
   * @param int $pumProgrammeId
   * @return boolean $canBeDeleted
   * @access public
   * @static
   */
  public static function checkCanBeDeleted($pumProgrammeId) {
    if (empty($pumProgrammeId)) {
      return TRUE;
    }
    /*
     * can not delete if any project for programme
     */
    $pumProject = new CRM_Threepeas_BAO_PumProject();
    $pumProject->programme_id = $pumProgrammeId;
    if ($pumProject->count() > 0) {
      return FALSE;
    }
    return TRUE;
  }
  /**
   * Function to get programme title only with id
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 18 Apr 2014
   * @param int $pumProgrammeId
   * @return string $pumProgramme->title
   * @access public
   * @static
   */
  public static function getProgrammeTitleWithId($pumProgrammeId) {
    if (empty($pumProgrammeId)) {
      return '';
    }
    $pumProgramme = new CRM_Threepeas_BAO_PumProgramme();
    $pumProgramme->id = $pumProgrammeId;
    if ($pumProgramme->find(true)) {
      return $pumProgramme->title;
    } else {
      return '';
    }
  }
  /**
   * Function to check if there is already a programme with the incoming title
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 30 Apr 2014
   * @param string $programmeTitle
   * @return boolean
   * @access public
   * @static
   */
  public static function checkTitleExists($programmeTitle) {
    $programmes = self::getValues(array('title' => $programmeTitle));
    if (empty($programmes)) {
      return FALSE;
    } else {
      return TRUE;
    }
  }
}
