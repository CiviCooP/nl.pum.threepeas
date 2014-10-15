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
  public $allContributionStatus = array();
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
   * custom group id for Project Information (used in case Projectintake),
   * country action plan (used in case CP-AP)
   */
  public $projectCustomGroupId = NULL;
  public $capCustomGroupId = NULL;
  /*
   * case type and status option group id
   */
  public $caseTypeOptionGroupId = NULL;
  public $caseTypes = array();
  public $caseStatusOptionGroupId = NULL;
  public $caseStatus = array();
  public $pumCaseTypes = array();
  public $countryActionPlanCaseTypeId = NULL;
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
   * protected sectorTree
   */
  private $sectorTree = array();
  /*
   * config variables for CEO and CFO
   */
  private $pumCfo = array();
  private $pumCeo = array();
  public $ceoRelationshipTypeId = NULL;
  public $cfoRelationshipTypeId = NULL;
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
    
    $this->projectCustomGroupId = $this->setCustomGroupId('Projectinformation');    
    $this->capCustomGroupId = $this->setCustomGroupId('country_action_plan'); 
    
    $this->setCaseOptionGroupId();
    $this->setProjectOptionGroupId();
    
    $this->setGroupId('Programme Managers');
    $this->setGroupId('Projectmanager');
    
    $this->setCaseStatus();
    $this->setCaseTypes();
    
    $this->expertRelationshipTypeId = $this->setRelationshipTypeId('Expert');
    $this->countryCoordinatorRelationshipTypeId = $this->setRelationshipTypeId('Country Coordinator is');
    $this->projectOfficerRelationshipTypeId = $this->setRelationshipTypeId('Project Officer for');
    $this->representativeRelationshipTypeId = $this->setRelationshipTypeId('Representative is');
    $this->sectorCoordinatorRelationshipTypeId = $this->setRelationshipTypeId('Sector Coordinator');
    $this->anamonRelationshipTypeId = $this->setRelationshipTypeId('Anamon');
    $this->ceoRelationshipTypeId = $this->setRelationshipTypeId('CEO');
    $this->cfoRelationshipTypeId = $this->setRelationshipTypeId('CFO');
    
    $this->setActiveProjectList();
    $this->setActiveProgrammeList();
    $this->setActiveCaseList();
    $this->openCaseActTypeId = $this->setActivityTypeId('Open Case');
    $this->setActTargetRecordType();
    $this->setSectorTree();
  	$this->setCeoCfo();  
  }
  public function getCeo() {
    return $this->pumCeo;
  }
  public function getCfo() {
    return $this->pumCfo;
  }  
  public function getSectorTree() {
    return $this->sectorTree;
  }
  private function setSectorTree() {
    /*
     * first check if tag 'Sector' exists
     */
    try {
      $sectorTagId = civicrm_api3('Tag', 'Getvalue', array('name' => 'Sector', 'return' => 'id'));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a Tag called Sector, error from API Tag Getvalue: '.$ex->getMessage());
    }
    $this->sectorTree[] = $sectorTagId;
    $this->getSectorChildren($sectorTagId);
  }
  private function getSectorChildren($sectorTagId) {
    $gotAllChildren = FALSE;
    $levelChildren = array($sectorTagId);
    while ($gotAllChildren == FALSE) {
      foreach ($levelChildren as $levelChildTagId) {
        $childParams = array('parent_id' => $levelChildTagId,'is_selectable' => 1);
        $tagChildren = civicrm_api3('Tag', 'Get', $childParams);
        $gotAllChildren = $this->gotAllSectorChildren($tagChildren['count']);
        if ($tagChildren['count'] > 0) {
          $this->addSectorChildren($tagChildren['values']);
          $levelChildren = $this->sectorTree;
        }
      }
    }    
  }
  private function addSectorChildren($tagChildren) {
    foreach($tagChildren as $tagChild) {
      if (!in_array($tagChild['id'], $this->sectorTree)) {
        $this->sectorTree[] = $tagChild['id'];
      }
    }    
  }
  private function gotAllSectorChildren($count) {
    if ($count == 0) {
      return TRUE;
    } else {
      return FALSE;
    }
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
        $customGroupId = civicrm_api3('CustomGroup', 'Getvalue', $customGroupParams);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new CiviCRM_API3_Exception('Could not find a custom group with name '
          .$name.', error from API CustomGroup Getvalue: '.$ex->getMessage());
      }
    }
    return $customGroupId;
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
    $pumCaseTypes = array('Projectintake', 'Advice', 'BLP', 'RemoteCoaching', 'PDV', 'CPA', 'CTM', 'CAPAssessment');
    try {
      $apiCaseTypes = civicrm_api3('OptionValue', 'Get', array('option_group_id' => $this->caseTypeOptionGroupId));
      foreach ($apiCaseTypes['values'] as $caseTypeId => $caseType) {
        $this->caseTypes[$caseType['value']] = $caseType['label'];
        if (in_array($caseType['label'], $pumCaseTypes)) {
          $this->pumCaseTypes[$caseType['value']] = $caseType['label'];
          if ($caseType['label'] == 'CPA' || $caseType['label'] == 'CAPAssessment') {
            $this->countryActionPlanCaseTypeId = $caseType['value'];
          }
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
   * Function to get all active programmes
   */
  private function setActiveProgrammeList() {
    $programmes = CRM_Threepeas_BAO_PumProgramme::getValues(array('is_active' => 1));
    foreach ($programmes as $programme) {
      if (isset($programme['title'])) {
        $this->activeProgrammeList[$programme['id']] = $programme['title'];
      }
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
      if (isset($project['title'])) {
        $this->activeProjectList[$project['id']] = $project['title'];
      }
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
    $this->allContributionStatus = array_merge($this->activeContributionStatus, $this->inactiveContributionStatus);
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
  /**
   * Function to set CEO and CFO for PUM. Based on expectation that job title CEO
   * and job title CFO for organization PUM Netherlands Senior Experts are there.
   * Assumption is that PUM is contact_id 1
   */
  private function setCeoCfo() {
    $relationshipParams = array(
      'contact_id_b' => 1,
      'is_active' => 1,
      'relationship_type_id' => $this->getEmployeeRelationshipTypeId());
    $pumEmployees = civicrm_api3('Relationship', 'Get', $relationshipParams);
    foreach ($pumEmployees['values'] as $pumEmployee) {
      $this->setCeoCfoValues($pumEmployee['contact_id_a']);
    } 
  }
  private function setCeoCfoValues($contactId) {
    $contactData = civicrm_api3('Contact', 'Getsingle', array('id' => $contactId));
    if (isset($contactData['job_title'])) {
      switch ($contactData['job_title']) {
        case 'CEO':
          $this->pumCeo['contact_id'] = $contactId;
          $this->pumCeo['display_name'] = $contactData['display_name'];
          break;
        case 'CFO':
          $this->pumCfo['contact_id'] = $contactId;
          $this->pumCfo['display_name'] = $contactData['display_name'];
          break;
      }
    }
  }
  private function getEmployeeRelationshipTypeId() {
    $params = array('name_a_b' => 'Employee of', 'return' => 'id');
    try {
      $relationshipTypeId = civicrm_api3('RelationshipType', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find relationshi type with name_a_b Employee Of, '
        . 'error from API RelationshipType Getvalue: '.$ex->getMessage());
    }
    return $relationshipTypeId;
  }
}
