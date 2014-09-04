<?php
/**
 * Page Projectlist to list all projects (PUM)
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 17 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the Academic Free License version 3.0.
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Projectlist extends CRM_Core_Page {
  function run() {
    CRM_Utils_System::setTitle(ts('List of Projects'));
    
    $snippet = CRM_Utils_Request::retrieve('snippet', 'Positive');
    if ($snippet != 1) {
      $addUrl = CRM_Utils_System::url('civicrm/pumproject', 'action=add', true);
    } else {
      $addUrl = '';
    }
    $this->assign('addUrl', $addUrl);
    $type = CRM_Utils_Request::retrieve('type', 'String');
    $customerId = CRM_Utils_Request::retrieve('cid', 'Positive');
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    switch ($type) {
      case $threepeasConfig->countryContactType:
        $projects = CRM_Threepeas_BAO_PumProject::getValues(array('country_id' => $customerId));
        break;
      case $threepeasConfig->customerContactType:
        $projects = CRM_Threepeas_BAO_PumProject::getValues(array('customer_id' => $customerId));
        break;
      default:
        $projects = CRM_Threepeas_BAO_PumProject::getValues(array());
        break;
    }
    $displayProjects = array();
    foreach ($projects as $project) {
      $displayProject = array();
      $displayProject['id'] = $project['id'];
      if (isset($project['title'])) {
        $displayProject['title'] = $project['title'];
      }

      if (isset($project['programme_id']) && !empty($project['programme_id'])) {
        $displayProject['programme_id'] = $project['programme_id'];
        $displayProject['programme_name'] = CRM_Threepeas_BAO_PumProgramme::getProgrammeTitleWithId($project['programme_id']);
      }
      $coordinatorType = NULL;      
      if (isset($project['customer_id']) && !empty($project['customer_id'])) {
        $coordinatorType = 'customer';
        $coordinatorId = $project['customer_id'];
        $displayProject['customer_id'] = $project['customer_id'];
        $contactParams = array(
          'id'    =>  $project['customer_id'],
          'return'=>  'display_name'
        );
        $displayProject['customer_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
        $displayProject['showCustomer'] = 1;
      } else {
        if (isset($project['country_id']) && !empty($project['country_id'])) {
          $coordinatorType = 'country';
          $coordinatorId = $project['country_id'];
          $displayProject['country_id'] = $project['country_id'];
          $contactParams = array(
            'id'    =>  $project['country_id'],
            'return'=>  'display_name'
          );
          $displayProject['country_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
          $displayProject['showCustomer'] = 0;
        }
      }
      
      if ($coordinatorType == 'customer' || $coordinatorType == 'country') {
        if ($coordinatorType == 'customer') {
          $displayProject['sector_coordinator_id'] = CRM_Threepeas_BAO_PumProject::getSectorCoordinator($coordinatorId, $coordinatorType);
          $contactParams = array(
            'id'     =>  $displayProject['sector_coordinator_id'],
            'return' =>  'display_name'
          );
          try {
            $displayProject['sector_coordinator_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
          } catch (CiviCRM_API3_Exception $ex) {
            $displayProject['sector_coordinator_name'] = NULL;
          }
        }

        $displayProject['country_coordinator_id'] = CRM_Threepeas_BAO_PumProject::getCountryCoordinator($coordinatorId, $coordinatorType);
        $contactParams = array(
          'id'     =>  $displayProject['country_coordinator_id'],
          'return' =>  'display_name'
        );
        try {
          $displayProject['country_coordinator_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
        } catch (CiviCRM_API3_Exception $ex) {
          $displayProject['country_coordinator_name'] = NULL;
        }
        $displayProject['project_officer_id'] = CRM_Threepeas_BAO_PumProject::getProjectOfficer($coordinatorId, $coordinatorType);
        $contactParams = array(
          'id'     =>  $displayProject['project_officer_id'],
          'return' =>  'display_name'
        );
        try {
          $displayProject['project_officer_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
        } catch (CiviCRM_API3_Exception $ex) {
          $displayProject['project_officer_name'] = NULL;
        }

        $displayProject['representative_id'] = CRM_Threepeas_BAO_PumProject::getRepresentative($coordinatorId, $coordinatorType);
        $contactParams = array(
          'id'     =>  $displayProject['representative_id'],
          'return' =>  'display_name'
        );
        try {
          $displayProject['representative_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
        } catch (CiviCRM_API3_Exception $ex) {
          $displayProject['representative_name'] = NULL;
        }
      }
      
      if (isset($project['start_date']) && !empty($project['start_date'])) {
        $displayProject['start_date'] = $project['start_date'];
      }

        if (isset($project['end_date']) && !empty($project['end_date'])) {
          $displayProject['end_date'] = $project['end_date'];
        }

      if (!isset($project['is_active'])) {
        $displayProject['is_active'] = ts("No");
      } else {
        if ($project['is_active'] == 1) {
          $displayProject['is_active'] = ts("Yes");
        } else {
          $displayProject['is_active'] = ts("No");
        }
      }
      /*
       * set page actions, first generic part (view, edit)
       * then in foreach the specifics (enable or disable and delete only if allowed)
       */
      $editUrl = CRM_Utils_System::url('civicrm/pumproject', "action=update&pid=".$project['id'], true);
      $viewUrl = CRM_Utils_System::url('civicrm/pumproject', "action=view&pid=".$project['id'], true);
      $drillUrl = CRM_Utils_System::url('civicrm/pumdrill', "pumEntity=project&pid=".$project['id'], true);
      $disableUrl = CRM_Utils_System::url('civicrm/pumproject', "action=disable&pid=".$project['id'], true);
      $enableUrl = CRM_Utils_System::url('civicrm/pumproject', "action=enable&pid=".$project['id'], true);
      $delUrl = CRM_Utils_System::url('civicrm/pumproject', "action=delete&pid=".$project['id'], true);
      $pageActions = array();
      $pageActions[] = '<a class="action-item" title="View project details" href="'.$viewUrl.'">View</a>';
      $pageActions[] = '<a class="action-item" title="Edit project" href="'.$editUrl.'">Edit</a>';
      $pageActions[] = '<a class="action-item" title="Drill down project" href="'.$drillUrl.'">Drill Down</a>';
      //load extra actions from hooks
      $pageActions = array_merge($pageActions, $this->getProjectActions($project));
      
      if (isset($project['is_active']) && $project['is_active'] == 1) {
        $pageActions[] = '<a class="action-item" title="Disable project" href="'.$disableUrl.'">Disable</a>';
      } else {
        $pageActions[] = '<a class="action-item" title="Enable project" href="'.$enableUrl.'">Enable</a>';                
      }
      if (CRM_Threepeas_BAO_PumProject::checkCanBeDeleted($project['id'])) {
        $pageActions[] = '<a class="action-item" title="Delete project" href="'.$delUrl.'">Delete</a>';
      }
      $displayProject['actions'] = $pageActions;
      $displayProjects[] = $displayProject;
    }
    $this->assign('pumProjects', $displayProjects);
    parent::run();
  }
  
  /**
   * Returns an array with extra links (filled from a hook)
   * 
   * @param array $project
   * @return array with links e.g. array('<a class="action-item" title="my item" href="link.php">link</a>')
   * 
   */
  protected function getProjectActions($project) {
    $hooks = CRM_Utils_Hook::singleton();
    $return = $hooks->invoke(1, $project, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, 'civicrm_threepeas_projectactions');
    if (is_array($return)) {
      return $return;
    }
    return array();
  }
}
