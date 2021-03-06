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
  protected $countryType = NULL;
  protected $customerType = NULL;
  
  function run() {
    $this->setPageConfiguration();
    $daoProjects = $this->getDaoProjects();
    $displayProjects = array();
    while($daoProjects->fetch()) {
      $displayProjects[] = $this->buildRow($daoProjects);
    }
    $this->assign('pumProjects', $displayProjects);
    parent::run();
  }
  /**
   * Fucntion to build the display row
   * 
   * @param object $dao
   * @return array
   * @access protected
   */
  protected function buildRow($dao) {
    $displayRow = array();
    $displayRow['id'] = $dao->id;
    $displayRow['title'] = $this->getProjectTitle($dao->title);
    $displayRow['programme_name'] = CRM_Threepeas_BAO_PumProgramme::getProgrammeTitleWithId($dao->programme_id);
    $this->processProjectType($dao->customer_id, $dao->country_id, $displayRow);
    $displayRow['projectmanager_name'] = $dao->projectmanager_name;
    $displayRow['is_active'] = CRM_Threepeas_Utils::setIsActive($dao->is_active);
    $displayRow['start_date'] = CRM_Threepeas_Utils::setProjectDate($dao->start_date);
    $displayRow['end_date'] = CRM_Threepeas_Utils::setProjectDate($dao->end_date);
    $displayRow['actions'] = $this->setRowActions($dao);
    return $displayRow;
  }
  /**
   * Function to set contact id and name based on type of project
   * 
   * @param int $customerId
   * @param int $countryId
   * @param array $displayRow
   * @access protected
   */
  protected function processProjectType($customerId, $countryId, &$displayRow) {
    if (!empty($countryId)) {
      $displayRow['contact_id'] = $countryId;
      $displayRow['country_name'] = CRM_Threepeas_Utils::getContactName($countryId);
    } else {
      $displayRow['contact_id'] = $customerId;
      $displayRow['customer_name'] = CRM_Threepeas_Utils::getContactName($customerId);
    }
  }
  /**
   * Function to set the project title to display
   * @param string $title
   * @return string
   * @access protected
   */
  protected function getProjectTitle($title) {
    if (!empty($title)) {
      return $title;
    } else {
      return '';
    }
  }
  /**
   * Function to set urls for row
   * 
   * @param int $projectId
   * @return array $urls
   * @access protected
   */
  protected function setRowUrls($projectId) {
    $urls = array();
    $urls['edit'] = CRM_Utils_System::url('civicrm/pumproject', "action=update&pid=".$projectId, true);
    $urls['view'] = CRM_Utils_System::url('civicrm/pumproject', "action=view&pid=".$projectId, true);
    $urls['drill'] = CRM_Utils_System::url('civicrm/pumdrill', "pumEntity=project&pid=".$projectId, true);
    $urls['disable'] = CRM_Utils_System::url('civicrm/pumproject', "action=disable&pid=".$projectId, true);
    $urls['enable'] = CRM_Utils_System::url('civicrm/pumproject', "action=enable&pid=".$projectId, true);
    $urls['del'] = CRM_Utils_System::url('civicrm/pumproject', "action=delete&pid=".$projectId, true);
    return $urls;
  }
  /**
   * Function to set actions for row
   * 
   * @param obj $dao
   * @return array
   * @access protected
   */
  protected function setRowActions($dao) {
    $urls = $this->setRowUrls($dao->id);
    $pageActions = array();
    $pageActions[] = '<a class="action-item" title="View project details" href="'.$urls['view'].'">View</a>';
    if (CRM_Core_Permission::check('edit all contacts') || CRM_Core_Permission::check('administer CiviCRM')) {
      $pageActions[] = '<a class="action-item" title="Edit project" href="'.$urls['edit'].'">Edit</a>';
      if ($dao->is_active == 1) {
        $pageActions[] = '<a class="action-item" title="Disable project" href="'.$urls['disable'].'">Disable</a>';
      } else {
        $pageActions[] = '<a class="action-item" title="Enable project" href="'.$urls['enable'].'">Enable</a>';
      }
      if (CRM_Threepeas_BAO_PumProject::checkCanBeDeleted($dao->id)) {
        $pageActions[] = '<a class="action-item" title="Delete project" href="'.$urls['del'].'">Delete</a>';
      }
    }
    $pageActions[] = '<a class="action-item" title="Drill down project" href="'.$urls['drill'].'">Drill Down</a>';
    return array_merge($pageActions, $this->getHookActions(array('id' => $dao->id, 'title' => $dao->title)));
  }
  /**
   * Function to set the page configuration initially
   * 
   * @access protected
   */
  protected function setPageConfiguration() {
    CRM_Utils_System::setTitle(ts('List of All Projects'));    
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $this->countryType = $threepeasConfig->countryContactType;
    $this->customerType = $threepeasConfig->customerContactType;
    $session = CRM_Core_Session::singleton();
    $url = CRM_Utils_System::url('civicrm/allprojectslist', 'reset=1', true);
    $session->pushUserContext($url);
  }
  /**
   * Function to get projects with as much data as possible
   * 
   * @return object DAO
   * @access protected
   */
  protected function getDaoProjects() {
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
  protected function getHookActions($project) {
    $hooks = CRM_Utils_Hook::singleton();
    $return = $hooks->invoke(1, $project, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, 'civicrm_threepeas_projectactions');
    if (is_array($return)) {
      return $return;
    }
    return array();
  }
}
