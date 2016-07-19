<?php
/**
 * Class to deal with Relationship related to project
 * (issue 3287 http://redmine.pum.nl/issues/3287)
 * and to projectintake
 * (issue 3498 http://redmine.pum.nl/issues/3498)
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 2 June 2016
 */
class CRM_Threepeas_Relationship {

  private $_validRelations = array();
  private $_relationshipData = NULL;
  private $_relationshipOperation = NULL;
  private $_relationshipId = NULL;
  private $_validCaseSubTypes = array();

  /**
   * CRM_Threepeas_Relationship constructor.
   *
   * @param $op
   * @param $objectId
   * @param $objectRef
   * @throws Exception when relationship type API Getvalue error
   */
  function __construct($op = NULL, $objectId = NULL, $objectRef = NULL) {
    $names = array(
      'Anamon' => 'anamon_id',
      'Country Coordinator is' => 'country_coordinator_id',
      'Project Officer for' => 'project_officer_id',
      'Sector Coordinator' => 'sector_coordinator_id');
    foreach ($names as $name => $column) {
      try {
        $relTypeId = civicrm_api3('RelationshipType', 'Getvalue', array('name_a_b' => $name, 'return' => 'id'));
        $this->_validRelations[$relTypeId] = array('name' => $name, 'column' => $column);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not find a single relationship type with the name '.$name.' in '.__METHOD__
          .', contact your system administrator. Error from API RelationshipType Getvalue: '.$ex->getMessage());
      }
    }
    $this->_relationshipOperation = $op;
    $this->_relationshipId = $objectId;
    $this->_relationshipData = $objectRef;
    $this->_validCaseSubTypes = array('Customer', 'Country');
  }

  /**
   * Method to process post hook for Relationship 
   * - update anamon_id, country_coordinator_id, project_officer_id sector_coordinator_id in 
   *   civicrm_pum_case_reports
   *
   * @throws Exception when error from API
   * @return int
   * @access public
   * @static
   */
  public static function post($op, $objectId, $objectRef) {
    // not if coming in from webform
    $requestValues = CRM_Utils_Request::exportValues();
    if (!isset($requestValues['form_id'])) {
      $relationShip = new CRM_Threepeas_Relationship($op, $objectId, $objectRef);
      // if valid relationship type
      if ($relationShip->isValidRelationshipType()) {
        // process based on operation
        switch ($relationShip->_relationshipOperation) {
          case "create":
            $relationShip->addToProject();
            $relationShip->addToPumCaseReport();
            break;
          case "delete":
            $relationShip->removeFromProject();
            $relationShip->removeFromPumCaseReport();
            break;
          case "edit":
            $relationShip->edit();
            break;
        }
      }
    }
  }

  /**
   * Method to determine what to do when relationship has been edited
   * (add to project if is active is 1 and start date, remove if end date and is active = 0
   */
  private function edit() {
    if ($this->_relationshipData->is_active == 1) {
      $this->addToProject();
      $this->addToPumCaseReport();
    } else {
      $this->removeFromProject();
      $this->removeFromPumCaseReport();
    }
  }

  /**
   * Method to remove role from project
   *
   * @access private
   */
  private function removeFromPumCaseReport() {
    // only if end date is valid and case_id is set
    if (isset($this->_relationshipData->case_id) && !empty($this->_relationshipData->case_id)) {
      if ($this->isValidEndDate()) {
        $query = 'UPDATE civicrm_pum_case_reports SET ' . $this->_validRelations[$this->_relationshipData->relationship_type_id]['column']
          . ' = NULL WHERE case_id = %1';
        $params = array(
          1 => array($this->_relationshipData->case_id, 'Integer'));
        CRM_Core_DAO::executeQuery($query, $params);
      }
    }
  }

  /**
   * Method to add role to pum case reports
   *
   * @access private
   */
  private function addToPumCaseReport() {
    // only if start date is valid and case_id is set
    if (isset($this->_relationshipData->case_id) && !empty($this->_relationshipData->case_id)) {
      if ($this->isValidStartDate()) {
        if ($this->pumCaseAlreadyExists()) {
          $query = 'UPDATE civicrm_pum_case_reports SET ' . $this->_validRelations[$this->_relationshipData->relationship_type_id]['column']
            . ' = %1 WHERE case_id = %2';
        } else {
          $query = 'INSERT INTO civicrm_pum_case_reports SET ' . $this->_validRelations[$this->_relationshipData->relationship_type_id]['column']
            . ' = %1 WHERE case_id = %2';
        }
        $params = array(
          1 => array($this->_relationshipData->contact_id_b, 'Integer'),
          2 => array($this->_relationshipData->case_id, 'Integer'));
        CRM_Core_DAO::executeQuery($query, $params);
      }
    }
  }

  /**
   * Method to check if pum case report record already exists
   * @return bool
   */
  private function pumCaseAlreadyExists() {
    $countQuery = "SELECT COUNT(*) FROM civicrm_pum_case_reports WHERE case_id = %1";
    $count = CRM_Core_DAO::singleValueQuery($countQuery, 
      array(1 => array($this->_relationshipData->case_id, 'Integer')));
    if ($count > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Method to determine if relationship should be processed based on type
   *
   * @return bool
   * @access private
   */
  private function isValidRelationshipType() {
    if (isset($this->_relationshipData->relationship_type_id)) {
      if (array_key_exists($this->_relationshipData->relationship_type_id, $this->_validRelations)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Method to determine if start date of relationship is active today
   *
   * @return bool
   * @access private
   */
  private function isValidStartDate() {
    $startDate = new DateTime(date('Ymd', strtotime($this->_relationshipData->start_date)));
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
    $endDate = new DateTime(date('Ymd', strtotime($this->_relationshipData->end_date)));
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
    if (!isset($this->_relationshipData->case_id) || empty($this->_relationshipData->case_id)) {
      // only if contact a is valid contact sub type (Country or Customer) and end date is valid
      if ($this->isValidCaseSubType() && $this->isValidEndDate()) {
        // get all projects for customer or country
        $projects = CRM_Threepeas_BAO_PumProject::getContactProjects($this->_relationshipData->contact_id_a);
        foreach ($projects as $project) {
          $query = 'UPDATE civicrm_project SET ' . $this->_validRelations[$this->_relationshipData->relationship_type_id]['column']
            . ' = NULL WHERE id = %1';
          $params = array(
            1 => array($project['id'], 'Integer'));
          CRM_Core_DAO::executeQuery($query, $params);
        }
      }
    }
  }
  /**
   * Method to add role to project
   *
   * @access private
   */
  private function addToProject() {
    if (!isset($this->_relationshipData->case_id) || empty($this->_relationshipData->case_id)) {
      // only if contact a is valid contact sub type (Country or Customer) and start date is valid
      if ($this->isValidCaseSubType() && $this->isValidStartDate()) {
        // get all projects for customer or country
        $projects = CRM_Threepeas_BAO_PumProject::getContactProjects($this->_relationshipData->contact_id_a);
        foreach ($projects as $project) {
          $query = 'UPDATE civicrm_project SET ' . $this->_validRelations[$this->_relationshipData->relationship_type_id]['column']
            . ' = %1 WHERE id = %2';
          $params = array(
            1 => array($this->_relationshipData->contact_id_b, 'Integer'),
            2 => array($project['id'], 'Integer'));
          CRM_Core_DAO::executeQuery($query, $params);
        }
      }
    }
  }
  /**
   * Method to determine if contact sub type is valid for processing relationship into project
   *
   * @return bool
   * @access private
   */
  private function isValidCaseSubType() {
    if (isset($this->_relationshipData->contact_id_a)) {
      try {
        $contactSubTypes = civicrm_api3('Contact', 'Getvalue',
          array('id' => $this->_relationshipData->contact_id_a, 'return' => 'contact_sub_type'));
        foreach ($contactSubTypes as $contactSubType) {
          if (in_array($contactSubType, $this->_validCaseSubTypes)) {
            return TRUE;
          }
        }
      } catch (CiviCRM_API3_Exception $ex) {}
    }
    return FALSE;
  }
}
