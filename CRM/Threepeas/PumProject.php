<?php

/**
 * Class PumProject for dealing with projects (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 17 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
 */
class CRM_Threepeas_PumProject {
    /**
     * Constructor function
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     */
    function __construct() {
    }
    /**
     * Function to retrieve all proejcts
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     * @return array $result with data
     * @access public
     * @static
     */
    public static function getAllProjects() {
        $result = array();
        $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_project 
            ORDER BY title");
        while ($dao->fetch()) {
            $result[$dao->id] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to retrieve all active proejcts
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 18 Feb 2014
     * @return array $result with data
     * @access public
     * @static
     */
    public static function getAllActiveProjects() {
        $result = array();
        $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_project 
            WHERE is_active = 1 ORDER BY title");
        while ($dao->fetch()) {
            $result[$dao->id] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to retrieve single project with project_id
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     * @param int $projectId
     * @return array $result
     * @access public
     * @static
     */
    public static function getProjectById($projectId) {
        $result = array();
        if (empty($projectId) || !is_numeric($projectId)) {
            return $result;
        }
        $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_project 
            WHERE id = $projectId");
        if ($dao->fetch()) {
            $result = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to add project
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     * @param array $params
     * @return int $projectId (id fo the created project)
     * @throws Exception when required param missing or empty
     * @throws Exception when project with title and program_id already exists in DB
     * @throws Exception when program_id, sector_coordinator_id, country_coordinator_id 
     *         or project_officer_id is not numeric
     * @access public
     * @static
     */
    public static function add($params) {
        $projectId = 0;
        /*
         * array with mandatory parameters (have to be there and can not be empty
         */
        $mandatoryFields = array("title");
        if (!CRM_Utils_ThreepeasUtils::checkMandatoryFields($mandatoryFields, $params)) {
            throw new Exception("Missing or empty mandatory params ".
                implode("; ", $mandatoryFields));
            return $projectId;
        }
        /*
         * if no program_id, then program_id = 0
         */
        if (!isset($params['program_id'])) {
            $programId = 0;
        } else {
            $programId = $params['program_id'];
        }
        /*
         * check numeric fields
         */
        $numericFields = array("program_id", "sector_coordinator_id", 
            "country_coordinator_id", "project_officer_id");
        if (!CRM_Utils_ThreepeasUtils::checkNumericFields($numericFields, $params)) {
            throw new Exception("Fields ".implode(", ", $numericFields)." have to be numeric");
            return $projectId;
        }
        /*
         * check if title does not exist yet (has to be unique) for the program
         */
        $title = CRM_Core_DAO::escapeString($params['title']);
        $query = "SELECT COUNT(*) AS count_title FROM civicrm_project 
            WHERE title = '$title' AND program_id = $programId";
        $dao = CRM_Core_DAO::executeQuery($query);
        if ($dao->fetch()) {
            if ($dao->count_title > 0) {
                throw new Exception("Program with title $title already exists");
                return $programId;
            }
        }
        
        $fields = array();
        $fields[] = "title = '$title'";
        $fields[] = "program_id = $programId";

        if (isset($params['reason'])) {
            $reason = CRM_Core_DAO::escapeString($params['reason']);
            $fields[] = "reason = '$reason'";
        }

        if (isset($params['work_description'])) {
            $work_description = CRM_Core_DAO::escapeString($params['work_description']);
            $fields[] = "work_description = '$work_description'";
        }

        if (isset($params['qualifications'])) {
            $qualifications = CRM_Core_DAO::escapeString($params['qualifications']);
            $fields[] = "qualifications = '$qualifications'";
        }

        if (isset($params['expected_results'])) {
            $expected_results = CRM_Core_DAO::escapeString($params['expected_results']);
            $fields[] = "expected_results = '$expected_results'";
        }

        if (isset($params['sector_coordinator_id'])) {
            $fields[] = "sector_coordinator_id = {$params['sector_coordinator_id']}";
        }
        
        if (isset($params['country_coordinator_id'])) {
            $fields[] = "country_coordinator_id = {$params['country_coordinator_id']}";
        }
        
        if (isset($params['project_officer_id'])) {
            $fields[] = "project_officer_id = {$params['project_officer_id']}";
        }
        
        if (isset($params['start_date'])) {
            if (!empty($params['start_date'])) {
                $startDate = date("Ymd", strtotime($params['start_date']));
            } else {
                $startDate = "";
            }
            $fields[] = "start_date = '$startDate'";
        }
        
        if (isset($params['end_date'])) {
            if (!empty($params['end_date'])) {
                $endDate = date("Ymd", strtotime($params['end_date']));
            } else {
                $endDate = "";
            }
            $fields[] = "end_date = '$endDate'";
        }
        
        if (isset($params['is_active'])) {
            if ($params['is_active'] == 1 || $params['is_active'] == "y") {
                $fields[] = "is_active = 1";
            } else {
                $fields[] = "is_active = 0";
            }
        }
        if (!empty($fields)) {
            $insert = "INSERT INTO civicrm_project SET ".implode(", ", $fields);
            CRM_Core_DAO::executeQuery($insert);
            $query = "SELECT MAX(id) AS latest_id FROM civicrm_project";
            $dao = CRM_Core_DAO::executeQuery($query);
            if ($dao->fetch()) {
                $projectId = $dao->latest_id;
            }
        }
        return $projectId;
    }
    /**
     * Function to update project
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     * @param array $params
     * @return array $result
     * @throws Exception when required params not found or empty
     * @throws Exception when project_id, program_id, sector_coordinator_id, country_coordinator_id 
     *         or project_officer_id is not numeric
     * @throws Exception when no project with id found
     * @access public
     * @static
     */
    public static function update($params) {
        $result = array();
        /*
         * array with mandatory parameters
         */
        $mandatoryFields = array("project_id", "title");
        if (!CRM_Utils_ThreepeasUtils::checkMandatoryFields($mandatoryFields, $params)) {
            throw new Exception("Missing or empty mandatory params ".
                implode("; ", $mandatoryFields));
            return $result;
        }
        $projectId = $params['project_id'];
        if (!isset($params['program_id'])) {
            $params['program_id'] = 0;
        } 
        /*
         * check numeric fields
         */
        $numericFields = array("project_id", "program_id", "sector_coordinator_id", 
            "country_coordinator_id", "project_officer_id");
        if (!CRM_Utils_ThreepeasUtils::checkNumericFields($numericFields, $params)) {
            throw new Exception("Fields ".implode(", ", $numericFields)." have to be numeric");
            return $result;
        }
        /*
         * check if project exists
         */
        $checkQuery = "SELECT COUNT(*) AS count_project FROM civicrm_project 
            WHERE id = $projectId";
        $daoCheck = CRM_Core_DAO::executeQuery($checkQuery);
        if ($daoCheck->fetch()) {
            if ($daoCheck->count_project == 0) {
                throw new Exception("No project found with project_id $projectId");
            }
        }
        
        $programId = $params['program_id'];
        $fields = array();
        
        $title = CRM_Core_DAO::escapeString($params['title']);
        $fields[] = "title = '$title'";
        $fields[] = "program_id = $programId";
        
        if (isset($params['reason'])) {
            $reason = CRM_Core_DAO::escapeString($params['reason']);
            $fields[] = "reason = '$reason'";
        }

        if (isset($params['work_description'])) {
            $work_description = CRM_Core_DAO::escapeString($params['work_description']);
            $fields[] = "work_description = '$work_description'";
        }

        if (isset($params['qualifications'])) {
            $qualifications = CRM_Core_DAO::escapeString($params['qualifications']);
            $fields[] = "qualifications = '$qualifications'";
        }

        if (isset($params['expected_results'])) {
            $expected_results = CRM_Core_DAO::escapeString($params['expected_results']);
            $fields[] = "expected_results = '$expected_results'";
        }

        if (isset($params['sector_coordinator_id'])) {
            $fields[] = "sector_coordinator_id = {$params['sector_coordinator_id']}";
        }

        if (isset($params['country_coordinator_id'])) {
            $fields[] = "country_coordinator_id = {$params['country_coordinator_id']}";
        }

        if (isset($params['project_officer_id'])) {
            $fields[] = "project_officer_id = {$params['project_officer_id']}";
        }
        
        if (isset($params['start_date'])) {
            if (!empty($params['start_date'])) {
                $startDate = date("Ymd", strtotime($params['start_date']));
            } else {
                $startDate = "";
            }
            $fields[] = "start_date = '$startDate'";
        }
        
        if (isset($params['end_date'])) {
            if (!empty($params['end_date'])) {
                $endDate = date("Ymd", strtotime($params['end_date']));
            } else {
                $endDate = "";
            }
            $fields[] = "end_date = '$endDate'";
        }
        
        if (isset($params['is_active'])) {
            if ($params['is_active'] == 1) {
                $fields[] = "is_active = 1";
            } else {
                $fields[] = "is_active = 0";
            }
        }
        
        if (!empty($fields)) {
            $update = "UPDATE civicrm_project SET ".implode(", ", $fields).
                " WHERE id = $projectId";
            CRM_Core_DAO::executeQuery($update);
            $result = self::getProjectById($projectId);
        }
        return $result;
    }
    /**
     * Function to delete project
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     * @param int $projectId
     * @return void
     * @throws Exception when projectId is empty
     * @throws Exception when projectId is not numeric
     * @access public
     * @static
     */
    public static function delete($projectId) {
        if (empty($projectId) || !is_numeric($projectId)) {
            throw new Exception("ProjectId can not be empty and has to be numeric");
        }
        $delete = "DELETE FROM civicrm_project WHERE id = $projectId";
        CRM_Core_DAO::executeQuery($delete);
        return;
    }
    /**
     * Function to disable a program
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     * @param int $projectId
     * @return void
     * @throws Exception when projectId is empty
     * @throws Exception when projectId is not numeric
     * @access public
     * @static
     */
    public static function disable($projectId) {
        return;
        if (empty($projectId) || !is_numeric($projectId)) {
            throw new Exception("Project_id can not be empty and has to be numeric");
        }
        $update = "UPDATE civicrm_project SET is_active = 0 WHERE id = $projectId";
        CRM_Core_DAO::executeQuery($update);
        return;
    }
    /**
     * Function to add OptionValue for project
     * 
     * @author Erik Hommel <erik.hommel@civicoop.org>
     * @date 18 Feb 2014
     * @param string $label
     * @param int $value
     * @access public
     * @static
     */
    public static function _changeOptionValue($label, $value) {
        if (empty($label) || empty($value)) {
            throw new Exception("Label and value can not be empty when adding OptionValue for PUM Project");
            return;
        }
        $optionGroupId = self::_getProjectOptionGroup();
        if ($optionGroupId != 0) {
            /*
             * check if option value already exists and if so get id
             */
            $getIdParams = array(
                'option_group_id'   =>  $optionGroupId,
                'value'             =>  $value,
                'return'            =>  "id"
            );
            try {
                $optionValueId = civicrm_api3('OptionValue', 'Getvalue', $getIdParams);
                $createParams = array(
                    'id'                =>  $optionValueId,
                    'option_group_id'   =>  $optionGroupId,
                    'label'             =>  $label,
                    'value'             =>  $value,
                    'is_active'         =>  1,
                    'is_reserved'       =>  1
                );
            } catch (CiviCRM_API3_Exception $e) {
                $createParams = array(
                    'option_group_id'   =>  $optionGroupId,
                    'label'             =>  $label,
                    'value'             =>  $value,
                    'is_active'         =>  1,
                    'is_reserved'       =>  1
                );
            }
            civicrm_api3('OptionValue', 'Create', $createParams);
        }
    }
    /**
     * Function to populate array with dao
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     * @param object $dao
     * @return array $result
     * @access private
     * @static
     */
    private static function _daoToArray($dao) {
        $result = array();
        if (empty($dao)) {
            return $result;
        }
        if (isset($dao->id)) {
            $result['id'] = $dao->id;
        }
        if (isset($dao->title)) {
            $result['title'] = $dao->title;
        }
        if (isset($dao->program_id)) {
            $result['program_id'] = $dao->program_id;
        }
        if (isset($dao->reason)) {
            $result['reason'] = $dao->reason;
        }
        if (isset($dao->work_description)) {
            $result['work_description'] = $dao->work_description;
        }
        if (isset($dao->qualifications)) {
            $result['qualifications'] = $dao->qualifications;
        }
        if (isset($dao->expected_results)) {
            $result['expected_results'] = $dao->expected_results;
        }
        if (isset($dao->sector_coordinator_id)) {
            $result['sector_coordinator_id'] = $dao->sector_coordinator_id;
        }
        if (isset($dao->country_coordinator_id)) {
            $result['country_coordinator_id'] = $dao->country_coordinator_id;
        }
        if (isset($dao->project_officer_id)) {
            $result['project_officer_id'] = $dao->project_officer_id;
        }
        if (isset($dao->start_date)) {
            $result['start_date'] = $dao->start_date;
        }
        if (isset($dao->end_date)) {
            $result['end_date'] = $dao->end_date;
        }
        if (isset($dao->is_active)) {
            $result['is_active'] = $dao->is_active;
        }
        return $result;
    }
    /**
     * Function to retrieve option_group_id of pum_project
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 18 Feb 2014
     * @return int $optionGroupId
     * @access private
     * @static
     */
    private static function _getProjectOptionGroup() {
        $apiParams = array(
            'name'      =>  "pum_project",
            'return'    =>  "id"
        );
        try {
            $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $apiParams);
        } catch (CiviCRM_API3_Exception $e) {
            throw new Exception("Could not find OptionGroup for pum_project");
            $optionGroupId = 0;
        }
        return $optionGroupId;
    }
}

