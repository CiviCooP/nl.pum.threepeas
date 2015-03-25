<?php
/**
 * Class with general static util functions for threepeas
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-V3.0
 */

class CRM_Threepeas_Utils {

  /**
   * Function to check if contact is country
   *
   * @param int $contactId
   * @return boolean
   * @access public
   * @static
   */
  public static function contactIsCountry($contactId) {
    if (empty($contactId)) {
      return FALSE;
    }
    try {
      $contactData = civicrm_api3('Contact', 'Getsingle', array('contact_id' => $contactId));
      if (isset($contactData['contact_sub_type']) && !empty($contactData['contact_sub_type'])) {
        $threepeasConfig = CRM_Threepeas_Config::singleton();
        foreach ($contactData['contact_sub_type'] as $contactSubType) {
          if ($contactSubType == $threepeasConfig->countryContactType) {
            return TRUE;
          }
        }
      } else {
        return FALSE;
      }
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Function to get custom group if exists
   *
   * @param string $customGroupName
   * @return array
   * @access public
   * @static
   */
  public static function getCustomGroup($customGroupName) {
    try {
      $customGroup = civicrm_api3('CustomGroup', 'Getsingle', array('name' => $customGroupName));
      return $customGroup;
    } catch (CiviCRM_API3_Exception $ex) {
      return array();
    }
  }

  /**
   * Function to get the option group id of activity type
   *
   * @return int $activityTypeOptionGroupId
   * @throws Exception when option group not found
   * @access public
   * @static
   */
  public static function getActivityTypeOptionGroupId() {
    $params = array(
      'name' => 'activity_type',
      'return' => 'id');
    try {
      $activityTypeOptionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $params);
      return $activityTypeOptionGroupId;
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a valid option group for name activity_type, error from
        API OptionGroup Getvalue: ' . $ex->getMessage());
    }
  }

  /**
   * Function to get activity type with name
   *
   * @param string $activityTypeName
   * @return array
   */
  public static function getActivityTypeWithName($activityTypeName) {
    $activityTypeOptionGroupId = self::getActivityTypeOptionGroupId();
    $params = array(
      'option_group_id' => $activityTypeOptionGroupId,
      'name' => $activityTypeName);
    try {
      $activityType = civicrm_api3('OptionValue', 'Getsingle', $params);
      return $activityType;
    } catch (CiviCRM_API3_Exception $ex) {
      return array();
    }
  }
  /**
   * Function to get the option group id of activity status
   *
   * @return int $activityStatusOptionGroupId
   * @throws Exception when option group not found
   * @access public
   * @static
   */
  public static function getActivityStatusOptionGroupId() {
    $params = array(
      'name' => 'activity_status',
      'return' => 'id');
    try {
      $activityStatusOptionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $params);
      return $activityStatusOptionGroupId;
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a valid option group for name activity_status, error from
        API OptionGroup Getvalue: ' . $ex->getMessage());
    }
  }

  /**
   * Function to get activity status with name
   *
   * @param string $activityStatusName
   * @return array
   */
  public static function getActivityStatusWithName($activityStatusName) {
    $activityStatusOptionGroupId = self::getActivityStatusOptionGroupId();
    $params = array(
      'option_group_id' => $activityStatusOptionGroupId,
      'name' => $activityStatusName);
    try {
      $activityStatus = civicrm_api3('OptionValue', 'Getsingle', $params);
      return $activityStatus;
    } catch (CiviCRM_API3_Exception $ex) {
      return array();
    }
  }

  /**
   * Function to get custom field if exists
   *
   * @param int $customGroupId
   * @param string $customFieldName
   * @return array
   * @access public
   * @static
   */
  public static function getCustomField($customGroupId, $customFieldName) {
    $params = array(
      'custom_group_id' => $customGroupId,
      'name' => $customFieldName
    );
    try {
      $customField = civicrm_api3('CustomField', 'Getsingle', $params);
      return $customField;
    } catch (CiviCRM_API3_Exception $ex) {
      return array();
    }
  }

  /**
   * Function to create activity type
   * @param string $activityTypeName
   * @param string $activityTypeLabel
   * @param int $componentId
   * @return array
   * @throws Exception when error from API create
   */
  public static function createActivityType($activityTypeName, $activityTypeLabel = null, $componentId = 0) {
    $activityTypeOptionGroupId = self::getActivityTypeOptionGroupId();
    if (empty($activityTypeLabel)) {
      $labelExplode = explode('_', $activityTypeName);
      foreach ($labelExplode as $key => $label) {
        $labelExplode[$key] = ucfirst($label);
      }
      $activityTypeLabel = implode(' ', $labelExplode);
    }
    $params = array(
      'option_group_id' => $activityTypeOptionGroupId,
      'name' => $activityTypeName,
      'label' => $activityTypeLabel,
      'component_id' => $componentId,
      'is_active' => 1);
    try {
      $activityType = civicrm_api3('OptionValue', 'Create', $params);
      return $activityType['values'];
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not create activity type with name '.$activityTypeName
        .', error from API OptionValue Create: '.$ex->getMessage());
    }
  }

  /**
   * Function to create activity status
   * @param string $activityStatusName
   * @param string $activityStatusLabel
   * @return array
   * @throws Exception when error from API create
   */
  public static function createActivityStatus($activityStatusName, $activityStatusLabel = null) {
    $activityStatusOptionGroupId = self::getActivityStatusOptionGroupId();
    if (empty($activityStatusLabel)) {
      $labelExplode = explode('_', $activityStatusName);
      foreach ($labelExplode as $key => $label) {
        $labelExplode[$key] = ucfirst($label);
      }
      $activityStatusLabel = implode(' ', $labelExplode);
    }
    $params = array(
      'option_group_id' => $activityStatusOptionGroupId,
      'name' => $activityStatusName,
      'label' => $activityStatusLabel,
      'is_active' => 1);
    try {
      $activityStatus = civicrm_api3('OptionValue', 'Create', $params);
      return $activityStatus['values'];
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not create activity status with name '.$activityStatusName
        .', error from API OptionValue Create: '.$ex->getMessage());
    }
  }

  /**
   * Function to create custom group
   *
   * @param string $customGroupName
   * @param string $customGroupTable
   * @param string $extends
   * @param array $extendsEntityColumnValues
   * @param string $customGroupLabel
   * @return int $customGroup['id']
   * @throws Exception when error from API CustomGroup Create
   * @access public
   * @static
   */
  public static function createCustomGroup($customGroupName, $customGroupTable, $extends, $extendsEntityColumnValues = array(), $customGroupLabel = null) {
    if (empty($customGroupLabel)) {
      $labelExplode = explode('_', $customGroupName);
      foreach ($labelExplode as $key => $label) {
        $labelExplode[$key] = ucfirst($label);
      }
      $customGroupLabel = implode(' ', $labelExplode);
    }
    $params = array(
      'name' => $customGroupName,
      'title' => $customGroupLabel,
      'extends' => $extends,
      'is_active' => 1,
      'is_reserved' => 1,
      'table_name' => $customGroupTable
    );
    if (!empty($extendsEntityColumnValues)) {
      $params['extends_entity_column_value'] = implode(CRM_Core_DAO::VALUE_SEPARATOR, $extendsEntityColumnValues);
    }
    try {
      $customGroup = civicrm_api3('CustomGroup', 'Create', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception(ts('Could not create custom group '.$customGroupName
          .', error from API CustomGroup Create: ').$ex->getMessage());
    }
    return $customGroup['id'];
  }

  /**
   * Fucntion to create custom field
   *
   * @param int $customGroupId
   * @param string $customFieldName
   * @param string $customFieldColumn
   * @param string $customFieldDataType
   * @param string $customFieldHtmlType
   * @param mixed $defaultValue
   * @param int $isView
   * @param string $customFieldLabel
   * @return int $customField['id']
   * @throws Exception when error from API CustomField Create
   * @access public
   * @static
   */
  public static function createCustomField($customGroupId, $customFieldName, $customFieldColumn, $customFieldDataType,
      $customFieldHtmlType, $defaultValue = null, $isView = 0, $customFieldLabel = null) {

    if (empty($customFieldLabel)) {
      $labelExplode = explode('_', $customFieldName);
      foreach ($labelExplode as $key => $label) {
        $labelExplode[$key] = ucfirst($label);
      }
      $customFieldLabel = implode(' ', $labelExplode);
    }
    $params = array(
      'custom_group_id' => $customGroupId,
      'name' => $customFieldName,
      'label' => $customFieldLabel,
      'data_type' => $customFieldDataType,
      'html_type' => $customFieldHtmlType,
      'column_name' => $customFieldColumn,
      'is_active' => 1,
      'is_reserved' => 1,
      'is_view' => $isView,
      'default_value' => $defaultValue,
    );
    try {
      $customField = civicrm_api3('CustomField', 'Create', $params);
    } catch (CiviCRM_API3_Explorer $ex) {
      throw new Exception(ts('Could not create custom field '.$customFieldName
          .' in custom group id '.$customGroupId.', error from API CustomField Create: ').$ex->getMessage());
    }
    return $customField['id'];
  }

  /**
   * Function to return the clientId of a case
   *
   * @param int $caseId
   * @return int $clientId
   * @access public
   * @static
   */
  public static function getCaseClientId($caseId) {
    $params = array(
      'id' => $caseId,
      'return' => 'client_id'
    );
    try {
      $caseClients = civicrm_api3('Case', 'Getvalue', $params);
      foreach ($caseClients as $caseClient) {
        $clientId = $caseClient;
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $clientId = 0;
    }
    return $clientId;
  }
  /**
   * Function to get contact name
   *
   * @param int $contactId
   * @return string $contactName
   * @access public
   * @static
   */
  public static function getContactName($contactId) {
    $params = array(
      'id' => $contactId,
      'return' => 'display_name');
    try {
      $contactName = civicrm_api3('Contact', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $contactName = '';
    }
    return $contactName;
  }
  /**
   * Function to set the value for is_active
   *
   * @param int $isActive
   * @return string
   * @access public
   * @static
   */
  public static function setIsActive($isActive) {
    if ($isActive == 1) {
      return ts('Yes');
    } else {
      return ts('No');
    }
  }
  /**
   * Function to make sure we have empty string when date is empty
   *
   * @param string $date
   * @return string
   * @access public
   * @static
   */
  public static function setProjectDate($date) {
    if (empty($date)) {
      return '';
    } else {
      return $date;
    }
  }
}