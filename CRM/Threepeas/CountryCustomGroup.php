<?php
/**
 * Class for custom group for country
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 19 Jan 2015
 */
class CRM_Threepeas_CountryCustomGroup {
  
  static private $_singleton = NULL;
  
  protected $_country_custom_group_id = NULL;
  protected $_country_custom_group_table = NULL;
  protected $_country_custom_field_id = NULL;
  protected $_country_custom_field_column_name = NULL;
  protected $_country_custom_group_name = NULL;
  protected $_country_custom_field_name = NULL;
  
  /**
   * Function to return singleton object
   * 
   * @return object $_singleton
   * @access public
   * @static
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Threepeas_CountryCustomGroup();
    }
    return self::$_singleton;
  }
  /**
   * Constructor function
   */
  function __construct() {
    $this->_country_custom_group_name = 'pumCountry';
    $this->_country_custom_field_name = 'civicrm_country_id';
    $this->set_custom_group_data();
  }
  /**
   * Function to get the _country_custom_group_id
   * 
   * @return int
   * @access public
   */
  public function get_country_custom_group_id() {
    return $this->_country_custom_group_id;
  }
  /**
   * Function to get the _country_custom_group_table
   * 
   * @return string
   * @access public
   */
  public function get_country_custom_group_table() {
    return $this->_country_custom_group_table;
  }
  /**
   * Function to get the _country_custom_field_id
   * 
   * @return iny
   * @access public
   */
  public function get_country_custom_field_id() {
    return $this->_country_custom_field_id;
  }
  /**
   * Function to get the _country_custom_field_column_name
   * 
   * @return string
   * @access public
   */
  public function get_country_custom_field_column_name() {
    return $this->_country_custom_field_column_name;
  }
  /**
   * Function to create the custom group for country
   * 
   * @access public
   * @throws Exception when API CustomGroup Create fails
   */
  public function create_country_custom_group() {
    if ($this->check_country_custom_group_exists() == FALSE) {
      $params = $this->set_country_custom_group_params();
      try {
        $custom_group = civicrm_api3('CustomGroup', 'Create', $params);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not create PUM specific custom group with name '
          .$this->_country_custom_group_name.', error from API CustomGroup Create: '
          .$ex->getMessage());
      }
      $this->_country_custom_group_id = $custom_group['id'];
      $this->_country_custom_group_table = $custom_group['values']
        [$this->_country_custom_group_id]['table_name'];
      $this->create_country_custom_field();
    }
  }
  /**
   * Function to check if custom group with country name already exists
   * 
   * @return boolean
   * @access protected
   */
  protected function check_country_custom_group_exists() {
    $params = array('name' => $this->_country_custom_group_name);
    try {
      $custom_group_count = civicrm_api3('CustomGroup', 'Getcount', $params);
      if ($custom_group_count > 0) {
        return TRUE;
      }
    } catch (CiviCRM_API3_Exception $ex) {
    }
    return FALSE;
  }
  /**
   * Function to create custom field in custom group
   * 
   * @access protected 
   * @throws Exception when custom field create API throws error
   */
  protected function create_country_custom_field() {
    if ($this->check_country_custom_field_exists() == FALSE) {
      $params = $this->set_country_custom_field_params();
      try {
        $custom_field = civicrm_api3('CustomField', 'Create', $params);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not create PUM specific custom field '
          .$this->_country_custom_field_name.' in custom group '
          .$this->_country_custom_group_name.', error from API CustomField Create: '.
          $ex->getMessage());
      }
      $this->_country_custom_field_id = $custom_field['id'];
      $this->_country_custom_field_column_name = $custom_field['values']
        [$this->_country_custom_field_id]['column_name'];
    }
  }
  /**
   * Function to check if custom group with country name already exists
   * 
   * @return boolean
   * @access protected
   */
  protected function check_country_custom_field_exists() {
    $params = array(
      'name' => $this->_country_custom_field_name,
      'custom_group_id' => $this->_country_custom_group_id);
    try {
      $custom_field_count = civicrm_api3('CustomField', 'Getcount', $params);
      if ($custom_field_count > 0) {
        return TRUE;
      }
    } catch (CiviCRM_API3_Exception $ex) {
    }
    return FALSE;
  }
  /**
   * Function to set parameter list for custom field create
   * 
   * @return array $params
   * @access protected
   */
  protected function set_country_custom_field_params() {
    $params = array(
      'name' => $this->_country_custom_field_name,
      'label' => 'CountryID',
      'data_type' => 'String',
      'html_type' => 'Text',
      'is_required' => 0,
      'is_searchable' => 0,
      'weight' =>1,
      'is_active' => 1,
      'is_view' => 1,
      'column_name' => $this->_country_custom_field_name,
      'custom_group_id' => $this->_country_custom_group_id
    );
    return $params;
  }
  /**
   * Function to set parameter list for custom group create
   * 
   * @return array $params
   * @access protected
   */
  protected function set_country_custom_group_params() {
    $params = array(
      'name' => $this->_country_custom_group_name,
      'title' => 'Country data',
      'extends' => 'Organization',
      'extends_entity_column_value_option_group' => 'contact_type',
      'extends_entity_column_value' => 'Country',
      'style' => 'Inline',
      'collapse_display' => 0,
      'weight' => 15,
      'is_active' => 1,
      'table_name' => 'civicrm_value_country',
      'is_multiple' => 0,
      'is_reserved' => 0,
      'collapse_adv_display' => 0);
    return $params;
  }
  /**
   * Function to set the custom group data if it exists
   * 
   * @access protected
   */
  protected function set_custom_group_data() {
    $group_params = array('name' => $this->_country_custom_group_name);
    try {
      $custom_group = civicrm_api3('CustomGroup', 'Getsingle', $group_params);
      $this->_country_custom_group_id = $custom_group['id'];
      $this->_country_custom_group_table = $custom_group['table_name'];
     $field_params = array(
       'name' => $this->_country_custom_field_name,
       'custom_group_id' => $this->_country_custom_group_id);
     try {
       $custom_field = civicrm_api3('CustomField', 'Getsingle', $field_params);
       $this->_country_custom_field_id = $custom_field['id'];
       $this->_country_custom_field_column_name = $custom_field['column_name'];
     } catch (CiviCRM_API3_Exception $ex) {
       $this->_country_custom_field_id = null;
       $this->_country_custom_field_column_name = null;
     }
    } catch (CiviCRM_API3_Exception $ex) {
      $this->_country_custom_group_id = null;
      $this->_country_custom_group_table = null;
    }
  }
}
