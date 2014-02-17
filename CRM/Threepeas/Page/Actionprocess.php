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
    /**
     * standard CivCRM page function run
     */
    function run() {
        if (!isset($session)) {
            $session = CRM_Core_Session::singleton();
        }
        /*
         * retrieve data from $_POST
         */
        $this->retrievePostData();
        /*
         * determine further processing based on entity/action
         */
        if ($this->_submitButton == "Save" || $this->_submitButton == 
                "Save and divide budget") {
            switch ($this->_entity) {
                case "program":
                    $entityParams = $this->setProgramData();
                    switch ($this->_action) {
                        case "add":
                            $session->setStatus(ts("Program added succesfully."), ts("Added"), 'success');
                            CRM_Threepeas_PumProgram::add($entityParams);
                            break;
                        case "edit":
                            $session->setStatus(ts("Program saved succesfully."), ts("Saved"), 'success');
                            CRM_Threepeas_PumProgram::update($entityParams);
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
            }
        }
        
        CRM_Utils_System::redirect($this->_redirectUrl);
        //parent::run();
    }
    /**
     * Function to retrieve data from POST
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @access private
     */
    private function retrievePostData() {
        if (!empty($_POST)) {
            foreach ($_POST as $postKey => $postValue) {
                switch($postKey) {
                    case "pumEntity":
                        $this->_entity = trim(strip_tags($postValue));
                        switch($postValue) {
                            case "program":
                                $this->_redirectUrl = CRM_Utils_System::url
                                    ('civicrm/programlist', null, TRUE);
                                break;
                            case "project":
                                $this->_redirectUrl = CRM_Utils_System::url(
                                        'civicrm/projectlist', null, TRUE);
                        }
                        break;
                    case "pumAction":
                        $this->_action = trim(strip_tags($postValue));
                        break;
                    case "saveProgram":
                        $this->_submitButton = trim(strip_tags($postValue));
                        break;
                    case "saveProject":
                        $this->_submitButton = trim(strip_tags($postValue));
                        break;
                    default:
                        $this->_data[$postKey] = $postValue;
                }
            }
        }
        unset($_POST);
        unset($_REQUEST);
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
}
