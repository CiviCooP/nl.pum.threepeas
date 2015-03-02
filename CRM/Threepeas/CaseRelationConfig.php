<?php
/**
 * Class following Singleton pattern for specific extension configuration
 * as far as the default Case Relations are concerned for PUM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 11 November 2014
 */
class CRM_Threepeas_CaseRelationConfig {
  /*
   * singleton pattern
   */
  static private $_singleton = NULL;
  
  protected $caseTypeRelations = NULL;
  /*
   * relationship_type_ids
   */
  protected $relationshipTypes = NULL;
  /*
   * ceo and cfo contact_id
   */
  protected $pumCeo = NULL;
  protected $pumCfo = NULL;
  protected $ceoRelationshipTypeId = NULL;
  protected $cfoRelationshipTypeId = NULL;
  /*
   * activity status completed
   */
  protected $activityStatusCompleted = NULL;

  /**
   * Function to return singleton object
   * 
   * @return object $_singleton
   * @access public
   * @static
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Threepeas_CaseRelationConfig();
    }
    return self::$_singleton;
  }

  /**
   * Constructor function
   */
  function __construct() {
    $this->setCaseTypeRelations();
    $this->setRelationshipTypes();
    $this->setCeoCfo();
    $this->setActivityStatusCompleted();
  }

  /**
   * Function to get the activity status id of completed
   *
   * @return int
   * @access public
   */
  public function getActivityStatusCompleted() {
    return $this->activityStatusCompleted;
  }

  /**
   * Function to get all case type relationship settings
   * 
   * @return array
   * @access public
   */
  public function getAllCaseTypeRelations() {
    return $this->caseTypeRelations;
  }

  /**
   * Function to get case type relationship settings for one case type
   * 
   * @param string $caseType
   * @return array
   * @access public
   */
  public function getCaseTypeRelations($caseType) {
    if (isset($this->caseTypeRelations[$caseType])) {
      return $this->caseTypeRelations[$caseType];
    } else {
      return array();
    }
  }

  /**
   * Function to get single relationship type id with case role label
   *
   * @param string $caseRoleLabel
   * @return integer
   * @access public
   */
  public function getRelationshipTypeId($caseRoleLabel) {
    return $this->getSingleRelationshipTypeId($caseRoleLabel);
  }

  /**
   * Funtion to get CEO
   *
   * @return array
   * @access public
   */
  public function getPumCeo() {
    return $this->pumCeo;
  }

  /**
   * Funtion to get CFO
   *
   * @return array
   * @access public
   */
  public function getPumCfo() {
    return $this->pumCfo;
  }

  /**
   * Function to set activity status for completed
   * 
   * @throws Exception when option group activity_status not found
   * @throws Exception when activity status completed not found
   * @access protected
   */
  protected function setActivityStatusCompleted() {
    $paramsOptionGroup = array('name' => 'activity_status', 'return' => 'id');
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $paramsOptionGroup);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find option group with name activity_status, '
        . 'error from API OptionGroup Getvalue: '.$ex->getMessage());
    }
    $paramsOptionValue = array(
      'option_group_id' => $optionGroupId,
      'name' => 'Completed',
      'return' => 'value');
    try {
      $this->activityStatusCompleted = civicrm_api3('OptionValue', 'Getvalue', $paramsOptionValue);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find activity status with name Completed, '
        . 'error from API OptionValue Getvalue: '.$ex->getMessage());
    }
  }

  /**
   * Function to set CEO and CFO for PUM. Based on expectation that job title CEO
   * and job title CFO for organization PUM Netherlands Senior Experts are there.
   * Assumption is that PUM is contact_id 1
   *
   * $access protected
   */
  protected function setCeoCfo() {
    $relationshipParams = array(
      'contact_id_b' => 1,
      'is_active' => 1,
      'relationship_type_id' => $this->getEmployeeRelationshipTypeId(),
      'options' => array('limit' => 99999));
    $pumEmployees = civicrm_api3('Relationship', 'Get', $relationshipParams);
    foreach ($pumEmployees['values'] as $pumEmployee) {
      $this->setCeoCfoValues($pumEmployee['contact_id_a']);
    }
  }

  /**
   * Function to set ceo and cfo values
   * 
   * @param int $contactId
   * @access protected
   */
  protected function setCeoCfoValues($contactId) {
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

  /**
   * Function to get the Employee Relationship Type Id
   * 
   * @return int $relationshipTypeId
   * @throws Exception
   * @access protected
   */
  protected function getEmployeeRelationshipTypeId() {
    $params = array('name_a_b' => 'Employee of', 'return' => 'id');
    try {
      $relationshipTypeId = civicrm_api3('RelationshipType', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find relationship type with name_a_b Employee Of, '
        . 'error from API RelationshipType Getvalue: '.$ex->getMessage());
    }
    return $relationshipTypeId;
  }
  /**
   * Function to get relationship_type_id with civicrm_api3
   * 
   * @param string $caseRoleLabel
   * @return integer $relationshipTypeId
   * @throws Exception when no relationship type id with name_a_b found
   * @access protected
   */
  protected function getSingleRelationshipTypeId($caseRoleLabel) {
    $params = array(
      'name_a_b' => $this->relationshipTypes[$caseRoleLabel],
      'return' => 'id');
    try {
      $relationshipTypeId = civicrm_api3('RelationshipType', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('No RelationshipType found with name_a_b '.
        $this->relationshipTypes[$caseRoleLabel].
        ', error from API RelationshipType Getvalue: '.$ex->getMessage());
    }
    return $relationshipTypeId;
  }
  /**
   * Function to set all case type relationship settings
   * 
   * @access protected
   */
  protected function setCaseTypeRelations() {
    $this->caseTypeRelations = array(
      'Advice' => array(
        'authorised_contact' => 1,
        'sector_coordinator' => 1,
        'country_coordinator' => 1,
        'project_officer' => 1,
        'representative' => 1,
        'ceo' => 0,
        'anamon' => 0,
        'cfo' => 0,
        'recruitment_team' => 0,
        'grant_coordinator' => 0),
      'BLP' => array(
        'authorised_contact' => 1,
        'sector_coordinator' => 1,
        'country_coordinator' => 1,
        'project_officer' => 1,
        'representative' => 1,
        'ceo' => 0,
        'anamon' => 0,
        'cfo' => 0,
        'recruitment_team' => 0,
        'grant_coordinator' => 0),
      'Business' => array(
        'authorised_contact' => 1,
        'sector_coordinator' => 1,
        'country_coordinator' => 1,
        'project_officer' => 1,
        'representative' => 1,
        'ceo' => 0,
        'anamon' => 0,
        'cfo' => 0,
        'recruitment_team' => 0,
        'grant_coordinator' => 0),
      'CAPAssessment' => array(
        'sector_coordinator' => 0,
        'anamon' => 0,
        'authorised_contact' => 0,
        'country_coordinator' => 1,
        'project_officer' => 1,
        'representative' => 0,
        'ceo' => 1,
        'cfo' => 1,
        'recruitment_team' => 0,
        'grant_coordinator' => 0),
      'CTM' => array(
        'sector_coordinator' => 0,
        'country_coordinator' => 1,
        'authorised_contact' => 0,
        'anamon' => 0,
        'project_officer' => 1,
        'representative' => 0,
        'ceo' => 0,
        'cfo' => 1,
        'recruitment_team' => 0,
        'grant_coordinator' => 0),
      'Expertapplication' => array(
        'sector_coordinator' => 1,
        'country_coordinator' => 0,
        'authorised_contact' => 0,
        'project_officer' => 0,
        'anamon' => 0,
        'representative' => 0,
        'ceo' => 0,
        'cfo' => 0,
        'recruitment_team' => 1,
        'grant_coordinator' => 0),
      'Grant' => array(
        'sector_coordinator' => 1,
        'country_coordinator' => 1,
        'authorised_contact' => 1,
        'project_officer' => 1,
        'representative' => 1,
        'anamon' => 0,
        'ceo' => 0,
        'cfo' => 1,
        'recruitment_team' => 0,
        'grant_coordinator' => 1),
      'Projectintake' => array(
        'sector_coordinator' => 1,
        'authorised_contact' => 1,
        'country_coordinator' => 1,
        'project_officer' => 1,
        'representative' => 1,
        'anamon' => 1,
        'ceo' => 0,
        'cfo' => 0,
        'recruitment_team' => 0,
        'grant_coordinator' => 0),
      'PDV' => array(
        'sector_coordinator' => 0,
        'country_coordinator' => 1,
        'project_officer' => 1,
        'authorised_contact' => 0,
        'representative' => 0,
        'anamon' => 0,
        'ceo' => 1,
        'cfo' => 0,
        'recruitment_team' => 0,
        'grant_coordinator' => 0),
      'RemoteCoaching' => array(
        'sector_coordinator' => 1,
        'country_coordinator' => 1,
        'authorised_contact' => 1,
        'project_officer' => 1,
        'representative' => 1,
        'anamon' => 0,
        'ceo' => 0,
        'cfo' => 0,
        'recruitment_team' => 0,
        'grant_coordinator' => 0),
      'Seminar' => array(
        'sector_coordinator' => 1,
        'country_coordinator' => 1,
        'authorised_contact' => 1,
        'project_officer' => 1,
        'representative' => 1,
        'anamon' => 0,
        'ceo' => 0,
        'cfo' => 0,
        'recruitment_team' => 0,
        'grant_coordinator' => 0),
      );
  }
  /**
   * Function to set the relationship_type_ids for the case default roles
   * 
   * @access protected
   */
  protected function setRelationshipTypes() {
    $this->relationshipTypes = array(
      'anamon' => 'Anamon',
      'authorised_contact' => 'Has authorised',
      'ceo' => 'CEO',
      'cfo' => 'CFO',
      'country_coordinator' => 'Country Coordinator is',
      'grant_coordinator' => 'Grant Coordinator',
      'project_officer' => 'Project Officer for',
      'recruitment_team' => 'Recruitment Team Member',
      'representative' => 'Representative is',
      'sector_coordinator' => 'Sector Coordinator'
    );
  }
}
