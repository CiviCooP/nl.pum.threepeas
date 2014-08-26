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
   * properties for sponsor link
   */
  public $defaultContributionId = NULL;
  public $inactiveContributionStatus = array();
  public $activeContributionStatus = array();
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
  public $projectCustomGroupId = NULL;  /*
  /*
   * case type and status option group id
   */
  public $caseTypeOptionGroupId = NULL;
  public $caseTypes = array();
  public $caseStatusOptionGroupId = NULL;
  public $caseStatus = array();
  /*
   * project option group id
   */
  public $projectOptionGroupId = NULL;
  /*
   * PUM expert at relationship type id
   */
  public $expertRelationshipTypeId = NULL;
  /*
   * properties to hold active programme, project and case list
   */
  public $activeProgrammeList = array();
  public $activeProjectList = array();
  public $activeCaseList = array();
  /**
   * Constructor function
   */
  function __construct() {
    $this->setDefaultContributionId(4);
    $this->setInactiveContributionStatus();
    $this->setCustomerContactType('Customer');
    $this->setCountryContactType('Country');
    $this->setCountryCustomField('civicrm_country_id');
    $this->setCountryCustomTable('pumCountry');
    $this->setGroupId('Programme Managers');
    $this->setGroupId('Sector Coordinators');
    $this->setGroupId('Country Coordinators');
    $this->setGroupId('Project Officers');
    $this->setCustomGroupId('Projectinformation');    
    $this->setCaseOptionGroupId();
    $this->setProjectOptionGroupId();
    $this->setCaseStatus();
    $this->setCaseTypes();
    $this->expertRelationshipTypeId = $this->setRelationshipTypeId('Expert');
    $this->setActiveProjectList();
    $this->setActiveProgrammeList();
    $this->setActiveCaseList();
  }
  private function setDefaultContributionId($contributionId) {
    $this->defaultContributionId = $contributionId;
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
    try {
      $apiCaseTypes = civicrm_api3('OptionValue', 'Get', array('option_group_id' => $this->caseTypeOptionGroupId));
      foreach ($apiCaseTypes['values'] as $caseTypeId => $caseType) {
        $this->caseTypes[$caseTypeId] = $caseType['label'];
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $this->caseTypes = array();
    }
  }
  private function setCaseStatus() {
    try {
      $apiCaseStatus = civicrm_api3('OptionValue', 'Get', array('option_group_id' => $this->caseStatusOptionGroupId));
      foreach ($apiCaseStatus['values'] as $caseStatusId => $caseStatus) {
        $this->caseStatus[$caseStatusId] = $caseStatus['label'];
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $this->caseStatus = array();
    }
  }
  /**
   * Function to get a realtionship type ID with the CiviCRM API and store it in property
   * 
   * @param string $title name of the group of whic the id is to be set
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
   * Function to get all active programmes
   */
  private function setActiveProgrammeList() {
    $programmes = CRM_Threepeas_BAO_PumProgramme::getValues(array('is_active' => 1));
    foreach ($programmes as $programme) {
      $this->activeProgrammeList[$programme['id']] = $programme['title'];
    }
    $this->activeProgrammeList[0] = '- select -';
    asort($this->activeProgrammeList);
  }
  /**
   * Function to get all active projects
   */
  private function setActiveProjectList() {
    $projects = CRM_Threepeas_BAO_PumProject::getValues(array('is_active' => 1));
    foreach ($projects as $project) {
      $this->activeProjectList[$project['id']] = $project['title'];
    }
    $this->activeProjectList[0] = '- select -';
    asort($this->activeProjectList);
  }
  /**
   * Function to get all active cases
   */
  private function setActiveCaseList() {
    $query = 'SELECT a.id, a.subject, b.label FROM civicrm_case a '
      . 'LEFT JOIN civicrm_option_value b ON a.case_type_id = b.value AND option_group_id = '
      .$this->caseTypeOptionGroupId.' WHERE is_deleted = 0';
    $dao = CRM_Core_DAO::executeQuery($query);
    while ($dao->fetch()) {
      $this->activeCaseList[$dao->id] = $dao->subject.' ('.$dao->label.')';
    }
    $this->activeCaseList[0] = '- select -';
    asort($this->activeCaseList);
  }
  /*
   * Function to set the names of the contribution statuses that are deemed
   * Inactive and are not to be selected when linking
   */
  private function setInactiveContributionStatus() {
    $inactiveContributionStatus = array('Cancelled', 'Failed', 'Refunded');
    try {
      $params = array('name'=> 'contribution_status', 'return' => 'id');
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $params);
      $optionValues = civicrm_api3('OptionValue', 'Get', array('option_group_id' => $optionGroupId));
    } catch (CiviCRM_API3_Exception $ex) {
        $this->inactiveContributionStatus = array();
        $this->activeContributionStatus = array();
    }
    foreach ($optionValues['values'] as $optionValue) {
      $found = CRM_Utils_Array::key($optionValue['name'], $inactiveContributionStatus);
      if (!is_null($found)) {
        $this->inactiveContributionStatus[$optionValue['value']] = $optionValue['name'];
      } else {
        $this->activeContributionStatus[$optionValue['value']] = $optionValue['name'];
      }
    }
  }
}
