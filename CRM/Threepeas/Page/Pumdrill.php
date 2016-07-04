<?php
/**
 * Page Pumdrill to drill down the tree of programmes, projects and activities
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 5 Mar 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the  AGPL-3.0
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Pumdrill extends CRM_Core_Page {
  function run() {
    $entity = CRM_Utils_Request::retrieve('pumEntity', 'String', $this);
    $context = CRM_Utils_Request::retrieve('context', 'String');
    $this->assign('entity', $entity);
    $session = CRM_Core_Session::singleton();
    $config = CRM_Core_Config::singleton();
    $doneUrl = $session->readUserContext();
    // set doneUrl to My PUM Projects report if equal to userFrameWork (meaning not set)
    if ($doneUrl == $config->userFrameworkBaseURL) {
      $doneUrl = $this->getReportUrl();
    }
    $this->assign('doneUrl', $doneUrl);
    if ($entity == "programme") {
      $programmeId = CRM_Utils_Request::retrieve('pid', 'Positive', $this);
      $programme = CRM_Threepeas_BAO_PumProgramme::getValues(array('id' => $programmeId));
      $pageTitle = "Programme ".$programme[$programmeId]['title'];
      $this->assign('pageTitle', $pageTitle);
      $drillRows = $this->buildProgrammeRows($programmeId);
            
    } else {
        
      $projectId = CRM_Utils_Request::retrieve('pid', 'Positive', $this);
      $project = CRM_Threepeas_BAO_PumProject::getValues(array('id'=> $projectId));
      $pageTitle = "Project ".$project[$projectId]['title'];
      $this->assign('pageTitle', $pageTitle);
            
      $productLabel = array(
        'type'       => ts("Main Activity"),
        'objective'  => ts("Objective"),
        'client'     => ts("Client/Country"),
        'expert'     => ts("Expert"),
        'start_date' => ts("Start date"),
        'end_date'   => ts("End date"),
        'status'     => ts("Status")
      );
      $this->assign('productLabel', $productLabel);
      if (!isset($project[$projectId]['customer_id']) && isset($project[$projectId]['country_id'])) {
        $drillRows = $this->buildProjectRows($projectId, $project[$projectId]['country_id'], $context);
      } else {
        $drillRows = $this->buildProjectRows($projectId, $project[$projectId]['customer_id'], $context);
      }
    }
    $this->assign('drillData', $drillRows);            
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
  private function buildProgrammeRows($programmeId) {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $rows = array();
    if (empty($programmeId) || !is_numeric($programmeId)) {
      return $rows;
    }
    /*
     * build rows by select all projects for programme, and within project
     * all cases for project
     */
    $projects = CRM_Threepeas_BAO_PumProject::getValues(array('programme_id' => $programmeId));
    foreach ($projects as $project) {
      $cases = CRM_Threepeas_BAO_PumProject::getCasesByProjectId($project['id']);
      if (empty($cases)) {
        $row = array();
        $row['project_id'] = $project['id'];
        if (!empty($project['customer_id'])) {
          $row['project_officer_id'] = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($project['customer_id'], 'project_officer');
        } else {
          if (!empty($project['country_id'])) {
          $row['project_officer_id'] = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($project['country_id'], 'project_officer');
          }
        }
        if (isset($row['project_officer_id'])) {
          $contactParams = array(
            'id'    =>  $row['project_officer_id'],
            'return'=>  'display_name'
          );
          try {
            $projectOfficerName = civicrm_api3('Contact', 'Getvalue', $contactParams);
          } catch (CiviCRM_API3_Exception $e) {
            $projectOfficerName = "";
          }
          $row['project_officer_name'] = $projectOfficerName;
        }

        $projectUrl = CRM_Utils_System::url('civicrm/pumproject', 'action=view&pid='.$project['id']);
        $projectHtml = '<a href="'.$projectUrl. '">'.$project['title'].'</a>';
        $row['project_title'] = $projectHtml;

        if (isset($row['project_start_date'])) {
          $row['project_start_date'] = $project['start_date'];
        }
        if (isset($row['project_end_date'])) {
          $row['project_end_date'] = $project['end_date'];
        }
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
            if (!empty($project['customer_id'])) {
              $row['project_officer_id'] = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($project['customer_id'], 'project_officer');
            }
            $contactParams = array(
              'id'    =>  $row['project_officer_id'],
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
          $caseType = CRM_Utils_Array::value($case['case_type'], $threepeasConfig->caseTypes);
          $caseUrlParams = "reset=1&action=view&id=".$case['case_id']."&cid=".$case['client_id'];
          $caseUrl = CRM_Utils_System::url('civicrm/contact/view/case', $caseUrlParams);
          $caseHtml = '<a href="'.$caseUrl.'">'.$caseType.'</a>';
          $row['case_type'] = $caseHtml;
          $row['case_id'] = $case['case_id'];
          $row['case_status'] = CRM_Utils_Array::value($case['case_status'], $threepeasConfig->caseStatus);
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
   * @param int $customerId
   * @return array $drillRows
   * @access private
   */
  private function buildProjectRows($projectId, $customerId, $context) {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    if (!empty($customerId) && $context != 'disabled') {
      $caseUrl = CRM_Utils_System::url('civicrm/case/add', 'reset=1&action=add&cid='.$customerId.'&context=case&pid='.$projectId, true);
      $this->assign('caseUrl', $caseUrl);
    } 
    $drillRows = array();
    if (empty($projectId) || !is_numeric($projectId)) {
      return $drillRows;
    }
    $projectCases = CRM_Threepeas_BAO_PumProject::getCasesByProjectId($projectId);
    foreach ($projectCases as $caseId => $case) {
      $row = array();
      $row['case_id'] = $caseId;
      $clientParams = array(
        'contact_id'    =>  $case['client_id'],
        'return'        =>  'display_name'
      );
      try {
        $clientName = civicrm_api3('Contact', 'Getvalue', $clientParams);
      } catch (CiviCRM_API3_Exception $e) {
        $clientName = "";
      }
      $row['client'] = $clientName;
      $row['client_id'] = $case['client_id'];
      
      $expertParams = array(
        'contact_id'    =>  $case['expert_id'],
        'return'        =>  'display_name'
      );
      try {
        $expertName = civicrm_api3('Contact', 'Getvalue', $expertParams);
      } catch (CiviCRM_API3_Exception $e) {
        $expertName = "";
      }
      $row['expert'] = $expertName;
      $row['expert_id'] = $case['expert_id'];

      $caseUrlParams = "reset=1&action=view&id=".$caseId."&cid=".$case['client_id'];
      $caseUrl = CRM_Utils_System::url('civicrm/contact/view/case', $caseUrlParams);
      $caseType = CRM_Utils_Array::value($case['case_type'], $threepeasConfig->caseTypes);
      $caseHtml = '<a href="'.$caseUrl.'">'.$caseType.'</a>';
      
      $row['type'] = $caseHtml;

      // issue 2507 Erik Hommel 8 dec 2015 <erik.hommel@civicoop.org>
      if ($threepeasConfig->caseTypes[$case['case_type']] == "Projectintake") {
        $row['start_date'] = $case['start_date'];
        $row['end_date'] = $case['end_date'];
      } else {
        $mainActivityDates = $this->getMainActivityDates($caseId);
        $row['start_date'] = $mainActivityDates['start_date'];
        $row['end_date'] = $mainActivityDates['end_date'];
      }

      $row['status'] = CRM_Utils_Array::value($case['case_status'], $threepeasConfig->caseStatus);
      $drillRows[] = $row;
    }
    return $drillRows;
  }

  /**
   * Method to get start and end date of main activity custom group
   *
   * @param $caseId
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function getMainActivityDates($caseId) {
    $mainActivtityDates = array(
      'start_date' => "",
      'end_date' => "");
    $maCustomGroup = civicrm_api3('CustomGroup', 'Getsingle', array('name' => 'main_activity_info'));
    if ($maCustomGroup) {
      $maCustomFields = civicrm_api3('CustomField', 'Get', array('custom_group_id' => $maCustomGroup['id']));
      foreach ($maCustomFields['values'] as $customFieldId => $customField) {
        if ($customField['name'] == 'main_activity_start_date') {
          $startDateColumn = $customField['column_name'];
        }
        if ($customField['name'] == 'main_activity_end_date') {
          $endDateColumn = $customField['column_name'];
        }
      }
      $query = 'SELECT '.$startDateColumn.' AS start_date, '.$endDateColumn.' AS end_date FROM '
        .$maCustomGroup['table_name'].' WHERE entity_id = %1';
      $params = array(1 => array($caseId, 'Integer'));
      $dao = CRM_Core_DAO::executeQuery($query, $params);
      if ($dao->fetch()) {
        $mainActivtityDates['start_date'] = $dao->start_date;
        $mainActivtityDates['end_date'] = $dao->end_date;
      }
    }
    return $mainActivtityDates;
  }

  /**
   * Issue 3287 - return either the URL of My PUM Projects or the CiviCRM start page as the url for when the done button is clicked
   *
   */
  private function getReportUrl() {
    $instanceId = CRM_Core_DAO::singleValueQuery('SELECT id FROM civicrm_report_instance WHERE report_id = %1',
      array(1 => array('nl.pum.casereports/pumprojects', 'String')));
    if (!empty($instanceId)) {
      return CRM_Utils_System::url('civicrm/report/instance/'.$instanceId, 'reset=1', true);
    } else {
      return CRM_Utils_System::url('civicrm/dashboard/', 'reset=1', true);
    }
  }
}
