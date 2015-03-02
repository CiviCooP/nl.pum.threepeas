<?php
/**
 * BAO PumContributionProjects for dealing with civicrm_contribution_number_projects (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 19 August 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the AGPL-3.0
 */
class CRM_Threepeas_BAO_PumContributionProjects extends CRM_Threepeas_DAO_PumContributionProjects {

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
    $pumContributionProjects = new CRM_Threepeas_BAO_PumContributionProjects();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $paramKey => $paramValue) {
        if (isset($fields[$paramKey])) {
          $pumContributionProjects->$paramKey = $paramValue;
        }
      }
    }
    $pumContributionProjects->find();
    while ($pumContributionProjects->fetch()) {
      $row = array();
      self::storeValues($pumContributionProjects, $row);
      $result[$row['contribution_id']] = $row;
    }
    return $result;
  }
  /**
   * Function to add or update number of projects
   * 
   * @param array $params
   * @return array $result
   * @throws Exception when params are empty
   * @access public
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params) || !isset($params['contribution_id'])) {
      throw new Exception('Params can not be empty and has to contain contribution_id when adding or updating Number of Projects for Contribution');
    }
    $pumContributionProjects = new CRM_Threepeas_BAO_PumContributionProjects();
    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $pumContributionProjects->$paramKey = $paramValue;
      }
    }
    $pumContributionProjects->save();
    self::storeValues($pumContributionProjects, $result);
    return $result;
  }
  /**
   * Function to delete contribution projects
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 19 Aug 2014
   * @param string $contributionId
   */
  public static function deleteById($contributionId) {
    if (!empty($contributionId)) {
      $pumContributionProjects = new CRM_Threepeas_BAO_PumContributionProjects();
      $pumContributionProjects->contribution_id = $contributionId;
      $pumContributionProjects->find();
      if ($pumContributionProjects->fetch()) {
        $pumContributionProjects->delete();
      }
    }
  }
}