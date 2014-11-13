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
  
  protected $_case_type_relations = NULL;
  /*
   * relationship_type_ids
   */
  protected $_relationship_types = NULL;
  /*
   * ceo and cfo contact_id
   */
  protected $_pum_ceo = NULL;
  protected $_pum_cfo = NULL;
  protected $_ceo_relationship_type_id = NULL;
  protected $_cfo_relationship_type_id = NULL;
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
    $this->set_case_type_relations();
    $this->set_relationship_types();
    $this->set_ceo_cfo();
  }
  /**
   * Function to get all case type relationship settings
   * 
   * @return array
   * @access public
   */
  public function get_all_case_type_relations() {
    return $this->_case_type_relations;
  }
  /**
   * Function to get case type relationship settings for one case type
   * 
   * @param string $case_type
   * @return array
   * @access public
   */
  public function get_case_type_relations($case_type) {
    return $this->_case_type_relations[$case_type];
  }
  /**
   * Function to get single relationship type id with case role label
   * 
   * @return integer
   * @access public
   */
  public function get_relationship_type_id($case_role_label) {
    return $this->get_single_relationship_type_id($case_role_label);
  }
  public function get_pum_ceo() {
    return $this->_pum_ceo;
  }
  public function get_pum_cfo() {
    return $this->_pum_cfo;
  }
  
  /**
   * Function to set CEO and CFO for PUM. Based on expectation that job title CEO
   * and job title CFO for organization PUM Netherlands Senior Experts are there.
   * Assumption is that PUM is contact_id 1
   */
  protected function set_ceo_cfo() {
    $relationship_params = array(
      'contact_id_b' => 1,
      'is_active' => 1,
      'relationship_type_id' => $this->get_employee_relationship_type_id(),
      'options' => array('limit' => 99999));
    $pum_employees = civicrm_api3('Relationship', 'Get', $relationship_params);
    foreach ($pum_employees['values'] as $pum_employee) {
      $this->set_ceo_cfo_values($pum_employee['contact_id_a']);
    }
  }
  /**
   * Function to set ceo and cfo values
   * 
   * @param int $contact_id
   */
  protected function set_ceo_cfo_values($contact_id) {
    $contact_data = civicrm_api3('Contact', 'Getsingle', array('id' => $contact_id));
    if (isset($contact_data['job_title'])) {
      switch ($contact_data['job_title']) {
        case 'CEO':
          $this->_pum_ceo['contact_id'] = $contact_id;
          $this->_pum_ceo['display_name'] = $contact_data['display_name'];
          break;
        case 'CFO':
          $this->_pum_cfo['contact_id'] = $contact_id;
          $this->_pum_cfo['display_name'] = $contact_data['display_name'];
          break;
      }
    }
  }
  /**
   * Function to get the Employee Relationship Type Id
   * 
   * @return int $relationship_type_id
   * @throws Exception
   */
  protected function get_employee_relationship_type_id() {
    $params = array('name_a_b' => 'Employee of', 'return' => 'id');
    try {
      $relationship_type_id = civicrm_api3('RelationshipType', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find relationshi type with name_a_b Employee Of, '
        . 'error from API RelationshipType Getvalue: '.$ex->getMessage());
    }
    return $relationship_type_id;
  }
  /**
   * Function to get relationship_type_id with civicrm_api3
   * 
   * @param string $case_role_label
   * @return integer $relationship_type_id
   * @throws Exception when no relationship type id with name_a_b found
   * @access protected
   */
  protected function get_single_relationship_type_id($case_role_label) {
    $params = array(
      'name_a_b' => $this->_relationship_types[$case_role_label],
      'return' => 'id');
    try {
      $relationship_type_id = civicrm_api3('RelationshipType', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('No RelationshipType found with name_a_b '.
        $this->_relationship_type_ids[$case_role_label].
        ', error from API RelationshipType Getvalue: '.$ex->getMessage());
    }
    return $relationship_type_id;
  }
  /**
   * Function to set all case type relationship settings
   * 
   * @access protected
   */
  protected function set_case_type_relations() {
    $this->_case_type_relations = array(
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
      //'CAPAssessment' => array(
        'CPA' => array(
        'sector_coordinator' => 0,
        'anamon' => 1,
        'authorised_contact' => 1,
        'country_coordinator' => 1,
        'project_officer' => 1,
        'representative' => 1,
        'ceo' => 1,
        'cfo' => 1,
        'recruitment_team' => 0,
        'grant_coordinator' => 1),
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
        'sector_coordinator' => 0,
        'authorised_contact' => 0,
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
  protected function set_relationship_types() {
    $this->_relationship_types = array(
      'anamon' => 'Anamon',
      'authorised_contact' => 'Has authorised',
      'ceo' => 'CEO',
      'cfo' => 'CFO',
      'country_coordinator' => 'Country Coordinator is',
      'grant_coordinator' => 'Grant Coordinator',
      'project_officer' => 'Project Officer is',
      'recruitment_team' => 'Recruitment Team Member',
      'representative' => 'Representative is',
      'sector_coordinator' => 'Sector Coordinator'
    );
  }
}
