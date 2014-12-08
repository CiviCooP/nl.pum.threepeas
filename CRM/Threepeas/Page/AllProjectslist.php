<?php
/**
 * Page AllProjectslist to list all projects (PUM)
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 25 Nov 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the AGPL-3.0
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_AllProjectslist extends CRM_Core_Page {
  protected $_country_type = NULL;
  protected $_customer_type = NULL;
  
  function run() {
    $this->set_page_configuration();
    $dao_projects = $this->get_dao_projects();
    $display_projects = array();
    while($dao_projects->fetch()) {
      $display_projects[] = $this->build_row($dao_projects);
    }
    $this->assign('pumProjects', $display_projects);
    parent::run();
  }
  /**
   * Fucntion to build the display row
   * 
   * @param obj $dao
   * @return array
   * @access protected
   */
  protected function build_row($dao) {
    $display_row = array();
    $display_row['id'] = $dao->id;
    $display_row['title'] = $this->get_project_title($dao->title);
    $this->process_project_type($dao->customer_id, $dao->country_id, $display_row);
    $display_row['projectmanager_name'] = $dao->projectmanager_name;
    $display_row['is_active'] = $this->set_is_active($dao->is_active);
    $display_row['start_date'] = $this->set_project_date($dao->start_date);
    $display_row['end_date'] = $this->set_project_date($dao->end_date);
    $display_row['actions'] = $this->set_row_actions($dao);
    return $display_row;
  }
  /**
   * Function to set contact id and name based on type of project
   * 
   * @param int $customer_id
   * @param int $country_id
   * @param array $display_row
   * @access protected
   */
  protected function process_project_type($customer_id, $country_id, &$display_row) {
    if (!empty($country_id)) {
      $display_row['contact_id'] = $country_id;
      $display_row['country_name'] = $this->get_contact_name($country_id);
    } else {
      $display_row['contact_id'] = $customer_id;
      $display_row['customer_name'] = $this->get_contact_name($customer_id);
    }
  }
  /**
   * Function to make sure we have empty string when date is empty
   * 
   * @param string $date
   * @return string
   * @access protected
   */
  protected function set_project_date($date) {
    if (empty($date)) {
      return '';
    } else {
      return $date;
    }
  }
  /**
   * Function to set the value for is_active
   * 
   * @param type $is_active
   * @return type
   * @access protected
   */
  protected function set_is_active($is_active) {
    if ($is_active == 1) {
      return ts('Yes');
    } else {
      return ts('No');
    }
  }    
  /**
   * Function to set the project title to display
   * @param string $title
   * @return string
   * @access protected
   */
  protected function get_project_title($title) {
    if (!empty($title)) {
      return $title;
    } else {
      return '';
    }
  }
  /**
   * Function to set urls for row
   * 
   * @param int $project_id
   * @return array $urls
   * @access protected
   */
  protected function set_row_urls($project_id) {
    $urls = array();
    $urls['edit'] = CRM_Utils_System::url('civicrm/pumproject', "action=update&pid=".$project_id, true);
    $urls['view'] = CRM_Utils_System::url('civicrm/pumproject', "action=view&pid=".$project_id, true);
    $urls['drill'] = CRM_Utils_System::url('civicrm/pumdrill', "pumEntity=project&pid=".$project_id, true);
    $urls['disable'] = CRM_Utils_System::url('civicrm/pumproject', "action=disable&pid=".$project_id, true);
    $urls['enable'] = CRM_Utils_System::url('civicrm/pumproject', "action=enable&pid=".$project_id, true);
    $urls['del'] = CRM_Utils_System::url('civicrm/pumproject', "action=delete&pid=".$project_id, true);
    return $urls;
  }
  /**
   * Function to set actions for row
   * 
   * @param obj $dao
   * @return array
   * @access protected
   */
  protected function set_row_actions($dao) {
    $urls = $this->set_row_urls($dao->id);
    $page_actions = array();
    $page_actions[] = '<a class="action-item" title="View project details" href="'.$urls['view'].'">View</a>';
    if (CRM_Core_Permission::check('edit all contacts') || CRM_Core_Permission::check('administer CiviCRM')) {
      $page_actions[] = '<a class="action-item" title="Edit project" href="'.$urls['edit'].'">Edit</a>';
      if ($dao->is_active == 1) {
        $page_actions[] = '<a class="action-item" title="Disable project" href="'.$urls['disable'].'">Disable</a>';
      } else {
        $page_actions[] = '<a class="action-item" title="Enable project" href="'.$urls['enable'].'">Enable</a>';                
      }
      if (CRM_Threepeas_BAO_PumProject::checkCanBeDeleted($dao->id)) {
        $page_actions[] = '<a class="action-item" title="Delete project" href="'.$urls['del'].'">Delete</a>';
      }
    }
    $page_actions[] = '<a class="action-item" title="Drill down project" href="'.$urls['drill'].'">Drill Down</a>';
    return array_merge($page_actions, $this->get_hook_actions(array('id' => $dao->id, 'title' => $dao->title)));
  }
  /**
   * Function to set the page configuration initially
   * 
   * @access protected
   */
  protected function set_page_configuration() {
    CRM_Utils_System::setTitle(ts('List of All Projects'));    
    $threepeas_config = CRM_Threepeas_Config::singleton();
    $this->_country_type = $threepeas_config->countryContactType;
    $this->_customer_type = $threepeas_config->customerContactType;
    $session = CRM_Core_Session::singleton();
    $url = CRM_Utils_System::url('civicrm/allprojectslist');
    $session->pushUserContext($url);
  }
  /**
   * Function to get projects with as much data as possible
   * 
   * @return object DAO
   * @access protected
   */
  protected function get_dao_projects() {
    $query = 'SELECT a.*, b.title AS programme_title, c.display_name AS projectmanager_name
      FROM civicrm_project a
      LEFT JOIN civicrm_programme b ON a.programme_id = b.id
      LEFT JOIN civicrm_contact c ON a.projectmanager_id = c.id
      ORDER BY a.start_date DESC';
    return CRM_Core_DAO::executeQuery($query);
  }
  /**
   * Returns an array with extra links (filled from a hook)
   * 
   * @param array $project
   * @return array with links e.g. array('<a class="action-item" title="my item" href="link.php">link</a>')
   * 
   */
  protected function get_hook_actions($project) {
    $hooks = CRM_Utils_Hook::singleton();
    $return = $hooks->invoke(1, $project, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, 'civicrm_threepeas_projectactions');
    if (is_array($return)) {
      return $return;
    }
    return array();
  }
  /**
   * Function to get display name of a contact
   * 
   * @param int $contact_id
   * @return string $name
   * @access protected
   */
  protected function get_contact_name($contact_id) {
    $name = '';
    if (!empty($contact_id)) {
      $query = 'SELECT display_name FROM civicrm_contact WHERE id = %1';
      $params = array(1 => array($contact_id, 'Positive'));
      $dao = CRM_Core_DAO::executeQuery($query, $params);
      if ($dao->fetch()) {
        $name = $dao->display_name;
      }
    } 
    return $name;
  }
}
