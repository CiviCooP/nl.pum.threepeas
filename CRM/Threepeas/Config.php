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
   * contact sub_type id for Customer and Country
   */
  public $customerContactType = NULL;
  public $countryContactType = NULL;
  /*
   * custom field for country key and custom table
   */
  public $countryCustomFieldId = NULL;
  public $countryCustomFieldColumn = NULL;
  public $countryCustomTable = NULL;
  /* 
   * custom group id for Project Information (used in case Projectintake)
   */
  public $projectCustomGroupId = NULL;  /*
  /*
   * case type and status option group id
   */
  public $caseTypeOptionGroupId = NULL;
  public $caseTypes = array();
  public $caseStatusOptionGroupId = NULL;
  public $caseStatus = array();
  public $pumCaseTypes = array();
  /*
   * project option group id
   */
  public $projectOptionGroupId = NULL;
  /*
   * PUM relationship types
   */
  public $expertRelationshipTypeId = NULL;
  public $countryCoordinatorRelationshipTypeId = NULL;
  public $projectOfficerRelationshipTypeId = NULL;
  public $representativeRelationshipTypeId = NULL;
  public $sectorCoordinatorRelationshipTypeId = NULL;
  /*
   * activity type for Open Case
   */
  public $openCaseActTypeId = NULL;
  /*
   * activity record type for target contacts
   */
  public $actTargetRecordType = NULL;
  /**
   * Constructor function
   */
  function __construct() {
    $this->setCustomerContactType('Customer');
    $this->setCountryContactType('Country');
    $this->setCountryCustomField('civicrm_country_id');
    $this->setCountryCustomTable('pumCountry');
    $this->setCustomGroupId('Projectinformation');    
    $this->setCaseOptionGroupId();
    $this->setProjectOptionGroupId();
    $this->setCaseStatus();
    $this->setCaseTypes();
    $this->expertRelationshipTypeId = $this->setRelationshipTypeId('Expert');
    $this->countryCoordinatorRelationshipTypeId = $this->setRelationshipTypeId('Country Coordinator for');
    $this->projectOfficerRelationshipTypeId = $this->setRelationshipTypeId('Project Officer');
    $this->representativeRelationshipTypeId = $this->setRelationshipTypeId('Representative for');
    $this->sectorCoordinatorRelationshipTypeId = $this->setRelationshipTypeId('Sector Coordinator');
    $this->openCaseActTypeId = $this->setActivityTypeId('Open Case');
    $this->setActTargetRecordType();
  }
  private function setCustomerContactType($customerContactType) {
    $this->customerContactType = $customerContactType;
  }
  private function setCountryContactType($countryContactType) {
    $this->countryContactType = $countryContactType;
  }
  private function setCountryCustomTable($name) {
    try {
      $this->countryCustomTable = civicrm_api3('CustomGroup', 'Getvalue', 
        array('name' => $name, 'return' => 'table_name'));
    } catch (CiviCRM_API3_Exception $ex) {
      $this->countryCustomTable = '';
    }
  }
  private function setCountryCustomField($fieldName) {
    try {
      $customField = civicrm_api3('CustomField', 'Getsingle', array('name' => $fieldName));
      $this->countryCustomFieldId = $customField['id'];
      $this->countryCustomFieldColumn = $customField['column_name'];
    } catch (CiviCRM_API3_Exception $ex) {
      $this->countryCustomFieldId = 0;
      $this->countryCustomFieldColumn = '';
    }
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
  private function setCaseOptionGroupId() {
    try {
      $this->caseTypeOptionGroupId = civicrm_api3('OptionGroup', 'Getvalue', 
        array('name' => 'case_type', 'return' => 'id'));
    } catch (CiviCRM_API3_Exception $ex) {
      $this->caseTypeOptionGroupId = 0;
    }
    try {
      $this->caseStatusOptionGroupId = civicrm_api3('OptionGroup', 'Getvalue', 
        array('name' => 'case_status', 'return' => 'id'));
    } catch (CiviCRM_API3_Exception $ex) {
      $this->caseStatusOptionGroupId = 0;
    }
  }
  private function setProjectOptionGroupId() {
    try {
      $this->projectOptionGroupId = civicrm_api3('OptionGroup', 'Getvalue', 
        array('name' => 'pum_project', 'return' => 'id'));
    } catch (CiviCRM_API3_Exception $ex) {
      $this->projectOptionGroupId = 0;
    }
    try {
      $this->caseStatusOptionGroupId = civicrm_api3('OptionGroup', 'Getvalue', 
        array('name' => 'case_status', 'return' => 'id'));
    } catch (CiviCRM_API3_Exception $ex) {
      $this->caseStatusOptionGroupId = 0;
    }
  }
  private function setCaseTypes() {
    $pumCaseTypes = array('Projectintake', 'Advice', 'BLP', 'RemoteCoaching', 'PDV', 'CAP', 'CTM');
    try {
      $apiCaseTypes = civicrm_api3('OptionValue', 'Get', array('option_group_id' => $this->caseTypeOptionGroupId));
      foreach ($apiCaseTypes['values'] as $caseTypeId => $caseType) {
        $this->caseTypes[$caseType['value']] = $caseType['label'];
        if (in_array($caseType['label'], $pumCaseTypes)) {
          $this->pumCaseTypes[$caseType['value']] = $caseType['label'];
        }
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $this->caseTypes = array();
    }
  }
  private function setCaseStatus() {
    try {
      $apiCaseStatus = civicrm_api3('OptionValue', 'Get', array('option_group_id' => $this->caseStatusOptionGroupId));
      foreach ($apiCaseStatus['values'] as $caseStatusId => $caseStatus) {
        $this->caseStatus[$caseStatus['value']] = $caseStatus['label'];
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $this->caseStatus = array();
    }
  }
  /**
   * Function to get a relationship type ID with the CiviCRM API and store it in property
   * 
   * @param string $name name of the group of whic the id is to be set
   * @access private
   */
  private function setRelationshipTypeId($name) {
    if (!empty($name)) {
      $relationshipTypeParams = array('name_a_b' => $name, 'return' => 'id');
      try {
        $relationshipTypeId = civicrm_api3('RelationshipType', 'Getvalue', $relationshipTypeParams);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception(ts('Could not find a relationshipType with name_a_b '
          .$name.', error from API Group Getvalue : '.$ex->getMessage()));
      }
    }
    return $relationshipTypeId;
  }
  /**
   * Function to get an activity type id with the CiviCRM API
   */
  private function setActivityTypeId($name) {
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', array('name' => 'activity_type', 'return' => 'id'));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find an option group with name activity_type, error from API OptionGroup Getvalue : '.$ex->getMessage());
    }
    $params = array('option_group_id' => $optionGroupId, 'name' => $name, 'return' => 'value');
    try {
      $activityTypeId = civicrm_api3('OptionValue', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $activityTypeId = 0;
    }
    return $activityTypeId;
  }
  /**
   * Function to retrieve the record type for activity targets
   */
  private function setActTargetRecordType() {
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', array('name' => 'activity_contacts', 'return' => 'id'));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find an option group with name activity_contacts, error from API OptionGroup Getvalue : '.$ex->getMessage());
    }
    $params = array('option_group_id' => $optionGroupId, 'name' => 'Activity Targets', 'return' => 'value');
    try {
      $this->actTargetRecordType = civicrm_api3('OptionValue', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $this->actTargetRecordType = NULL;
      throw new Exception('Could not find an option value with name Activity Targets in group activity_contacts, error from API OptionValue Getvalue : '.$ex->getMessage());
    }
  }
}
