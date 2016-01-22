<?php
/**
 * Page Disabeld Projects List to list all disabled projects (PUM)
 *
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 15 June 2015
 *
 * Copyright (C) 2015 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the AGPL-3.0
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_DisabledProjectsList extends CRM_Core_Page {
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
   * Function to build the display row
   *
   * @param object $dao
   * @return array
   * @access protected
   */
  protected function buildRow($dao) {
    $displayRow = array();
    $displayRow['id'] = $dao->id;
    $displayRow['title'] = $this->getProjectTitle($dao->title);
    $this->processProjectType($dao->customer_id, $dao->country_id, $displayRow);
    $displayRow['projectmanager_name'] = $dao->projectmanager_name;
    $displayRow['start_date'] = CRM_Threepeas_Utils::setProjectDate($dao->start_date);
    $displayRow['end_date'] = CRM_Threepeas_Utils::setProjectDate($dao->end_date);
    $displayRow['number_cases'] = CRM_Threepeas_BAO_PumCaseProject::countCasesForProject($dao->id);
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
    $urls['view'] = CRM_Utils_System::url('civicrm/pumproject', "action=view&pid=".$projectId, true);
    $urls['drill'] = CRM_Utils_System::url('civicrm/pumdrill', "pumEntity=project&context=disabled&pid=".$projectId, true);
    $urls['case_del'] = CRM_Utils_System::url('civicrm/deletedisabledpumproject', "action=delete&context=cases&pid=".$projectId, true);
    $urls['del'] = CRM_Utils_System::url('civicrm/deletedisabledpumproject', "action=delete&context=only&pid=".$projectId, true);
    return $urls;
  }
  /**
   * Function to set actions for row
   *
   * @param object $dao
   * @return array
   * @access protected
   */
  protected function setRowActions($dao) {
    $urls = $this->setRowUrls($dao->id);
    $pageActions = array();
    $pageActions[] = '<a class="action-item" title="View project details" href="'.$urls['view'].'">View Project</a>';
    $pageActions[] = '<a class="action-item" title="Drill down project" href="'.$urls['drill'].'">Drill down</a>';
    if (CRM_Core_Permission::check('edit all contacts') || CRM_Core_Permission::check('administer CiviCRM')) {
      $pageActions[] = '<a class="action-item" title="Delete project with cases" href="'.$urls['case_del'].'">Delete WITH Cases</a>';
      $pageActions[] = '<a class="action-item" title="Delete project only" href="'.$urls['del'].'">Delete Project ONLY</a>';
    }
    return $pageActions;
  }
  /**
   * Function to set the page configuration initially
   *
   * @access protected
   */
  protected function setPageConfiguration() {
    CRM_Utils_System::setTitle(ts('List of Disabled Projects'));
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $this->countryType = $threepeasConfig->countryContactType;
    $this->customerType = $threepeasConfig->customerContactType;
    $session = CRM_Core_Session::singleton();
    $url = CRM_Utils_System::url('civicrm/disabledprojectslist', 'reset=1', true);
    $session->pushUserContext($url);
  }
  /**
   * Function to get projects with as much data as possible
   *
   * @return object DAO
   * @access protected
   */
  protected function getDaoProjects() {
    $query = 'SELECT a.*, b.display_name AS projectmanager_name
      FROM civicrm_project a
      LEFT JOIN civicrm_contact b ON a.projectmanager_id = b.id
      WHERE a.is_active = 0
      ORDER BY a.start_date DESC';
    return CRM_Core_DAO::executeQuery($query);
  }
}
