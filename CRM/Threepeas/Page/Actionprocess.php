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
        $validActions = array("add", "edit", "disable", "delete", "enable");
        $validEntities = array("programme", "project", "budgetdivision");
        
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
                    case "programme":
                        $entityParams = $this->setProgrammeData();
                        switch ($this->_action) {
                            case "add":
                                $session->setStatus(ts("Programme added succesfully."), ts("Added"), 'success');
                                $programmeId = CRM_Threepeas_PumProgramme::add($entityParams);
                                /*
                                 * if clicked 'Save and divide budget', redirect to 
                                 * budget division page
                                 */
                                if ($this->_submitButton == "Save and divide budget") {
                                    $this->_redirectUrl = CRM_Utils_System::url('civicrm/pumprogrammedivision', null, TRUE);
                                    $this->_redirectUrl .= "&pid=$programmeId";
                                }
                                break;
                            case "edit":
                                $session->setStatus(ts("Programme saved succesfully."), ts("Saved"), 'success');
                                CRM_Threepeas_PumProgramme::update($entityParams);
                                /*
                                 * if clicked 'Save and divide budget', redirect to 
                                 * budget division page
                                 */
                                if ($this->_submitButton == "Save and divide budget") {
                                    $this->_redirectUrl = CRM_Utils_System::url('civicrm/pumprogrammedivision', null, TRUE);
                                    $this->_redirectUrl .= "&pid=".$entityParams['programme_id'];
                                }
                                break;
                            case "enable":
                                $session->setStatus(ts("Programme enabled succesfully."), ts("Enabled"), 'success');
                                CRM_Threepeas_PumProgramme::enable($entityParams['programme_id']);
                                break;
                            case "disable":
                                $session->setStatus(ts("Programme disabled succesfully."), ts("Disabled"), 'success');
                                CRM_Threepeas_PumProgramme::disable($entityParams['programme_id']);
                                break;
                            case "delete":
                                $session->setStatus(ts("Programme succesfully deleted"), ts("Deleted"), 'success');
                                CRM_Threepeas_PumProgramme::delete($entityParams['programme_id']);
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
                            case "enable":
                                $session->setStatus(ts("Project enabled succesfully."), ts("Enable"), 'success');
                                CRM_Threepeas_PumProject::enable($entityParams['project_id']);
                                break;
                            case "disable":
                                $session->setStatus(ts("Project disabled succesfully."), ts("Disabled"), 'success');
                                CRM_Threepeas_PumProject::disable($entityParams['project_id']);
                                break;
                            case "delete":
                                $session->setStatus(ts("Project succesfully deleted"), ts("Deleted"), 'success');
                                CRM_Threepeas_PumProject::delete($entityParams['project_id']);
                                break;
                        }
                        break; 
                    case "budgetdivision":
                        switch ($this->_action) {
                            case "add":
                                $entityParams = $this->setBudgetDivisionData();
                                $session->setStatus(ts("Programme Budget Division row added"), ts("Added"), 'success');
                                CRM_Threepeas_PumProgrammeDivision::add($entityParams);
                                $this->_redirectUrl = CRM_Utils_System::url('civicrm/pumprogrammedivision', null, TRUE);
                                $this->_redirectUrl .= "&pid=".$this->_data['programmeId'];
                                break;
                            case "delete":
                                $programmeDivisionId = $this->_data['pid'];
                                CRM_Threepeas_PumProgrammeDivision::delete($programmeDivisionId);
                                $this->_redirectUrl = CRM_Utils_System::url('civicrm/pumprogrammedivision', null, TRUE);
                                $this->_redirectUrl .= "&pid=".$this->_data['programmeId'];
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
                            case "programme":
                                $this->_redirectUrl = CRM_Utils_System::url
                                    ('civicrm/programmelist', null, TRUE);
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
                    case "saveProgramme":
                        $this->_submitButton = trim(strip_tags($requestValue));
                        break;
                    case "saveProject":
                        $this->_submitButton = trim(strip_tags($requestValue));
                        break;
                    case "saveProgrammeDivision":
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
     * Function to set the fields in $this->_data into params for programme
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @return array $result
     * @access private
     */
    private function setProgrammeData() {
        $result = array();
        if (empty($this->_data)) {
            return $result;
        }
        foreach ($this->_data as $dataField => $dataValue) {
            switch($dataField) {
                case "programmeId":
                    $result['programme_id'] = $dataValue;
                case "programmeTitle":
                    $result['title'] = $dataValue;
                    break;
                case "programmeDescription":
                    $result['description'] = $dataValue;
                    break;
                case "programmeManager":
                    $result['contact_id_manager'] = $dataValue;
                    break;
                case "programmeBudget":
                    $result['budget'] = $dataValue;
                    break;
                case "programmeGoals":
                    $result['goals'] = $dataValue;
                    break;
                case "programmeRequirements":
                    $result['requirements'] = $dataValue;
                    break;
                case "programmeStartDate":
                    if (!empty($dataValue)) {
                        $result['start_date'] = $dataValue;
                    }
                    break;
                case "programmeEndDate":
                    if (!empty($dataValue)) {
                        $result['end_date'] = $dataValue;
                    }
                    break;
            }
        }
        $result['is_active'] = 0;
        if (isset($this->_data['programmeIsActive'])) {
            if ($this->_data['programmeIsActive'] == 1 || $this->_data['programmeIsActive'] == "on") {
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
                case "projectProgramme":
                    $result['programme_id'] = $dataValue;
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
                    if (!empty($dataValue)) {
                        $result['start_date'] = $dataValue;
                    }
                    break;
                case "projectEndDate":
                    if (!empty($dataValue)) {
                        $result['end_date'] = $dataValue;
                    }
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
     * Function to set the fields in $this->_data into params for programme
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
                case "programmeId":
                    $result['programme_id'] = $dataValue;
                case "programmeDivisionCountry":
                    $result['country_id'] = $dataValue;
                    break;
                case "programmeDivisionMinProjects":
                    $result['min_projects'] = $dataValue;
                    break;
                case "programmeDivisionMaxProjects":
                    $result['max_projects'] = $dataValue;
                    break;
                case "programmeDivisionMinBudget":
                    $result['min_budget'] = $dataValue;
                    break;
                case "programmeDivisionMaxBudget":
                    $result['max_budget'] = $dataValue;
                    break;
            }
        }
        return $result;
    }
}