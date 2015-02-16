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
  static function contactIsCountry($contactId) {
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
}