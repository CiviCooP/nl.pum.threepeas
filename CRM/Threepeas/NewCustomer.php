<?php
/**
 * Class to deal with New Customer related to projectintake
 * (issue 2993 http://redmine.pum.nl/issues/2993)
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 11 November 2015
 */
class CRM_Threepeas_NewCustomer {

  /**
   * Method to get the tag id for new customer
   *
   * @throws Exception when error from API
   * @return int
   * @access public
   * @static
   */
  public static function getNewCustomerTagId() {
    $tagParams = array(
      'name' => "New customer",
      'return' => "id"
    );
    try {
      return civicrm_api3('Tag', 'Getvalue', $tagParams);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception("Could not find a tag with the name New customer, error message from API Tag Getvalue: ".
        $ex->getMessage());
    }
  }

  /**
   * Method to remove the new customer tag for the case customer when a project intake comes in and
   * tag is still there
   *
   * @param $caseId
   */
  public static function removeTagOnFirstProjectintake($caseId) {
    if (!empty($caseId)) {
      $contactId = CRM_Threepeas_Utils::getCaseClientId($caseId);
      if (!empty($contactId)) {
        $tagParams = array(
          'contact_id' => $contactId,
          'tag_id' => self::getNewCustomerTagId()
        );
        $countNewCustomerTag = civicrm_api3('EntityTag', 'Getcount', $tagParams);
        if ($countNewCustomerTag > 0) {
          civicrm_api3('EntityTag', 'Delete', $tagParams);
        }
      }
    }

  }
}
