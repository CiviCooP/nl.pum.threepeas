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
        $this->assign('pumProjectUrl', CRM_Utils_System::url('civicrm/pumproject', null, true));
        $this->assign('delProjectUrl', CRM_Utils_System::url('civicrm/pumaction', null, true));
        $projects = CRM_Threepeas_PumProject::getAllProjects();
        $displayProjects = array();
        foreach ($projects as $project) {
            $displayProject = array();
            $displayProject['id'] = $project['id'];
            $displayProject['title'] = $project['title'];
            
            $displayProject['programme_id'] = $project['program_id'];
            $displayProject['programme_name'] = CRM_Threepeas_PumProgramme::getProgrammeTitleWithId($project['programme_id']);
            
            $displayProject['sector_coordinator_id'] = $project['sector_coordinator_id'];
            $contactParams = array(
                'id'     =>  $project['sector_coordinator_id'],
                'return' =>  'display_name'
            );
            $displayProject['sector_coordinator_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
            
            $displayProject['country_coordinator_id'] = $project['country_coordinator_id'];
            $contactParams = array(
                'id'     =>  $project['country_coordinator_id'],
                'return' =>  'display_name'
            );
            $displayProject['country_coordinator_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
            
            $displayProject['project_officer_id'] = $project['project_officer_id'];
            $contactParams = array(
                'id'     =>  $project['project_officer_id'],
                'return' =>  'display_name'
            );
            $displayProject['project_officer_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
            
            $displayProject['start_date'] = date("d-m-Y", strtotime($project['start_date']));
            $displayProject['end_date'] = date("d-m-Y", strtotime($project['end_date']));
            
            if (!isset($project['is_active'])) {
                $displayProject['is_active'] = ts("No");
            } else {
                if ($project['is_active'] == 1) {
                    $displayProject['is_active'] = ts("Yes");
                } else {
                    $displayProject['is_active'] = ts("No");
                }
            }
            $displayProjects[] = $displayProject;
        }
        $this->assign('pumProjects', $displayProjects);
        parent::run();
  }
}
