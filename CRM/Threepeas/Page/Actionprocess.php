<?php
/**
 * Page Actionprocess to process actions on PUM entities (PUM)
 * This 'abuses' the CiviCRM page cycle as it does not display anyhthing!
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 11 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the Academic Free License version 3.0.
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Actionprocess extends CRM_Core_Page {
    protected $_action = "";
    protected $_entity = "";
    protected $_redirectUrl = "";
    protected $_submitButton = "";
    protected $_data = array();
    protected $_requestURL = "";
    /**
     * standard CivCRM page function run
     */
    function run() {
        if (!isset($session)) {
            $session = CRM_Core_Session::singleton();
        }
        $validActions = array("add", "edit", "disable", "delete");
        $validEntities = array("program", "project", "budgetdivision");
        
        /*
         * retrieve data from $_REQUEST
         */
        $this->retrieveRequestData();
        /*
         * determine further processing based on entity/action
         */
        if (in_array($this->_action, $validActions)) {
            if (in_array($this->_entity, $validEntities)) {
                switch ($this->_entity) {
                    case "program":
                        $entityParams = $this->setProgramData();
                        switch ($this->_action) {
                            case "add":
                                $session->setStatus(ts("Program added succesfully."), ts("Added"), 'success');
                                $programId = CRM_Threepeas_PumProgram::add($entityParams);
                                /*
                                 * if clicked 'Save and divide budget', redirect to 
                                 * budget division page
                                 */
                                if ($this->_submitButton == "Save and divide budget") {
                                    $this->_redirectUrl = CRM_Utils_System::url('civicrm/pumprogramdivision', null, TRUE);
                                    $this->_redirectUrl .= "&pid=$programId";
                                }
                                break;
                            case "edit":
                                $session->setStatus(ts("Program saved succesfully."), ts("Saved"), 'success');
                                CRM_Threepeas_PumProgram::update($entityParams);
                                /*
                                 * if clicked 'Save and divide budget', redirect to 
                                 * budget division page
                                 */
                                if ($this->_submitButton == "Save and divide budget") {
                                    $this->_redirectUrl = CRM_Utils_System::url('civicrm/pumprogramdivision', null, TRUE);
                                    $this->_redirectUrl .= "&pid=".$entityParams['program_id'];
                                }
                                break;
                            case "disable":
                                $session->setStatus(ts("Program disabled succesfully."), ts("Disabled"), 'success');
                                CRM_Threepeas_PumProgram::disable($entityParams['program_id']);
                                break;
                            case "delete":
                                /*
                                 * check if program can be deleted
                                 */
                                $programDeletable = CRM_Threepeas_PumProgram::checkProgramDeleteable($entityParams['program_id']);
                                if ($programDeletable == FALSE) {
                                    $session->setStatus(ts("Program can not be deleted, has projects attached"), ts("Cancelled"), 'error');
                                } else {
                                    $session->setStatus(ts("Program succesfully deleted"), ts("Deleted"), 'success');
                                    CRM_Threepeas_PumProgram::delete($entityParams['program_id']);
                                }
                                break;
                        }
                        break;
                    case "project":
                        $entityParams = $this->setProjectData();
                        switch ($this->_action) {
                            case "add":
                                $session->setStatus(ts("Project added succesfully."), ts("Added"), 'success');
                                CRM_Threepeas_PumProject::add($entityParams);
                                break;
                            case "edit":
                                $session->setStatus(ts("Project saved succesfully."), ts("Saved"), 'success');
                                CRM_Threepeas_PumProject::update($entityParams);
                                break;
                        }
                        break; 
                    case "budgetdivision":
                        switch ($this->_action) {
                            case "add":
                                $entityParams = $this->setBudgetDivisionData();
                                $session->setStatus(ts("Program Budget Division row added"), ts("Added"), 'success');
                                CRM_Threepeas_PumProgramDivision::add($entityParams);
                                $this->_redirectUrl = CRM_Utils_System::url('civicrm/pumprogramdivision', null, TRUE);
                                $this->_redirectUrl .= "&pid=".$this->_data['programId'];
                                break;
                            case "delete":
                                $programDivisionId = $this->_data['pid'];
                                CRM_Threepeas_PumProgramDivision::delete($programDivisionId);
                                $this->_redirectUrl = CRM_Utils_System::url('civicrm/pumprogramdivision', null, TRUE);
                                $this->_redirectUrl .= "&pid=".$this->_data['pid'];
                                break;
                        }
                        break;
                }
            }
        }
        
        CRM_Utils_System::redirect($this->_redirectUrl);
        //parent::run();
    }
    /**
     * Function to retrieve data from REQUEST
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @access private
     */
    private function retrieveRequestData() {
        if (!empty($_REQUEST)) {
            foreach ($_REQUEST as $requestKey => $requestValue) {
                switch($requestKey) {
                    case "pumEntity":
                        $this->_entity = trim(strip_tags($requestValue));
                        switch($requestValue) {
                            case "program":
                                $this->_redirectUrl = CRM_Utils_System::url
                                    ('civicrm/programlist', null, TRUE);
                                break;
                            case "project":
                                $this->_redirectUrl = CRM_Utils_System::url
                                    ('civicrm/projectlist', null, TRUE);
                                break;
                        }
                        break;
                    case "pumAction":
                        $this->_action = trim(strip_tags($requestValue));
                        break;
                    case "saveProgram":
                        $this->_submitButton = trim(strip_tags($requestValue));
                        break;
                    case "saveProject":
                        $this->_submitButton = trim(strip_tags($requestValue));
                        break;
                    case "saveProgramDivision":
                        $this->_submitButton = "Save";
                    case "q":
                        $this->_requestURL = $requestValue;
                        break;
                    default:
                        $this->_data[$requestKey] = $requestValue;
                }
            }
        }
        unset($_POST, $_REQUEST, $_GET);
    }
    /**
     * Function to set the fields in $this->_data into params for program
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @return array $result
     * @access private
     */
    private function setProgramData() {
        $result = array();
        if (empty($this->_data)) {
            return $result;
        }
        foreach ($this->_data as $dataField => $dataValue) {
            switch($dataField) {
                case "programId":
                    $result['program_id'] = $dataValue;
                case "programTitle":
                    $result['title'] = $dataValue;
                    break;
                case "programDescription":
                    $result['description'] = $dataValue;
                    break;
                case "programManager":
                    $result['contact_id_manager'] = $dataValue;
                    break;
                case "programBudget":
                    $result['budget'] = $dataValue;
                    break;
                case "programGoals":
                    $result['goals'] = $dataValue;
                    break;
                case "programRequirements":
                    $result['requirements'] = $dataValue;
                    break;
                case "programStartDate":
                    $result['start_date'] = $dataValue;
                    break;
                case "programEndDate":
                    $result['end_date'] = $dataValue;
                    break;
            }
        }
        $result['is_active'] = 0;
        if (isset($this->_data['programIsActive'])) {
            if ($this->_data['programIsActive'] == 1 || $this->_data['programIsActive'] == "on") {
                $result['is_active'] = 1;
            }
        }
        return $result;
    }
    /**
     * Function to set the fields in $this->_data into params for project
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     * @return array $result
     * @access private
     */
    private function setProjectData() {
        $result = array();
        if (empty($this->_data)) {
            return $result;
        }
        foreach ($this->_data as $dataField => $dataValue) {
            switch($dataField) {
                case "projectId":
                    $result['project_id'] = $dataValue;
                case "projectTitle":
                    $result['title'] = $dataValue;
                    break;
                case "projectProgram":
                    $result['program_id'] = $dataValue;
                    break;
                case "projectReason":
                    $result['reason'] = $dataValue;
                    break;
                case "projectWorkDescription":
                    $result['work_description'] = $dataValue;
                    break;
                case "projectQualifications":
                    $result['qualifications'] = $dataValue;
                    break;
                case "projectExpectedResults":
                    $result['expected_results'] = $dataValue;
                    break;
                case "projectSectorCoordinator":
                    $result['sector_coordinator_id'] = $dataValue;
                    break;
                case "projectCountryCoordinator":
                    $result['country_coordinator_id'] = $dataValue;
                    break;
                case "projectOfficer":
                    $result['project_officer_id'] = $dataValue;
                    break;
                case "projectStartDate":
                    $result['start_date'] = $dataValue;
                    break;
                case "projectEndDate":
                    $result['end_date'] = $dataValue;
                    break;
            }
        }
        $result['is_active'] = 0;
        if (isset($this->_data['projectIsActive'])) {
            if ($this->_data['projectIsActive'] == 1 || $this->_data['projectIsActive'] == "on") {
                $result['is_active'] = 1;
            }
        }
        return $result;
    }
    /**
     * Function to set the fields in $this->_data into params for program
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @return array $result
     * @access private
     */
    private function setBudgetDivisionData() {
        $result = array();
        if (empty($this->_data)) {
            return $result;
        }
        foreach ($this->_data as $dataField => $dataValue) {
            switch($dataField) {
                case "programId":
                    $result['program_id'] = $dataValue;
                case "programDivisionCountry":
                    $result['country_id'] = $dataValue;
                    break;
                case "programDivisionMinProjects":
                    $result['min_projects'] = $dataValue;
                    break;
                case "programDivisionMaxProjects":
                    $result['max_projects'] = $dataValue;
                    break;
                case "programDivisionMinBudget":
                    $result['min_budget'] = $dataValue;
                    break;
                case "programDivisionMaxBudget":
                    $result['max_budget'] = $dataValue;
                    break;
            }
        }
        return $result;
    }
}