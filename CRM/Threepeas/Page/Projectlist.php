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
        
        if (isset($_REQUEST['cid'])) {
            $customerId = $_REQUEST['cid'];
        } else {
            $customerId = 0;
        }
        unset($_REQUEST);
        if (!empty($customerId)) {
            $projects = CRM_Threepeas_PumProject::getAllProjectsByCustomerId($customerId);
        } else { 
            $projects = CRM_Threepeas_PumProject::getAllProjects();
        }
        $displayProjects = array();
        foreach ($projects as $project) {
            $displayProject = array();
            $displayProject['id'] = $project['id'];
            $displayProject['title'] = $project['title'];

            if (isset($project['programme_id']) && !empty($project['programme_id'])) {
                $displayProject['programme_id'] = $project['programme_id'];
                $displayProject['programme_name'] = CRM_Threepeas_PumProgramme::getProgrammeTitleWithId($project['programme_id']);
            }
            
            if (isset($project['customer_id']) && !empty($project['customer_id'])) {
                $displayProject['customer_id'] = $project['customer_id'];
                $contactParams = array(
                    'id'    =>  $project['customer_id'],
                    'return'=>  'display_name'
                );
                $displayProject['customer_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
            }
            
            if (isset($project['sector_coordinator_id']) && !empty($project['sector_coordinator_id'])) {
                $displayProject['sector_coordinator_id'] = $project['sector_coordinator_id'];
                $contactParams = array(
                    'id'     =>  $project['sector_coordinator_id'],
                    'return' =>  'display_name'
                );
                $displayProject['sector_coordinator_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
            }
            
            if (isset($project['country_coordinator_id']) && !empty($project['country_coordinator_id'])) {
                $displayProject['country_coordinator_id'] = $project['country_coordinator_id'];
                $contactParams = array(
                    'id'     =>  $project['country_coordinator_id'],
                    'return' =>  'display_name'
                );
                $displayProject['country_coordinator_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
            }
            
            if (isset($project['project_officer_id']) && !empty($project['project_officer_id'])) {
                $displayProject['project_officer_id'] = $project['project_officer_id'];
                $contactParams = array(
                    'id'     =>  $project['project_officer_id'],
                    'return' =>  'display_name'
                );
                $displayProject['project_officer_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
            }
            
            if (isset($project['start_date']) && !empty($project['start_date'])) {
                $displayProject['start_date'] = date("d-m-Y", strtotime($project['start_date']));
            }
            
            if (isset($project['end_date']) && !empty($project['end_date'])) {
                $displayProject['end_date'] = date("d-m-Y", strtotime($project['end_date']));
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
            $disableUrl = CRM_Utils_System::url('civicrm/actionprocess', "pumAction=disable&pumEntity=project&projectId=".$project['id'], true);
            $enableUrl = CRM_Utils_System::url('civicrm/actionprocess', "pumAction=enable&pumEntity=project&projectId=".$project['id'], true);
            $delUrl = CRM_Utils_System::url('civicrm/actionprocess', "pumAction=delete&pumEntity=project&projectId=".$project['id'], true);
            $pageActions = array();
            $pageActions[] = '<a class="action-item" title="View project details" href="'.$viewUrl.'">View</a>';
            $pageActions[] = '<a class="action-item" title="Edit project" href="'.$editUrl.'">Edit</a>';
            $pageActions[] = '<a class="action-item" title="Drill down project" href="'.$drillUrl.'">Drill Down</a>';
            if ($project['is_active'] == 1) {
                $pageActions[] = '<a class="action-item" title="Disable project" href="'.$disableUrl.'">Disable</a>';
            } else {
                $pageActions[] = '<a class="action-item" title="Enable project" href="'.$enableUrl.'">Enable</a>';                
            }
            if (CRM_Threepeas_PumProject::checkProjectDeleteable($project['id'])) {
                $pageActions[] = '<a class="action-item" title="Delete project" href="'.$delUrl.'">Delete</a>';
            }
            $displayProject['actions'] = $pageActions;
            $displayProjects[] = $displayProject;
        }
        $this->assign('pumProjects', $displayProjects);
        $addUrl = CRM_Utils_System::url('civicrm/pumproject', 'action=add', true);
        $this->assign('addUrl', $addUrl);
        parent::run();
  }
}
