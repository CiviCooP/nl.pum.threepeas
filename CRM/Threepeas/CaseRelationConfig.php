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
  protected $_relationship_type_ids = NULL;
  
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
    $this->setRelationshipTypeIds();
  }
  /**
   * Function to get all case type relationship settings
   * @return array
   * @access public
   */
  public function getAllCaseTypeRelations() {
    return $this->_case_type_relations;
  }
  /**
   * Function to get case type relationship settings for one case type
   * @param string $case_type
   * @return array
   * @access public
   */
  public function getCaseTypeRelations($case_type) {
    return $this->_case_type_relations[$case_type];
  }
  /*
   * Function to get single relationship type id with short name
   * 
   * @return integer
   * @access public
   */
  public function getRelationShipTypeId($relationship_short_name) {
    return $this->getSingleRelationshipTypeId($relationship_short_name);
  }
  /**
   * Function to get relationship_type_id with civicrm_api3
   * 
   * @param string $relationship_short_name
   * @return integer $relationship_type_id
   * @throws Exception when no relationship type id with name_a_b found
   * @access protected
   */
  protected function getSingleRelationshipTypeId($relationship_short_name) {
    $params = array(
      'name_a_b' => $this->_relationship_type_ids[$relationship_short_name],
      'return' => 'id');
    try {
      $relationship_type_id = civicrm_api3('RelationshipType', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('No RelationshipType found with name_a_b '.
        $this->_relationship_type_ids[$relationship_short_name].
        ', error from API RelationshipType Getvalue: '.$ex->getMessage());
    }
    return $relationship_type_id;
  }
  /**
   * Function to set all case type relationship settings
   * 
   * @access protected
   */
  protected function setCaseTypeRelations() {
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
  protected function setRelationshipTypeIds() {
    $this->_relationship_type_ids = array(
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
