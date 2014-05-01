<?php
/**
 * Class following Singleton pattern for specific extension configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 28 Apr 2014
 */
class CRM_Threepeas_Config {
  /*
   * singleton pattern
   */
  static private $_singleton = NULL;
  /*
   * contact sub_type id for Customer
   */
  public $customerContactType = NULL;
  /*
   * group id for Programme Manager
   */
  public $programmeManagersGroupId = NULL;
  /*
   * group id for Project Officer
   */
  public $projectOfficersGroupId = NULL;
  /*
   * group id for Sector Coordinator
   */
  public $sectorCoordinatorsGroupId = NULL;
  /*
   * group id for Country Coordinator
   */
  public $countryCoordinatorsGroupId = NULL;
  /*
   * custom group id for Project Information (used in case Projectintake)
   */
  public $projectCustomGroupId = NULL;
  /*
   * custom group table for cases with field for project
   */
  public $caseCustomTable = NULL;
  public $caseProjectColumn = NULL;
  /**
   * Constructor function
   */
  function __construct() {
    $this->customerContactType = 'Customer';
    $this->setGroupId('Programme Managers');
    $this->setGroupId('Sector Coordinators');
    $this->setGroupId('Country Coordinators');
    $this->setGroupId('Project Officers');
    $this->setCustomGroupId('Projectinformation');
    $this->setCaseCustomData();
  }
  /**
   * Function to return singleton object
   * 
   * @return object $_singleton
   * @access public
   * @static
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Threepeas_Config();
    }
    return self::$_singleton;
  }
  /**
   * Function to get a group ID with the CiviCRM API and store it in property
   * 
   * @param string $title name of the group of whic the id is to be set
   * @access private
   */
  private function setGroupId($title) {
    if (!empty($title)) {
      $groupParams = array('title' => $title, 'return' => 'id');
      try {
        $propName = $this->setGroupProperty($title);
        $this->$propName = civicrm_api3('Group', 'Getvalue', $groupParams);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception(ts('Could not find a group with title '
          .$title.', error from API Group Getvalue : '.$ex->getMessage()));
      }
    }
    return;
  }
  /**
   * Function to set the property that is required
   * 
   * @param string $label ($label that has to be processed into a property name)
   * @return string $property
   * @access private
   */
  private function setGroupProperty($label) {
    $parts = explode(' ', $label);
    $property = strtolower($parts[0]);
    if (isset($parts[1])) {
      $property .= ucfirst($parts[1]);
    }
    $property .= 'GroupId';
    return $property;
  }
  /**
   * Functio to set the custom group id
   * 
   * @param type $name
   * @return type
   * @throws CiviCRM_API3_Exception
   */
  private function setCustomGroupId($name) {
    if (!empty($name)) {
      $customGroupParams = array('name' => $name, 'return' => 'id');
      try {
        $this->projectCustomGroupId = civicrm_api3('CustomGroup', 'Getvalue', $customGroupParams);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new CiviCRM_API3_Exception('Could not find a custom group with name '
          .$name.', error from API CustomGroup Getvalue: '.$ex->getMessage());
      }
    }
    return;
  }
  private function setCaseCustomData() {
    try {
      $customDataSet = civicrm_api3('CustomGroup', 'Getsingle', array('name' => 'caseProject'));
      if (isset($customDataSet['table_name'])) {
        $this->caseCustomTable = $customDataSet['table_name'];
      }
      if (isset($customDataSet['id'])) {
        $projectColumnParams = array('custom_group_id' => $customDataSet['id'], 'name' => 'Project', 'return' => 'column_name');
        try {
          $this->caseProjectColumn = civicrm_api3('CustomField', 'Getvalue', $projectColumnParams);
        } catch(CiviCRM_API3_Exception $ex) {
          $this->caseProjectColumn = NULL;
        }
      }
    } catch(CiviCRM_API3_Exception $ex) {
      $this->caseCustomTable = NULL;
      $this->caseProjectColumn = NULL;
    }
  }
}
