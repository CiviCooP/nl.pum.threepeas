<?php
/**
 * Class for custom group for country
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 19 Jan 2015
 */
class CRM_Threepeas_CountryCustomGroup {
  
  static private $_singleton = NULL;
  
  protected $countryCustomGroupId = NULL;
  protected $countryCustomGroupTable = NULL;
  protected $countryCustomFieldId = NULL;
  protected $countryCustomFieldColumnName = NULL;
  protected $countryCustomGroupName = NULL;
  protected $countryCustomFieldName = NULL;
  
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
    $this->countryCustomGroupName = 'pumCountry';
    $this->countryCustomFieldName = 'civicrm_country_id';
    $this->setCustomGroupData();
  }
  /**
   * Function to get the country custom group id
   * 
   * @return int
   * @access public
   */
  public function getCountryCustomGroupId() {
    return $this->countryCustomGroupId;
  }
  /**
   * Function to get the country custom group table
   * 
   * @return string
   * @access public
   */
  public function getCountryCustomGroupTable() {
    return $this->countryCustomGroupTable;
  }
  /**
   * Function to get the country custom field id
   * 
   * @return iny
   * @access public
   */
  public function getCountryCustomFieldId() {
    return $this->countryCustomFieldId;
  }
  /**
   * Function to get the country custom field column name
   * 
   * @return string
   * @access public
   */
  public function getCountryCustomFieldColumnName() {
    return $this->countryCustomFieldColumnName;
  }
  /**
   * Function to create the custom group for country
   * 
   * @access public
   * @throws Exception when API CustomGroup Create fails
   */
  public function createCountryCustomGroup() {
    if ($this->checkCountryCustomGroupExists() == FALSE) {
      $params = $this->setCountryCustomGroupParams();
      try {
        $customGroup = civicrm_api3('CustomGroup', 'Create', $params);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not create PUM specific custom group with name '
          .$this->countryCustomGroupName.', error from API CustomGroup Create: '
          .$ex->getMessage());
      }
      $this->countryCustomGroupId = $customGroup['id'];
      $this->countryCustomGroupTable = $customGroup['values']
        [$this->countryCustomGroupId]['table_name'];
      $this->createCountryCustomField();
    }
  }
  /**
   * Function to check if custom group with country name already exists
   * 
   * @return boolean
   * @access protected
   */
  protected function checkCountryCustomGroupExists() {
    $params = array('name' => $this->countryCustomGroupName);
    try {
      $customGroupCount = civicrm_api3('CustomGroup', 'Getcount', $params);
      if ($customGroupCount > 0) {
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
  protected function createCountryCustomField() {
    if ($this->checkCountryCustomFieldExists() == FALSE) {
      $params = $this->setCountryCustomFieldParams();
      try {
        $customField = civicrm_api3('CustomField', 'Create', $params);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not create PUM specific custom field '
          .$this->countryCustomFieldName.' in custom group '
          .$this->countryCustomGroupName.', error from API CustomField Create: '.
          $ex->getMessage());
      }
      $this->countryCustomFieldId = $customField['id'];
      $this->countryCustomFieldColumnName = $customField['values']
        [$this->countryCustomFieldId]['column_name'];
    }
  }
  /**
   * Function to check if custom group with country name already exists
   * 
   * @return boolean
   * @access protected
   */
  protected function checkCountryCustomFieldExists() {
    $params = array(
      'name' => $this->countryCustomFieldName,
      'custom_group_id' => $this->countryCustomGroupId);
    try {
      $customFieldCount = civicrm_api3('CustomField', 'Getcount', $params);
      if ($customFieldCount > 0) {
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
  protected function setCountryCustomFieldParams() {
    $params = array(
      'name' => $this->countryCustomFieldName,
      'label' => 'CountryID',
      'data_type' => 'String',
      'html_type' => 'Text',
      'is_required' => 0,
      'is_searchable' => 0,
      'weight' =>1,
      'is_active' => 1,
      'is_view' => 1,
      'column_name' => $this->countryCustomFieldName,
      'custom_group_id' => $this->countryCustomGroupId
    );
    return $params;
  }
  /**
   * Function to set parameter list for custom group create
   * 
   * @return array $params
   * @access protected
   */
  protected function setCountryCustomGroupParams() {
    $params = array(
      'name' => $this->countryCustomGroupName,
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
  protected function setCustomGroupData() {
    $groupParams = array('name' => $this->countryCustomGroupName);
    try {
      $customGroup = civicrm_api3('CustomGroup', 'Getsingle', $groupParams);
      $this->countryCustomGroupId = $customGroup['id'];
      $this->countryCustomGroupTable = $customGroup['table_name'];
     $fieldParams = array(
       'name' => $this->countryCustomFieldName,
       'custom_group_id' => $this->countryCustomGroupId);
     try {
       $customField = civicrm_api3('CustomField', 'Getsingle', $fieldParams);
       $this->countryCustomFieldId = $customField['id'];
       $this->countryCustomFieldColumnName = $customField['column_name'];
     } catch (CiviCRM_API3_Exception $ex) {
       $this->countryCustomFieldId = null;
       $this->countryCustomFieldColumnName = null;
     }
    } catch (CiviCRM_API3_Exception $ex) {
      $this->countryCustomGroupId = null;
      $this->countryCustomGroupTable = null;
    }
  }
}
