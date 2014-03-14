<?php
/**
 * Page Pumdrill to drill down the tree of programmes, projects and activities
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 5 Mar 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the Academic Free License version 3.0.
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Pumdrill extends CRM_Core_Page {
    function run() {
        $entity = CRM_Utils_Request::retrieve('pumEntity', 'String', $this);
        $this->assign('entity', $entity);
        
        if ($entity == "programme") {
            $programmeId = CRM_Utils_Request::retrieve('pid', 'Positive', $this);
            $programme = CRM_Threepeas_PumProgramme::getProgrammeById($programmeId);
            
            $this->assign('programmeId', $programmeId);
            
            $programmeTitleLabel = '<label for="Title">'.ts('Title').'</label>';
            $this->assign('programmeTitleLabel', $programmeTitleLabel);
            $this->assign('programmeTitle', $programme['title']);
            
            $contactParams = array(
                'id'    =>  $programme['contact_id_manager'],
                'return'=>  'display_name'
            );
            try {
                $programmeManagerName = civicrm_api3('Contact', 'Getvalue', $contactParams);
            } catch (CiviCRM_API3_Exception $e) {
                $programmeManagerName = "";
            }
            $programmeManagerLabel = '<label for="Programme Manager">'.ts('Programme Manager').'</label>';
            $this->assign('programmeManagerLabel', $programmeManagerLabel);            
            $this->assign('programmeManager', $programmeManagerName);
            $pageTitle = "Programme ".$programme['title'];
            $this->assign('pageTitle', $pageTitle);
            $drillRows = $this->_buildProgrammeRows($programmeId);
            $this->assign("drillData", $drillRows);
            
        } else {
        
            $projectId = CRM_Utils_Request::retrieve('pid', 'Positive', $this);
            $project = CRM_Threepeas_PumProject::getAllCasesByProjectId($projectId);
            
            $this->assign('projectId', $projectId);
        }
        CRM_Utils_System::setTitle(ts("Drill Down"));
        parent::run();
    }
    /**
     * Function to build rows for programme drill down
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 5 March 2014
     * @param int $programmeId
     * @return array $rows
     * @access private
     */
    private function _buildProgrammeRows($programmeId) {
        $rows = array();
        if (empty($programmeId) || !is_numeric($programmeId)) {
            return $rows;
        }
        /*
         * build rows by select all projects for programme, and within project
         * all cases for project
         */
        $projects = CRM_Threepeas_PumProject::getAllProjects($programmeId);
        foreach ($projects as $project) {
            $cases = CRM_Threepeas_PumProject::getAllCasesByProjectId($project['id']);
            if (empty($cases)) {
                $row = array();
                $row['project_id'] = $project['id'];
                $row['project_officer_id'] = $project['project_officer_id'];
                $contactParams = array(
                    'id'    =>  $project['project_officer_id'],
                    'return'=>  'display_name'
                );
                try {
                    $projectOfficerName = civicrm_api3('Contact', 'Getvalue', $contactParams);
                } catch (CiviCRM_API3_Exception $e) {
                    $projectOfficerName = "";
                }
                $row['project_officer_name'] = $projectOfficerName;
                
                $projectUrl = CRM_Utils_System::url('civicrm/pumproject', 'action=view&pid='.$project['id'], true);
                $projectHtml = '<a href="'.$projectUrl. '">'.$project['title'].'</a>';
                $row['project_title'] = $projectHtml;
                
                $row['project_start_date'] = $project['start_date'];
                $row['project_end_date'] = $project['end_date'];
                if ($project['is_active'] == 1) {
                    $row['project_active'] = "Yes";
                } else {
                    $row['project_active'] = "No";
                }          
                $rows[] = $row;
                
            } else {
                $firstRow = TRUE;
                foreach ($cases as $case) {
                    $row = array();
                    if ($firstRow) {
                        $row['project_id'] = $project['id'];
                        $row['project_officer_id'] = $project['project_officer_id'];
                        $contactParams = array(
                            'id'    =>  $project['project_officer_id'],
                            'return'=>  'display_name'
                        );
                        try {
                            $projectOfficerName = civicrm_api3('Contact', 'Getvalue', $contactParams);
                        } catch (CiviCRM_API3_Exception $e) {
                            $projectOfficerName = "";
                        }
                        $row['project_officer_name'] = $projectOfficerName;

                        $projectUrl = CRM_Utils_System::url('civicrm/pumproject', 'action=view&pid='.$project['id'], true);
                        $projectHtml = '<a href="'.$projectUrl. '">'.$project['title'].'</a>';
                        $row['project_title'] = $projectHtml;

                        $row['project_start_date'] = $project['start_date'];
                        $row['project_end_date'] = $project['end_date'];

                        if ($project['is_active'] == 1) {
                            $row['project_active'] = "Yes";
                        } else {
                            $row['project_active'] = "No";
                        }
                        $firstRow = FALSE;
                    }
                    $caseUrlParams = "reset=1&action=view&id=".$case['case_id']."&cid=".$case['client_id'];
                    $caseUrl = CRM_Utils_System::url('civicrm/contact/view/case', $caseUrlParams, true);
                    $caseHtml = '<a href="'.$caseUrl.'">'.$case['subject'].'</a>';
                    
                    $row['case_subject'] = $caseHtml;
                    $row['case_type'] = $case['case_type'];
                    $row['case_id'] = $case['case_id'];
                    $rows[] = $row;
                }
                $rows[] = array();
            }
        }
        return $rows;
    }
}
