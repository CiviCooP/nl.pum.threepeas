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
            $doneUrl = CRM_Utils_System::url('civicrm/programmelist');
            $this->assign('doneUrl', $doneUrl);
            
        } else {
        
            $projectId = CRM_Utils_Request::retrieve('pid', 'Positive', $this);
            $project = CRM_Threepeas_PumProject::getProjectById($projectId);
            
            $programmeTitle = CRM_Threepeas_PumProgramme::getProgrammeTitleWithId($project['programme_id']);
            if (!empty($programmeTitle)) {
                $pageTitle = $project['title']." (part of programme ".$programmeTitle.")";
            } else {
                $pageTitle = $project['title'];
            }
            $this->assign('pageTitle', $pageTitle);
            
            $productLabel = array(
                'subject'           => ts("Main Activity"),
                'type'              => ts("Type"),
                'status'            => ts("Status"),
                'client'            => ts("Client"),
                'activity'          => ts("Subactvity"),
                'activity_type'     => ts("Subactivity type"),
                'activity_status'   => ts("Subactivity status")
            );
            $this->assign('productLabel', $productLabel);
            $drillRows = $this->_buildProjectRows($projectId);
            $this->assign('drillData', $drillRows);
            $doneUrl = CRM_Utils_System::url('civicrm/projectlist');
            $this->assign('doneUrl', $doneUrl);
            
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
                
                $projectUrl = CRM_Utils_System::url('civicrm/pumproject', 'action=view&pid='.$project['id']);
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

                        $projectUrl = CRM_Utils_System::url('civicrm/pumproject', 'action=view&pid='.$project['id']);
                        $projectHtml = '<a href="'.$projectUrl. '">'.$project['title'].'</a>';
                        $row['project_title'] = $projectHtml;

                        if (isset($project['start_date'])) { 
                            $row['project_start_date'] = $project['start_date'];
                        }
                        if (isset($project['end_date'])) {
                            $row['project_end_date'] = $project['end_date'];
                        }

                        if ($project['is_active'] == 1) {
                            $row['project_active'] = "Yes";
                        } else {
                            $row['project_active'] = "No";
                        }
                        $firstRow = FALSE;
                    }
                    $caseUrlParams = "reset=1&action=view&id=".$case['case_id']."&cid=".$case['client_id'];
                    $caseUrl = CRM_Utils_System::url('civicrm/contact/view/case', $caseUrlParams);
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
    /**
     * Function to build project rows
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 19 Mar 2014
     * @param int $projectId
     * @return array $drillRows
     * @access private
     */
    private function _buildProjectRows($projectId) {
        $drillRows = array();
        if (empty($projectId) || !is_numeric($projectId)) {
            return $drillRows;
        }
        $products = CRM_Threepeas_PumProject::getAllCasesByProjectId($projectId);
        foreach ($products as $product) {
            try {
                $caseDetails = civicrm_api3('Case', 'Getsingle', array('case_id' => $product['case_id']));
                $subActivities = $caseDetails['activities'];
            } catch (CiviCRM_API3_Exception $e) {
                $subActivities = array();
            }
            if (empty($subActivities)) {
                $row = array();
                $row['case_id'] = $product['case_id'];
                $clientParams = array(
                    'contact_id'    =>  $product['client_id'],
                    'return'        =>  'display_name'
                );
                try {
                    $clientName = civicrm_api3('Contact', 'Getvalue', $clientParams);
                } catch (CiviCRM_API3_Exception $e) {
                    $clientName = "";
                }
                $row['client'] = $clientName;
                $row['client_id'] = $product['client_id'];
                $caseUrlParams = "reset=1&action=view&id=".$product['case_id']."&cid=".$product['client_id'];
                $caseUrl = CRM_Utils_System::url('civicrm/contact/view/case', $caseUrlParams);
                $caseHtml = '<a href="'.$caseUrl.'">'.$product['subject'].'</a>';
                $row['subject'] = $caseHtml;
                
                $row['type'] = $product['case_type'];
                $row['status'] = $product['case_status'];
                
                $drillRows[] = $row;
                
            } else {
                $firstRow = TRUE;
                foreach ($subActivities as $subActivity) {
                    $row = array();
                    if ($firstRow) {
                        $row['case_id'] = $product['case_id'];
                        $clientParams = array(
                            'contact_id'    =>  $product['client_id'],
                            'return'        =>  'display_name'
                        );
                        try {
                            $clientName = civicrm_api3('Contact', 'Getvalue', $clientParams);
                        } catch (CiviCRM_API3_Exception $e) {
                            $clientName = "";
                        }
                        $row['client'] = $clientName;
                        $row['client_id'] = $product['client_id'];
                        $caseUrlParams = "reset=1&action=view&id=".$product['case_id']."&cid=".$product['client_id'];
                        $caseUrl = CRM_Utils_System::url('civicrm/contact/view/case', $caseUrlParams);
                        $caseHtml = '<a href="'.$caseUrl.'">'.$product['subject'].'</a>';
                        $row['subject'] = $caseHtml;
                
                        $row['type'] = $product['case_type'];
                        $row['status'] = $product['case_status'];
                
                        $firstRow = FALSE;
                    }
                    $subActUrlParams = "reset=1&action=update&id=".$subActivity."&caseid=".$product['case_id']."&cid=".$product['client_id'];
                    $subActUrl = CRM_Utils_System::url('civicrm/case/activity', $subActUrlParams);
                    $actParams = array(
                        'id'                    =>  $subActivity,
                        'is_current_revision'   =>  1
                    );
                    try {
                        $activityDetails = civicrm_api3('Activity', 'Getsingle', $actParams);
                    } catch (CiviCRM_API3_Exception $e) {
                        throw new Exception("Could not find an activity with id $subActivity, error from API Activity Getsingle : ".$e->getMessage());
                    }                    
                    if (isset($activityDetails['subject'])) {
                        $subActHtml = '<a href="'.$subActUrl.'">'.$activityDetails['subject'].'</a>';
                        $row['activity'] = $subActHtml;
                    } 
                    
                    $actTypeGroupParams = array(
                        'name'      => 'activity_type',
                        'return'    => 'id'
                    );
                    try {
                        $actTypeGroupId = civicrm_api3('OptionGroup', 'Getvalue', $actTypeGroupParams);
                        $actTypeParams = array(
                            'option_group_id'   =>  $actTypeGroupId,
                            'value'             =>  $activityDetails['activity_type_id'],
                            'return'            =>  'label'
                        );
                        try {
                            $actTypeLabel = civicrm_api3('OptionValue', 'Getvalue', $actTypeParams);
                        } catch (CiviCRM_API3_Exception $e) {
                            throw new Exception("Could not find an option label for 
                                value {$activityDetails['activity_type_id']} in 
                                    option group $optionGroupId");
                        }
                    } catch (CiviCRM_API3_Exception $e) {
                        throw new Exception("Could not find an option group with name activity_type");
                    }
                    $row['activity_type'] = $actTypeLabel;
                    
                    $actStatusGroupParams = array(
                        'name'      => 'activity_status',
                        'return'    => 'id'
                    );
                    try {
                        $actStatusGroupId = civicrm_api3('OptionGroup', 'Getvalue', $actStatusGroupParams);
                        $actStatusParams = array(
                            'option_group_id'   =>  $actStatusGroupId,
                            'value'             =>  $activityDetails['status_id'],
                            'return'            =>  'label'
                        );
                        try {
                            $actStatusLabel = civicrm_api3('OptionValue', 'Getvalue', $actStatusParams);
                        } catch (CiviCRM_API3_Exception $e) {
                            throw new Exception("Could not find an option label for 
                                value {$activityDetails['status_id']} in 
                                    option group $optionGroupId");
                        }
                    } catch (CiviCRM_API3_Exception $e) {
                        throw new Exception("Could not find an option group with name activity_status");
                    }
                    $row['activity_status'] = $actStatusLabel;
                    $row['activity_id'] = $subActivity;
                    $drillRows[] = $row;
                }
                $drillRows[] = array();

            }
        }
        return $drillRows;
    }
}
