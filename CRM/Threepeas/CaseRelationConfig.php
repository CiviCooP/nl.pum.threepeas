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
  protected $counsellorRelationshipTypeId = NULL;
  /*
   * activity and case status completed
   */
  protected $activityStatusCompleted = NULL;
  protected $caseStatusCompleted = NULL;
  protected $caseStatusError = NULL;
  /*
   * property for all case types to be counted as a main activity for expert
   */
  protected $expertCaseTypes = array();

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
    $this->setCaseStatusCompleted();
    $this->setCaseStatusError();
    $this->setExpertCaseTypes();
    $this->counsellorRelationshipTypeId = civicrm_api3('RelationshipType', 'Getvalue',
      array('name_a_b' => 'Counsellor', 'return' => 'id'));
  }

  /**
   * Getter for counsellor relationship type id
   *
   * @return mixed
   */
  public function getCounsellorRelationshipTypeId() {
    return $this->counsellorRelationshipTypeId;
  }
  /**
   * Function to get the expert case types
   *
   * @return array
   * @access public
   */
  public function getExpertCaseTypes() {
    return $this->expertCaseTypes;
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
   * Function to get the case status id of error
   *
   * @return int
   * @access public
   */
  public function getCaseStatusError() {
    return $this->caseStatusError;
  }

  /**
   * Function to get the case status id of completed
   *
   * @return int
   * @access public
   */
  public function getCaseStatusCompleted() {
    return $this->caseStatusCompleted;
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
   * Function to get relationship types
   *
   * @return null
   */
  public function getRelationshipTypes() {
    return $this->relationshipTypes;
  }

  /**
   * Function to set activity status for completed
   *
   * @throws Exception when option group activity_status not found
   * @throws Exception when activity status completed not found
   * @access protected
   */
  protected function setActivityStatusCompleted() {
    $this->activityStatusCompleted = CRM_Core_OptionGroup::getValue('activity_status', 'Completed', 'name');
  }

  /**
   * Method to set case status for completed
   *
   * @throws Exception when option group case_status not found
   * @throws Exception when case status completed not found
   * @access protected
   */
  protected function setCaseStatusCompleted() {
    $paramsOptionGroup = array('name' => 'case_status', 'return' => 'id');
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $paramsOptionGroup);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find option group with name case_status, '
        . 'error from API OptionGroup Getvalue: '.$ex->getMessage());
    }
    $paramsOptionValue = array(
      'option_group_id' => $optionGroupId,
      'name' => 'Completed',
      'return' => 'value');
    try {
      $this->caseStatusCompleted = civicrm_api3('OptionValue', 'Getvalue', $paramsOptionValue);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find case status with name Completed, '
        . 'error from API OptionValue Getvalue: '.$ex->getMessage());
    }
  }

  /**
   * Method to set case status for error
   *
   * @throws Exception when option group case_status not found
   * @access protected
   */
  protected function setCaseStatusError() {
    $paramsOptionGroup = array('name' => 'case_status', 'return' => 'id');
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $paramsOptionGroup);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find option group with name case_status, '
        . 'error from API OptionGroup Getvalue: '.$ex->getMessage());
    }
    $paramsOptionValue = array(
      'option_group_id' => $optionGroupId,
      'name' => 'Error',
      'return' => 'value');
    try {
      $this->caseStatusError = civicrm_api3('OptionValue', 'Getvalue', $paramsOptionValue);
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Function to set CEO and CFO for PUM. Based on expectation that job title CEO
   * and job title CFO for organization PUM Netherlands Senior Experts are there.
   * Assumption is that PUM is contact_id 1
   *
   * $access protected
   */
  protected function setCeoCfo() {
    $params[1] = array($this->getEmployeeRelationshipTypeId(), 'Integer');
    $params[2] = array(1, 'Integer');
    $sql = "SELECT c.job_title, c.id as contact_id, c.display_name
            FROM civicrm_contact c
             INNER JOIN civicrm_relationship r ON r.contact_id_a = c.id
             WHERE (c.job_title = 'CEO' or c.job_title = 'CFO')
             AND contact_id_b = %2
             and r.relationship_type_id = %1
             and r.is_active = 1
             and (r.start_date IS NULL or r.start_date <= NOW())
             and (r.end_date IS NULL or r.end_date >end_date= NOW())
";
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while($dao->fetch()) {
      switch ($dao->job_title) {
        case 'CEO':
          $this->pumCeo['contact_id'] = $dao->contact_id;
          $this->pumCeo['display_name'] = $dao->display_name;
          break;
        case 'CFO':
          $this->pumCfo['contact_id'] = $dao->contact_id;
          $this->pumCfo['display_name'] = $dao->display_name;
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
        'anamon' => 0,
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
      'ExitExpert' => array(
        'sector_coordinator' => 1,
        'country_coordinator' => 0,
        'authorised_contact' => 0,
        'project_officer' => 0,
        'representative' => 0,
        'anamon' => 0,
        'ceo' => 0,
        'cfo' => 0,
        'recruitment_team' => 0,
        'grant_coordinator' => 0),
      'FactFinding' => array(
        'sector_coordinator' => 1,
        'country_coordinator' => 1,
        'authorised_contact' => 1,
        'project_officer' => 1),
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

  /**
   * Method to set the valid case types for counting expert main activities
   *
   * @access protected
   */
  protected function setExpertCaseTypes() {
    $validCaseTypes = array('Advice', 'Business', 'RemoteCoaching', 'Seminar', 'FactFinding');
    foreach ($validCaseTypes as $validCaseType) {
      $caseType = CRM_Threepeas_Utils::getCaseTypeWithName($validCaseType);
      $this->expertCaseTypes[] = $caseType['value'];
    }
  }
}
