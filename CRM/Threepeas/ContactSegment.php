<?php
/**
 * Class to deal with ContactSegment related to project
 * (issue 3623 http://redmine.pum.nl/issues/3623)
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 15 November 2016
 */
class CRM_Threepeas_ContactSegment {

  private $_contactSegmentData = NULL;
  private $_contactSegmentOperation = NULL;

  /**
   * CRM_Threepeas_ContactSegment constructor.
   *
   * @param $op
   * @param $objectId
   * @param $objectRef
   * @throws Exception when relationship type API Getvalue error
   */
  function __construct($op = NULL, $objectRef = NULL) {
    $this->_contactSegmentOperation = $op;
    $this->_contactSegmentData = $objectRef;
  }

  /**
   * Method to process post hook for ContactSegment (update sector coordinator in project and civicrm_pum_case_reports
   *
   * @throws Exception when error from API
   * @return int
   * @access public
   * @static
   */
  public static function post($op, $objectRef) {
    $contactSegment = new CRM_Threepeas_ContactSegment($op, $objectRef);
    // if role sector coordinator or customer (new sc or changes sector)
    $validRoles = array("Sector Coordinator", "Customer");
    if (in_array($contactSegment->_contactSegmentData->role_value, $validRoles)) {
      // process based on operation
      switch ($contactSegment->_contactSegmentOperation) {
        case "create":
          $contactSegment->addToProject();
          break;
        case "delete":
          $contactSegment->removeFromProject();
          break;
        case "edit":
          $contactSegment->edit();
          break;
      }
    }
  }

  /**
   * Method to determine what to do when contactsegment has been edited
   * (add to project if is active is 1 and start date, remove if end date and is active = 0
   */
  private function edit() {
    if ($this->_contactSegmentData->is_active == 1) {
      $this->addToProject();
    } else {
      $this->removeFromProject();
    }
  }

  /**
   * Method to determine if start date of relationship is active today
   *
   * @return bool
   * @access private
   */
  private function isValidStartDate() {
    $startDate = new DateTime(date('Ymd', strtotime($this->_contactSegmentData->start_date)));
    $nowDate = new DateTime(date('Ymd'));
    if ($startDate <= $nowDate) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  /**
   * Method to determine if end date of relationship is active today
   *
   * @return bool
   * @access private
   */
  private function isValidEndDate() {
    $endDate = new DateTime(date('Ymd', strtotime($this->_contactSegmentData->end_date)));
    $nowDate = new DateTime(date('Ymd'));
    if ($endDate <= $nowDate) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  /**
   * Method to remove role from project
   *
   * @access private
   */
  private function removeFromProject() {
    if ($this->isValidEndDate()) {
      // get all projects for sector
      $projects = $this->getAllRelevantProjects();
      // if customer, get sector coordinator of sector
      foreach ($projects as $projectId) {
        $query = 'UPDATE civicrm_project SET sector_coordinator_id = NULL WHERE id = %1';
        $params = array(
          1 => array($projectId, 'Integer'));
        CRM_Core_DAO::executeQuery($query, $params);
      }
    }
  }
  /**
   * Method to add role to project
   *
   * @access private
   */
  private function addToProject() {
    if ($this->isValidStartDate()) {
      // get all projects for sector
      $projects = $this->getAllRelevantProjects();
      // if customer, get sector coordinator of sector
      if ($this->_contactSegmentData->role_value == 'Customer') {
        $sectorCoordinatorId = civicrm_api3('ContactSegment', 'getvalue', array(
          'segment_id' => $this->_contactSegmentData->segment_id,
          'role_value' => 'Sector Coordinator',
          'is_active' => 1,
          'return' => 'contact_id'
        ));
      } else {
        $sectorCoordinatorId = $this->_contactSegmentData->contact_id;
      }
      foreach ($projects as $projectId) {
        $query = 'UPDATE civicrm_project SET sector_coordinator_id = %1 WHERE id = %2';
        $params = array(
          1 => array($sectorCoordinatorId, 'Integer'),
          2 => array($projectId, 'Integer'));
        CRM_Core_DAO::executeQuery($query, $params);
      }
    }
  }

  /**
   * Method to get all projects for sector
   *
   * @return array
   * @access private
   */
  private function getAllRelevantProjects() {
    $result = array();
    // if sector coordinator change, get all projects for sector else only get customer projects
    if ($this->_contactSegmentData->role_value == 'Sector Coordinator') {
      $sql = "SELECT pp.id AS project_id FROM civicrm_contact_segment cs 
        JOIN civicrm_project pp ON cs.contact_id = pp.customer_id WHERE cs.segment_id = %1 AND cs.role_value = %2";
      $sqlParams =  array(
        1 => array($this->_contactSegmentData->segment_id, 'Integer'),
        2 => array('Customer', 'String'));
    } else {
      $sql = "SELECT id AS project_id FROM civicrm_project WHERE customer_id = %1";
      $sqlParams =  array(1 => array($this->_contactSegmentData->contact_id, 'Integer'));
    }
    $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
    while ($dao->fetch()) {
      $result[] = $dao->project_id;
    }
    return $result;
  }
}
