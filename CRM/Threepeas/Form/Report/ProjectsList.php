<?php
/**
 * Report to show all projects and related main activities for PUM
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 25 Nov 2014
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the AGPL-3.0
 */
class CRM_Threepeas_Form_Report_ProjectsList extends CRM_Report_Form {
  
  protected $_report_fields = NULL;
  protected $_relations = NULL;

  function __construct() {
    $this->configure_report();
    $this->build_columns();
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('List All Projects'));
    parent::preProcess();
  }

  function select() {
    $select = $this->_columnHeaders = array();
    $this->set_report_fields();
    foreach ($this->_report_fields as $key => $field) {
      $select[] = $field['alias'].'.'.$field['name'].' AS '.$key;
      if ($field['is_header'] == 1) {
        $this->_columnHeaders[$key]['title'] = $field['title'];
        $this->_columnHeaders[$key]['type'] = $field['type'];
      }
    }
    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }
    
  function from() {
    $this->_from = NULL;
    $this->_from = 'FROM civicrm_project project '
      . 'LEFT JOIN civicrm_programme programme ON project.programme_id = programme.id '
      . 'LEFT JOIN civicrm_contact contact ON project.projectmanager_id = contact.id '
      . 'LEFT JOIN civicrm_case_project caseproject ON project.id = caseproject.project_id '
      . 'LEFT JOIN civicrm_case civicase ON caseproject.case_id = civicase.id';
  }

  function postProcess() {

    $this->beginPostProcess();

    $sql = $this->buildQuery(TRUE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }
  
  function where() {
    switch ($this->_submitValues['is_active_value']) {
      case 1:
        $this->_where = 'WHERE is_active = 1';
        break;
      case 2:
        $this->_where = 'WHERE is_active = 0';
        break;
      default:
        $this->_where = NULL;
        break;
    }
  }
  
  function orderBy() {
    $this->_orderBy = 'ORDER BY project.start_date DESC, project.title';
  }
  
  protected function configure_report() {
    $this->_exposeContactID = FALSE;
    $this->_customGroupExtends = NULL;
    $this->_add2groupSupported = FALSE;
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    $this->_defaults['is_active'] = 0;
    $this->set_project_relations();
  }
  
  protected function set_project_relations() {
    $alias_count = 1;
    $this->_relations = array();
    $case_relation_config = CRM_Threepeas_CaseRelationConfig::singleton();
    $project_relations = $case_relation_config->get_case_type_relations('Projectintake');
    foreach ($project_relations as $label => $active) {
      if ($active == 1) {
        $this->_relations[] = array(
          'label' => $label,
          'title' => $this->set_relation_title($label));
      }
      $alias_count++;
    }
  }
  
  protected function set_relation_title($relation_label) {
    $title = '';
    if (!empty($relation_label)) {
      $parts = explode('-', $relation_label);
      foreach ($parts as $key => $value) {
        $parts[$key] = ucfirst($value);
      }
      $title = implode(' ', $parts);
    }
    return $title;
  }
  
  protected function build_columns() {
    $this->_columns = array(
      'civicrm_project' => array(
        'dao' => 'CRM_Threepeas_DAO_PumProject',
        'alias' => 'project',
        'filters' => array(
          'is_active' => array(
            'title' => ts('Enabled'),
            'type' => CRM_Utils_Type::T_INT, 
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array('All', 'Yes', 'No'),          
          ),
        ),
      ),
  );
  }
  
  function set_report_fields() {
    $this->_report_fields = array();
    
    $this->_report_fields['civicrm_project_id'] = array(
      'alias' => 'project',
      'name' => 'id',
      'type' => CRM_Utils_Type::T_INT,
      'is_header' => 0,
      'title' => ts('Project ID'));
    
    $this->_report_fields['civicrm_project_programme_id'] = array(
      'alias' => 'project',
      'name' => 'programme_id',
      'type' => CRM_Utils_Type::T_INT,
      'is_header' => 0,
      'title' => ts('Programme ID'));
    
    $this->_report_fields['civicrm_project_title'] = array(
      'alias' => 'project',
      'name' => 'title',
      'type' => CRM_Utils_Type::T_STRING,
      'is_header' => 1,
      'title' => ts('Title'));
    
    $this->_report_fields['civicrm_project_customer_id'] = array(
      'alias' => 'project',
      'name' => 'customer_id',
      'type' => CRM_Utils_Type::T_INT,
      'is_header' => 0,
      'title' => ts('Customer ID'));
    
    $this->_report_fields['civicrm_project_projectmanager_id'] = array(
      'alias' => 'project',
      'name' => 'projectmanager_id',
      'type' => CRM_Utils_Type::T_INT,
      'is_header' => 0,
      'title' => ts('Projectmanager ID'));
    
    $this->_report_fields['civicrm_project_country_id'] = array(
      'alias' => 'project',
      'name' => 'country_id',
      'type' => CRM_Utils_Type::T_INT,
      'is_header' => 0,
      'title' => ts('Country ID'));
    
    $this->_report_fields['civicrm_project_is_active'] = array(
      'alias' => 'project',
      'name' => 'is_active',
      'type' => CRM_Utils_Type::T_INT,
      'is_header' => 1,
      'title' => ts('Enabled'));
    
    $this->_report_fields['civicrm_project_start_date'] = array(
      'alias' => 'project',
      'name' => 'start_date',
      'type' => CRM_Utils_Type::T_DATE,
      'is_header' => 1,
      'title' => ts('Start Date'));
    
    $this->_report_fields['civicrm_project_end_date'] = array(
      'alias' => 'project',
      'name' => 'end_date',
      'type' => CRM_Utils_Type::T_DATE,
      'is_header' => 1,
      'title' => ts('End Date'));
    
    $this->_report_fields['civicrm_programme_title'] = array(
      'alias' => 'programme',
      'name' => 'title',
      'type' => CRM_Utils_Type::T_STRING,
      'is_header' => 1,
      'title' => ts('Parent Programme'));
    
    $this->_report_fields['civicrm_project_projectmanager_name'] = array(
      'alias' => 'contact',
      'name' => 'display_name',
      'type' => CRM_Utils_Type::T_STRING,
      'is_header' => 1,
      'title' => ts('Projectmanager'));
    
    $this->_report_fields['civicrm_case_id'] = array(
      'alias' => 'civicase',
      'name' => 'id',
      'type' => CRM_Utils_Type::T_INT,
      'is_header' => 0,
      'title' => ts('Case ID'));
    
    $this->_report_fields['civicrm_case_type_id'] = array(
      'alias' => 'civicase',
      'name' => 'case_type_id',
      'type' => CRM_Utils_Type::T_STRING,
      'is_header' => 1,
      'title' => ts('Main Activity'));
    
    $this->_report_fields['civicrm_case_type_id'] = array(
      'alias' => 'civicase',
      'name' => 'case_type_id',
      'type' => CRM_Utils_Type::T_STRING,
      'is_header' => 1,
      'title' => ts('Main Activity'));
        
    $this->_report_fields['civicrm_case_start_date'] = array(
      'alias' => 'civicase',
      'name' => 'start_date',
      'type' => CRM_Utils_Type::T_DATE,
      'is_header' => 1,
      'title' => ts('Start Date'));
        
    $this->_report_fields['civicrm_case_end_date'] = array(
      'alias' => 'civicase',
      'name' => 'end_date',
      'type' => CRM_Utils_Type::T_DATE,
      'is_header' => 1,
      'title' => ts('End Date'));
        
    $this->_report_fields['civicrm_case_status_id'] = array(
      'alias' => 'civicase',
      'name' => 'status_id',
      'type' => CRM_Utils_Type::T_INT,
      'is_header' => 1,
      'title' => ts('Status'));
  }
}
