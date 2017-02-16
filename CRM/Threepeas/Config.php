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
   * case status for Error
   */
  protected $caseErrorStatusId = NULL;
  /*
   * contact sub_type id for Customer and Country
   */
  public $customerContactType = NULL;
  public $countryContactType = NULL;
  public $expertContactType = NULL;
  /*
   * custom field for country key and custom table
   */
  public $countryCustomFieldId = NULL;
  public $countryCustomFieldColumn = NULL;
  public $countryCustomTable = NULL;
  /*
   * custom group id for Project Information (used in case Projectintake),
   * country action plan (used in case CP-AP)
   */
  public $projectCustomGroupId = NULL;
  protected $projectCustomTableName = NULL;
  protected $projectCustomFields = array();
  /*
   * case type and status option group id
   */
  public $caseTypeOptionGroupId = NULL;
  public $caseTypes = array();
  public $caseStatusOptionGroupId = NULL;
  public $caseStatus = array();
  protected $capCaseTypeId = NULL;
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
  public $anamonRelationshipTypeId = NULL;
  /*
   * properties to hold active programme, project and case list
   */
  public $activeProgrammeList = array();
  public $activeProjectList = array();
  public $activeCaseList = array();
  /*
   * group for Programme en Project Managers
   */
  public $programmeManagersGroupId = NULL;
  public $projectmanagerGroupId = NULL;
  /*
   * activity type for Open Case
   */
  public $openCaseActTypeId = NULL;
  /*
   * activity record type for target contacts
   */
  public $actTargetRecordType = NULL;  
  /*
   * protected activity type id for Assessment Project Request by Rep
   */
  protected $assessmentRepActTypeId = NULL;
  /**
   * Constructor function
   */
  function __construct() {    
    $this->setCustomerContactType('Customer');
    $this->setCountryContactType('Country');
    $this->setExpertContactType('Expert');
    $this->setCountryCustomField('civicrm_country_id');
    $this->setCountryCustomTable('pumCountry');
    
    $projectCustomGroup = $this->setCustomGroup('Projectinformation');
    $this->projectCustomGroupId = $projectCustomGroup['id'];
    $this->projectCustomTableName = $projectCustomGroup['table_name'];
    $this->projectCustomFields = $this->setCustomFields($this->projectCustomGroupId);
    
    $this->setCaseOptionGroupId();
    $this->setProjectOptionGroupId();
    
    $this->setGroupId('Programme Managers');
    $this->setGroupId('Projectmanager');
    
    $this->setCaseStatus();
    $this->setCaseTypes();
    
    $this->expertRelationshipTypeId = $this->setRelationshipTypeId('Expert');
    
    $this->setActiveProjectList();
    $this->setActiveProgrammeList();
    $this->setActiveCaseList();
    $this->openCaseActTypeId = $this->setActivityTypeId('Open Case');
    $this->setActTargetRecordType();
    $this->assessmentRepActTypeId = $this->setActivityTypeId('Assessment Project Request by Rep');
  }
  public function getProjectCustomFields() {
    return $this->projectCustomFields;
  }
  public function getProjectCustomGroupTableName() {
    return $this->projectCustomTableName;
  }
  public function getCaseErrorStatusId() {
    return $this->caseErrorStatusId;
  }
  public function getCapCaseTypeId() {
    return $this->capCaseTypeId;
  }
  public function getAssessmentRepActTypeId() {
    return $this->assessmentRepActTypeId;
  }

  private function setCustomerContactType($customerContactType) {
    $this->customerContactType = $customerContactType;
  }
  private function setCountryContactType($countryContactType) {
    $this->countryContactType = $countryContactType;
  }
  private function setExpertContactType($expertContactType) {
    $this->expertContactType = $expertContactType;
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
  /** Functio to set the custom group id
  *
  * @param string $name
  * @return array $customGroupId
  * @throws Exception when error from API
  */
  private function setCustomGroup($name) {
    if (!empty($name)) {
      $customGroupParams = array('name' => $name);
      try {
        return civicrm_api3('CustomGroup', 'Getsingle', $customGroupParams);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception(ts('Could not find a unique custom group with name '
          .$name.', error from API CustomGroup Getsingle: ').$ex->getMessage());
      }
    }
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
      $caseParams = array(
        'option_group_id' => $this->caseTypeOptionGroupId,
        'options' => array('limit' => 9999));
      $apiCaseTypes = civicrm_api3('OptionValue', 'Get', $caseParams);
      foreach ($apiCaseTypes['values'] as $caseTypeId => $caseType) {
        $this->caseTypes[$caseType['value']] = $caseType['label'];
        if ($caseType['label'] == 'CAPAssessment') {
          $this->capCaseTypeId = $caseType['value'];
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
        if ($caseStatus['name'] == 'Error') {
          $this->caseErrorStatusId = $caseStatus['value'];
        }
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $this->caseStatus = array();
    }
  }
  /**
   * Function to get a relationship type ID with the CiviCRM API and store it in property
   * 
   * @param string $name name of the group of whic the id is to be set
   * @return int $relationshipTypeId
   * @throws Exception when error from API
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
    $this->activeProgrammeList[0] = '- select -';
    $programmes = CRM_Threepeas_BAO_PumProgramme::getValues(array('is_active' => 1));
    @uasort($programmes, 'CRM_Threepeas_Utils::sortArrayByTitle');
    foreach ($programmes as $programme) {
      if (isset($programme['title'])) {
        $this->activeProgrammeList[$programme['id']] = $programme['title'];
      }
    }
  }
  /**
   * Function to get all active projects
   */
  private function setActiveProjectList() {
    $this->activeProjectList[0] = '- select -';
    $projects = CRM_Threepeas_BAO_PumProject::getValues(array('is_active' => 1));
    @uasort($projects, 'CRM_Threepeas_Utils::sortArrayByTitle');
    foreach ($projects as $project) {
      if (isset($project['title'])) {
        $this->activeProjectList[$project['id']] = $project['title'];
      }
    }
  }
  /**
   * Function to get all active cases
   */
  private function setActiveCaseList() {
    $this->activeCaseList[0] = '- select -';
    $query = 'SELECT a.id, a.subject, b.label FROM civicrm_case a '
      . 'LEFT JOIN civicrm_option_value b ON a.case_type_id = b.value AND option_group_id = '
      .$this->caseTypeOptionGroupId.' WHERE is_deleted = 0 ORDER BY a.subject';
    $dao = CRM_Core_DAO::executeQuery($query);
    while ($dao->fetch()) {
      $this->activeCaseList[$dao->id] = $dao->subject.' ('.$dao->label.')';
    }
  }
  /**
   * Function to get a group ID with the CiviCRM API and store it in property
   *
   * @param string $title name of the group of whic the id is to be set
   * @throws Exception when error from API
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
          .$title.', error from API Group Getvalue : ').$ex->getMessage());
      }
    }
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
   * Function to get an activity type id with the CiviCRM API
   */
  private function setActivityTypeId($name) {
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', array('name' => 'activity_type', 'return' => 'id'));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception(ts('Could not find an option group with name activity_type, error from API OptionGroup Getvalue : ').$ex->getMessage());
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
  private function setActTargetRecordType()
  {
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', array('name' => 'activity_contacts', 'return' => 'id'));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception(ts('Could not find an option group with name activity_contacts, error from API OptionGroup Getvalue : ') . $ex->getMessage());
    }
    $params = array('option_group_id' => $optionGroupId, 'name' => 'Activity Targets', 'return' => 'value');
    try {
      $this->actTargetRecordType = civicrm_api3('OptionValue', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $this->actTargetRecordType = NULL;
      throw new Exception(ts('Could not find an option value with name Activity Targets in group activity_contacts, error from API OptionValue Getvalue : ') . $ex->getMessage());
    }
  }

  /**
   * Method to set custom fields array
   *
   * @param int $customGroupId
   * @return array
   * @throws API_Exception when error from API CustomField Get
   */
  private function setCustomFields($customGroupId) {
    try {
      $customFields = civicrm_api3("CustomField", "Get", array('custom_group_id' => $customGroupId));
      return $customFields['values'];
    } catch (CiviCRM_API3_Exception $ex) {
      throw new API_Exception("Could not find custom fields for custom group id ".$customGroupId
        .", error from API CustomField Get: ".$ex->getMessage());
    }
  }
}
