<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM specific report for PUM <www.pum.nl>                       |
 | part of extension nl.pum.threepeas                                 |
 |                                                                    |
 | @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>          |
 | @date 1 Dec 2014                                                   |
 | Shows all projects with attached relations and cases               |
 +--------------------------------------------------------------------+
 |                                                                    |
 | Copyright (C) 2014 Coöperatieve CiviCooP U.A.                      |
 | <http://www.civicoop.org>                                          |
 | Licensed to PUM <http://www.pum.nl> and CiviCRM under the          |
 | AGPL-3.0                                                           |
 +--------------------------------------------------------------------+
 */
class CRM_Threepeas_Form_Report_AllProjectsReport extends CRM_Report_Form {
  
  protected $_projectintake_relations = array();
  protected $_capassessment_relations = array();
  protected $_column_relations = array();
  
  function __construct() {
    $this->configure_report();
    
    $this->set_columns();
    parent::__construct();
  }
  /**
   * This function overrides the parent function preProcess
   */
  function preProcess() {
    $this->assign('reportTitle', ts('Report with All PUM Projects'));
    parent::preProcess();
  }
  /**
   * This function overrides the parent function select
   */
  function select() {
    $this->_select = 'SELECT 
      proj.id AS civicrm_project_id, proj.title AS civicrm_project_title,
      proj.is_active AS civicrm_project_is_active, 
      proj.projectmanager_id AS civicrm_project_projectmanager_id, 
      proj.start_date AS civicrm_project_start_date, proj.end_date AS civicrm_project_end_date, 
      proj.customer_id AS civicrm_project_customer_id, proj.country_id AS civicrm_project_country_id, 
      cont.display_name AS civicrm_contact_projectmanager_name, prog.id AS civicrm_programme_id, 
      prog.title AS civicrm_programme_title, civicase.id AS civicrm_case_id, 
      civicase.case_type_id AS civicrm_case_type_id, civicase.start_date AS civicrm_case_start_date, 
      civicase.end_date AS civicrm_case_end_date, civicase.status_id AS civicrm_case_status_id';
  }
  /**
   * This function overrides the parent function from
   */
  function from() {
    $this->_from = ' FROM civicrm_project proj 
      LEFT JOIN civicrm_programme prog ON proj.programme_id = prog.id 
      LEFT JOIN civicrm_contact cont ON proj.projectmanager_id = cont.id 
      LEFT JOIN civicrm_case_project case_proj ON proj.id = case_proj.project_id 
      LEFT JOIN civicrm_case civicase ON case_proj.case_id = civicase.id';
  }
  /**
   * This function overrides the parent function where
   */
  function where() {
    $this->_where = '';
  }
  /**
   * This function overrides the parent function orderBy
   */
  function orderBy() {
    $this->_orderBy = ' ORDER BY proj.start_date DESC, proj.title';
  }
  /**
   * This function overrides the parent function postProcess
   */
  function postProcess() {

    $this->beginPostProcess();
    $sql = $this->buildQuery(TRUE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }
  /**
   * This function overrides the parent function buildRows
   * 
   * @param string $sql
   * @param array $rows
   */
  function buildRows($sql, &$rows) {
    $dao = CRM_Core_DAO::executeQuery($sql);
    if (!is_array($rows)) {
      $rows = array();
    }
    $this->modifyColumnHeaders();
    while ($dao->fetch()) {
      $row = array();
      $this->set_hidden_columns($dao, $row);
      foreach ($this->_columnHeaders as $key => $value) {
        if (property_exists($dao, $key)) {
          $row[$key] = $dao->$key;
        } else {
          $row[$key] = null;
        }
      }
      $rows[] = $row;
    }
  }
  /**
   * This method overrides the (empty) parent function modifyColumnHeaders
   */
  function modifyColumnHeaders() {
    $this->_columnHeaders['civicrm_project_title'] = array('title' => ts('Project title'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['civicrm_project_customer_name'] = array('title' => ts('Customer/Country'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['civicrm_project_start_date'] = array('title' => ts('Start Date'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['civicrm_project_end_date'] = array('title' => ts('End Date'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['civicrm_contact_projectmanager_name'] = array('title' => ts('Projectmanager'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['civicrm_project_is_active'] = array('title' => ts('Enabled?'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['civicrm_programme_title'] = array('title' => ts('Parent Programme'), 'type' => CRM_Utils_Type::T_STRING);
    $this->add_relation_columnheaders();
    $this->_columnHeaders['civicrm_case_type'] = array('title' => ts('Main Activity'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['civicrm_case_expert'] = array('title' => ts('Expert'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['civicrm_case_start_date'] = array('title' => ts('Start Date'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['civicrm_case_end_date'] = array('title' => ts('End Date'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['civicrm_case_status'] = array('title' => ts('Status'), 'type' => CRM_Utils_Type::T_STRING);
  }
  /**
   * This function overrides the parent function alterDisplay
   * 
   * @param array $rows
   */
  function alterDisplay(&$rows) {
    foreach ($rows as $row_num => $row) {
      $this->alter_relation_columns($row);
      $this->alter_projectmanager_column($row);
      $this->alter_case_type_column($row);
      $this->alter_case_status_column($row);
      $this->alter_project_title_column($row);
      $this->alter_programme_title_column($row);
      $rows[$row_num] = $row;
    }
  }
  /**
   * Function to alter the columns for relations
   * 
   * @param array $row
   * @access protected
   */
  protected function alter_relation_columns(&$row) {
    $client_id = $this->get_row_client_id($row);
    foreach ($this->_column_relations as $relation_label) {
      $relation_id = CRM_Threepeas_BAO_PumCaseRelation::get_relation_id($client_id, $relation_label);
      if (!empty($relation_id)) {
        $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$relation_id);
        $row[$relation_label] = $this->get_contact_name($relation_id);
        $row[$relation_label.'_link'] = $url;
        $row[$relation_label.'_hover'] = 'Click to view contact details';
      } else {
        $row[$relation_label] = '';
      }
    }
  }
  /**
   * Function to get contact name
   * 
   * @param int $contact_id
   * @return string $contact_name
   * @access protected
   */
  protected function get_contact_name($contact_id) {
    $params = array(
      'id' => $contact_id,
      'return' => 'display_name');
    try {
      $contact_name = civicrm_api3('Contact', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $contact_name = '';
    }
    return $contact_name;
  }
  /**
   * Function to set the link for the projectmanager column
   * 
   * @param array $row
   * @access protected
   */
  protected function alter_projectmanager_column(&$row) {
    if (isset($row['civicrm_contact_projectmanager_name']) && !empty($row['civicrm_contact_projectmanager_name'])) {
      $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$row['civicrm_project_projectmanager_id']);
      $row['civicrm_contact_projectmanager_name_link'] = $url;
      $row['civicrm_contact_projectmanager_name_hover'] = 'Click to view projectmanager details';
    }
  }
  /**
   * Function to alter the case status column
   * 
   * @param array $row
   * @access protected
   */
  protected function alter_case_type_column(&$row) {
    if (isset($row['civicrm_case_type_id']) && !empty($row['civicrm_case_type_id'])) {
      $threepeas_config = CRM_Threepeas_Config::singleton();
      $case_type_parts = explode(CRM_Core_DAO::VALUE_SEPARATOR, $row['civicrm_case_type_id']);
      if (isset($case_type_parts[1])) {
        $case_type_id = $case_type_parts[1];
      } else {
        $case_type_id = 0;
      }
      if (!empty($case_type_id)) {
        $row['civicrm_case_type'] = $threepeas_config->caseTypes[$case_type_id];
        $this->alter_case_url($row);
      }
    }
  }
  /**
   * Function to get either customer or country
   * 
   * @param array $row
   * @return int $client_id
   * @access protected
   */
  protected function get_row_client_id($row) {
    $client_id = 0;
    if (isset($row['civicrm_project_customer_id']) && !empty($row['civicrm_project_customer_id'])) {
      $client_id = $row['civicrm_project_customer_id'];
    } else {
      $client_id = $row['civicrm_project_country_id'];
    }
    return $client_id;
  }
  /**
   * Function to create case url's if required
   * 
   * @param array $row
   * @access protected
   */
  protected function alter_case_url(&$row) {
    $client_id = $this->get_row_client_id($row);
    if (!empty($client_id)) {
      $url_params = 'reset=1&action=view&id='.$row['civicrm_case_id'].'&cid='.
        $row['civicrm_project_customer_id'];
      $url = CRM_Utils_System::url('civicrm/contact/view/case', $url_params);
      $row['civicrm_case_type_link'] = $url;
      $row['civicrm_case_type_hover'] = 'Click to manage main activty';
    }
  }
  /**
   * Function to set case status label in column
   * 
   * @param array $row
   * @access protected
   */
  protected function alter_case_status_column(&$row) {
    if (isset($row['civicrm_case_status_id']) && !empty($row['civicrm_case_status_id'])) {
      $threepeas_config = CRM_Threepeas_Config::singleton();
      $row['civicrm_case_status'] = $threepeas_config->caseStatus[$row['civicrm_case_status_id']];
    }
  }
  /**
   * Function to create link to project view for project title
   * 
   * @param array $row
   * @access protected
   */
  protected function alter_project_title_column(&$row) {
    if (isset($row['civicrm_project_title']) && !empty($row['civicrm_project_title'])) {
      $url = CRM_Utils_System::url('civicrm/pumproject', 'action=view&pid='.$row['civicrm_project_id']);
      $row['civicrm_project_title_link'] = $url;
      $row['civicrm_project_title_hover'] = 'Click to view programme details';
    }
  }
  /**
   * Function to create link to programme view for programme title
   * 
   * @param array $row
   * @access protected
   */
  protected function alter_programme_title_column(&$row) {
    if (isset($row['civicrm_programme_title']) && !empty($row['civicrm_programme_title'])) {
      $url = CRM_Utils_System::url('civicrm/pumprogramme', 'action=view&pid='.$row['civicrm_programme_id']);
      $row['civicrm_programme_title_link'] = $url;
      $row['civicrm_programme_title_hover'] = 'Click to view programme details';
    }
  }
  /**
   * Function to add columnheaders for relations
   * (Initially only project intake and CAPAssessment)
   * 
   * @access protected
   */
  protected function add_relation_columnheaders() {
    foreach ($this->_capassessment_relations as $label => $active) {
      if ($active == 1) {
        $this->_column_relations[] = $label;
        $title = $this->create_relation_label_title($label);
        $this->_columnHeaders[$label] = array('title' => ts($title), 'type' => CRM_Utils_Type::T_STRING);
      }
    }
    foreach ($this->_projectintake_relations as $label => $active) {
      if ($active == 1 && !in_array($label, $this->_columnHeaders)) {
        $this->_column_relations[] = $label;
        $title = $this->create_relation_label_title($label);
        $this->_columnHeaders[$label] = array('title' => ts($title), 'type' => CRM_Utils_Type::T_STRING);
      }      
    }
  }
  /**
   * Function to create column title from relation label
   * 
   * @param string $relation_label
   * @return string $label_title
   * @access protected
   */
  protected function create_relation_label_title($relation_label) {
    $label_parts = explode('_', $relation_label);
    $label_title = ucfirst($label_parts[0]);
    if (isset($label_parts[1])) {
      $label_title .= ' '.ucfirst($label_parts[1]);
    }
    return $label_title;
  }
  /**
   * Function report configuration
   * 
   * @access protected
   */
  protected function configure_report() {
    $this->_tagFilter = FALSE;
    $this->_groupFilter = FALSE;
    $this->_exposeContactID = FALSE;
    $this->__groupButtonName = NULL;
    $this->_add2groupSupported = FALSE;
    $case_relation_config = CRM_Threepeas_CaseRelationConfig::singleton();
    $this->_capassessment_relations = $case_relation_config->get_case_type_relations('CAPAssessment');
    $this->_projectintake_relations = $case_relation_config->get_case_type_relations('Projectintake');
  }
  /**
   * Function to set columns
   * 
   * @access protected
   */
  protected function set_columns() {
    $this->_columns = array();
  }
  protected function set_hidden_columns($dao, &$row) {
      $row['civicrm_project_id'] = $dao->civicrm_project_id;
      $row['civicrm_programme_id'] = $dao->civicrm_programme_id;
      $row['civicrm_project_customer_id'] = $dao->civicrm_project_customer_id;
      $row['civicrm_project_country_id'] = $dao->civicrm_project_country_id;
      $row['civicrm_project_projectmanager_id'] = $dao->civicrm_project_projectmanager_id;
      $row['civicrm_case_id'] = $dao->civicrm_case_id;
      $row['civicrm_case_type_id'] = $dao->civicrm_case_type_id;    
      $row['civicrm_case_status_id'] = $dao->civicrm_case_status_id;
  }
}
