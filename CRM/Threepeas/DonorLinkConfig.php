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
  protected $defaultContributionId = NULL;
  protected $inactiveContributionStatus = array();
  protected $activeContributionStatus = array();
  protected $allContributionStatus = array();
  protected $grantDonationFinancialType = NULL;
  protected $financialTypeIds = array();
  protected $donationFinancialType = NULL;
  protected $grantCaseType = NULL;
  protected $donationCaseTypes = NULL;
  /*
   * properties for custom field donation applicable (issue 1566)
   */
  protected $applicableCustomFieldId = NULL;
  protected $applicableCustomGroupName = NULL;
  protected $applicableCustomGroupId = NULL;
  protected $applicableCustomFieldName = NULL;
  protected $applicableCustomGroupTable = NULL;
  protected $applicableCustomFieldColumn = NULL;
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
    $this->defaultContributionId = 4;
    $this->grantCaseType = 'Grant';
    $this->setContributionStatus();
    $this->grantDonationFinancialType = 'Grant Donation';
    $this->donationFinancialType = 'Donation';
    $this->setFinancialTypeId($this->grantDonationFinancialType);
    $this->setFinancialTypeId($this->donationFinancialType);
    $this->setDonationCaseTypes();
    $this->setApplicableCustomGroup();
  }

  /**
   * Function to get case types to exclude from donor link
   * 
   * @return array
   * @access public
   */
  public function getDonationCaseTypes() {
    return $this->donationCaseTypes;
  }

  /**
   * Function to get custom group name for contribution custom group
   *
   * @return string
   * @access public
   */
  public function getApplicableCustomGroupName() {
    return $this->applicableCustomGroupName;
  }

  /**
   * Function to get custom group id for contribution custom group
   *
   * @return int
   * @access public
   */
  public function getApplicableCustomGroupId() {
    return $this->applicableCustomGroupId;
  }

  /**
   * Function to get custom field name for applicable contribution
   *
   * @return string
   * @access public
   */
  public function getApplicableCustomFieldName() {
    return $this->applicableCustomFieldName;
  }

  /**
   * Function to get custom field id for applicable contribution
   * @return int
   * @access public
   */
  public function getApplicableCustomFieldId() {
    return $this->applicableCustomFieldId;
  }

  /**
   * Function to get custom group table name for contribution custom group
   *
   * @return string
   * @access public
   */
  public function getApplicableCustomGroupTable() {
    return $this->applicableCustomGroupTable;
  }

  /**
   * Function to get custom field column name for applicable contribution
   *
   * @return string
   * @access public
   */
  public function getApplicableCustomFieldColumn() {
    return $this->applicableCustomFieldColumn;
  }

  /**
   * Function to get grant case type
   * 
   * @return string
   * @access public
   */
  public function getGrantCaseType() {
    return $this->grantCaseType;
  }

  /**
   * Function to get default contribution_id
   * 
   * @return int
   * @access public
   */
  public function getDefaultContributionId() {
    return $this->defaultContributionId;
  }
  /**
   * Function to get inactive contribution statusses
   * 
   * @return array
   * @access public
   */
  public function getInactiveContributionStatus() {
    return $this->inactiveContributionStatus;
  }
  /**
   * Function to get active contribution statusses
   * 
   * @return array
   * @access public
   */
  public function getActiveContributionStatus() {
    return $this->activeContributionStatus;
  }
  /**
   * Function to get all contribution statusses
   * 
   * @return array
   * @access public
   */
  public function getAllContributionStatus() {
    return $this->allContributionStatus;
  }
  /**
   * Function to get the grant donation financial type
   * @return string
   * @access public
   */
  public function getGrantDonationFinancialType() {
    return $this->grantDonationFinancialType;
  }
  /**
   * Function to get the donation financial type
   * @return string
   * @access public
   */
  public function getDonationFinancialType() {
    return $this->donationFinancialType;
  }
  /**
   * Function to get the grant donation financial type id
   * @return int
   * @access public
   */
  public function getGrantDonationFinancialTypeId() {
    return $this->financialTypeIds[$this->grantDonationFinancialType];
  }

  /**
   * Function to get the donation financial type id
   * @return int
   * @access public
   */
  public function getDonationFinancialTypeId() {
    return $this->financialTypeIds[$this->donationFinancialType];
  }

  /**
   * Function to set contribution statusses
   * 
   * @access protected
   */
  protected function setContributionStatus() {
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
      if (in_array($optionValue['name'], $inactiveContributionStatus)) {
        $this->inactiveContributionStatus[$optionValue['value']] = $optionValue['name'];
      } else {
        $this->activeContributionStatus[$optionValue['value']] = $optionValue['name'];
      }
    }
    $this->allContributionStatus = array_merge($this->activeContributionStatus,
      $this->inactiveContributionStatus);
  }

  /**
   * Function to set the financial_type_id for incoming name and create it
   * if it does not exist
   *
   * @param string $financialTypeName
   */
  protected function setFinancialTypeId($financialTypeName) {
    $querySelect = 'SELECT id FROM civicrm_financial_type WHERE name = %1';
    $paramsSelect = array(1 => array($financialTypeName, 'String'));
    $daoSelect = CRM_Core_DAO::executeQuery($querySelect, $paramsSelect);
    if ($daoSelect->fetch()) {
      $this->financialTypeIds[$financialTypeName] = $daoSelect->id;
    } else {
      $queryAdd = 'INSERT INTO civicrm_financial_type (name, description, is_active, is_reserved) '
        . 'VALUES(%1, %1, %2, %2)';
      $paramsAdd = array(
        1 => array($financialTypeName, 'String'),
        2 => array(1, 'Positive'));
      CRM_Core_DAO::executeQuery($queryAdd, $paramsAdd);
      $querySelect = 'SELECT id FROM civicrm_financial_type WHERE name = %1';
      $paramsSelect = array(1 => array($financialTypeName, 'String'));
      $daoSelect = CRM_Core_DAO::executeQuery($querySelect, $paramsSelect);
      if ($daoSelect->fetch()) {
      $this->financialTypeIds[$financialTypeName] = $daoSelect->id;
      }
    }
  }

  /**
   * Function to set the case types which will show the donation link form
   * 
   * @access protected
   */
  protected function setDonationCaseTypes() {
    $this->donationCaseTypes = array(
      'Advice', 'Business', 'CTM', 'Grant', 'PDV', 'RemoteCoaching', 'Seminar', 'TravelCase'
    );
  }

  /**
   * Function to create custom group and custom field for donation applicable (issue 1566)
   *
   * @access protected
   */
  protected function setApplicableCustomGroup() {

    $this->applicableCustomGroupName = 'pum_donation_group';
    $customGroup = CRM_Threepeas_Utils::getCustomGroup($this->applicableCustomGroupName);
    if (empty($customGroup)) {
      $this->applicableCustomGroupTable = 'civicrm_value_pum_donation';
      $this->applicableCustomGroupId = CRM_Threepeas_Utils::createCustomGroup($this->applicableCustomGroupName,
        $this->applicableCustomGroupTable, 'Contribution');
    } else {
      $this->applicableCustomGroupId = $customGroup['id'];
      $this->applicableCustomGroupTable = $customGroup['table_name'];
    }

    $this->applicableCustomFieldName = 'pum_donation_applicable';
    $customField = CRM_Threepeas_Utils::getCustomField($this->applicableCustomGroupId, $this->applicableCustomFieldName);
    if (empty($customField)) {
      $this->applicableCustomFieldColumn = 'donation_applicable';
      $this->applicableCustomFieldId = CRM_Threepeas_Utils::createCustomField($this->applicableCustomGroupId,
        $this->applicableCustomFieldName, $this->applicableCustomFieldColumn, 'Boolean', 'Radio', 0);
    } else {
      $this->applicableCustomFieldId = $customField['id'];
      $this->applicableCustomFieldColumn = $customField['column_name'];
    }
  }
}
