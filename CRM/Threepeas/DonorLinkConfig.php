<?php
/**
 * Class following Singleton pattern for specific extension configuration
 * for Donor Links PUM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 18 November 2014
 */
class CRM_Threepeas_DonorLinkConfig {
  /*
   * singleton pattern
   */
  static private $_singleton = NULL;
  /*
   * properties for sponsor link
   */
  protected $_default_contribution_id = NULL;
  protected $_inactive_contribution_status = array();
  protected $_active_contribution_status = array();
  protected $_all_contribution_status = array();
  protected $_grant_donation_financial_type = NULL;
  protected $_financial_type_ids = array();
  protected $_donation_financial_type = NULL;
  protected $_grant_case_type = NULL;
  protected $_donation_case_types = NULL;
  /**
   * Function to return singleton object
   * 
   * @return object $_singleton
   * @access public
   * @static
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Threepeas_DonorLinkConfig();
    }
    return self::$_singleton;
  }
  /**
   * Constructor function
   */
  function __construct() {
    $this->_default_contribution_id = 4;
    $this->_grant_case_type = 'Grant';
    $this->set_contribution_status();
    $this->_grant_donation_financial_type = 'Grant Donation';
    $this->_donation_financial_type = 'Donation';
    $this->set_financial_type_id($this->_grant_donation_financial_type);
    $this->set_financial_type_id($this->_donation_financial_type);
    $this->set_donation_case_types();
  }
  /**
   * Function to get case types to exclude from donor link
   * 
   * @return array
   * @access public
   */
  public function get_donation_case_types() {
    return $this->_donation_case_types;
  }
  
  /**
   * Function to get grant case type
   * 
   * @return string
   * @access public
   */
  public function get_grant_case_type() {
    return $this->_grant_case_type;
  }
  /**
   * Function to get default contribution_id
   * 
   * @return int
   * @access public
   */
  public function get_default_contribution_id() {
    return $this->_default_contribution_id;
  }
  /**
   * Function to get inactive contribution statusses
   * 
   * @return array
   * @access public
   */
  public function get_inactive_contribution_status() {
    return $this->_inactive_contribution_status;
  }
  /**
   * Function to get active contribution statusses
   * 
   * @return array
   * @access public
   */
  public function get_active_contribution_status() {
    return $this->_active_contribution_status;
  }
  /**
   * Function to get all contribution statusses
   * 
   * @return array
   * @access public
   */
  public function get_all_contribution_status() {
    return $this->_all_contribution_status;
  }
  /**
   * Function to get the grant donation financial type
   * @return string
   * @access public
   */
  public function get_grant_donation_financial_type() {
    return $this->_grant_donation_financial_type;
  }
  /**
   * Function to get the donation financial type
   * @return string
   * @access public
   */
  public function get_donation_financial_type() {
    return $this->_donation_financial_type;
  }
  /**
   * Function to get the grant donation financial type id
   * @return int
   * @access public
   */
  public function get_grant_donation_financial_type_id() {
    return $this->_financial_type_ids[$this->_grant_donation_financial_type];
  }
  /**
   * Function to get the donation financial type id
   * @return int
   * @access public
   */
  public function get_donation_financial_type_id() {
    return $this->_financial_type_ids[$this->_donation_financial_type];
  }
  /**
   * Function to set contribution statusses
   * 
   * @access protected
   */
  protected function set_contribution_status() {
    $inactive_contribution_status = array('Cancelled', 'Failed', 'Refunded');
    try {
      $params = array('name'=> 'contribution_status', 'return' => 'id');
      $option_group_id = civicrm_api3('OptionGroup', 'Getvalue', $params);
      $option_values = civicrm_api3('OptionValue', 'Get', array('option_group_id' => $option_group_id));
    } catch (CiviCRM_API3_Exception $ex) {
        $this->_inactive_contribution_status = array();
        $this->_active_contribution_status = array();
    }
    foreach ($option_values['values'] as $option_value) {
      if (in_array($option_value['name'], $inactive_contribution_status)) {
        $this->_inactive_contribution_status[$option_value['value']] = $option_value['name'];
      } else {
        $this->_active_contribution_status[$option_value['value']] = $option_value['name'];
      }
    }
    $this->_all_contribution_status = array_merge($this->_active_contribution_status, 
      $this->_inactive_contribution_status);
  }
  /**
   * Function to set the financial_type_id for incoming name and create it
   * if it does not exist
   */
  protected function set_financial_type_id($financial_type_name) {
    $query_select = 'SELECT id FROM civicrm_financial_type WHERE name = %1';
    $params_select = array(1 => array($financial_type_name, 'String'));
    $dao_select = CRM_Core_DAO::executeQuery($query_select, $params_select);
    if ($dao_select->fetch()) {
      $this->_financial_type_ids[$financial_type_name] = $dao_select->id;
    } else {
      $query_add = 'INSERT INTO civicrm_financial_type (name, description, is_active, is_reserved) '
        . 'VALUES(%1, %1, %2, %2)';
      $params_add = array(
        1 => array($financial_type_name, 'String'),
        2 => array(1, 'Positive'));
      CRM_Core_DAO::executeQuery($query_add, $params_add);
      $query_select = 'SELECT id FROM civicrm_financial_type WHERE name = %1';
      $params_select = array(1 => array($financial_type_name, 'String'));
      $dao_select = CRM_Core_DAO::executeQuery($query_select, $params_select);
      if ($dao_select->fetch()) {
      $this->_financial_type_ids[$financial_type_name] = $dao_select->id;
      }
    }
  }
  /**
   * Function to set the case types which will show the donation link form
   * 
   * @access protected
   */
  protected function set_donation_case_types() {
    $this->_donation_case_types = array(
      'Advice', 'Business', 'CTM', 'Grant', 'PDV', 'RemoteCoaching', 'Seminar', 'TravelCase'
    );
  }
}
