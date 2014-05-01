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
     * Function to retrieve projects based on any search criteria in params
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 2 Apr 2014
     * @param array $params
     * @return array $result
     * @access public
     * @static
     */
    public static function get($params) {
        if (empty($params)) {
            $result = self::getAllProjects();
            return $result;
        }
        $tableFields = self::fields();
        foreach ($tableFields as $tableField) {
            if (isset($params[$tableField])) {
                
            }
        }
        
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
     * @throws Exception when project with title and programme_id already exists in DB
     * @throws Exception when programme_id, sector_coordinator_id, country_coordinator_id 
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
         * if no programme_id, then programme_id = 0
         */
        if (!isset($params['programme_id'])) {
            $programmeId = 0;
        } else {
            $programmeId = $params['programme_id'];
        }
        /*
         * check numeric fields
         */
        $numericFields = array("programme_id", "sector_coordinator_id", 
            "country_coordinator_id", "project_officer_id", "customer_id");
        if (!CRM_Utils_ThreepeasUtils::checkNumericFields($numericFields, $params)) {
            throw new Exception("Fields ".implode(", ", $numericFields)." have to be numeric");
            return $projectId;
        }
        /*
         * check if title does not exist yet (has to be unique) for the programme
         */
        $title = CRM_Core_DAO::escapeString($params['title']);
        $query = "SELECT COUNT(*) AS count_title FROM civicrm_project 
            WHERE title = '$title' AND programme_id = $programmeId";
        $dao = CRM_Core_DAO::executeQuery($query);
        if ($dao->fetch()) {
            if ($dao->count_title > 0) {
                throw new Exception("Programme with title $title already exists");
                return $programmeId;
            }
        }
        
        $fields = array();
        $fields[] = "title = '$title'";
        $fields[] = "programme_id = $programmeId";

        if (isset($params['reason'])) {
            $reason = CRM_Core_DAO::escapeString($params['reason']);
            $fields[] = "reason = '$reason'";
        }
        
        if (isset($params['customer_id'])) {
            $fields[] = "customer_id = {$params['customer_id']}";
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
                $startDate = NULL;
            }
            $fields[] = "start_date = '$startDate'";
        }
        
        if (isset($params['end_date'])) {
            if (!empty($params['end_date'])) {
                $endDate = date("Ymd", strtotime($params['end_date']));
            } else {
                $endDate = NULL;
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
            /*
             * add OptionValue for project
             */
            self::_changeOptionValue($title, $projectId);
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
     * @throws Exception when project_id, programme_id, sector_coordinator_id, country_coordinator_id 
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
        if (!isset($params['programme_id'])) {
            $params['programme_id'] = 0;
        } 
        /*
         * check numeric fields
         */
        $numericFields = array("project_id", "programme_id", "sector_coordinator_id", 
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
        
        $programmeId = $params['programme_id'];
        $fields = array();
        
        $title = CRM_Core_DAO::escapeString($params['title']);
        $fields[] = "title = '$title'";
        $fields[] = "programme_id = $programmeId";
        
        if (isset($params['reason'])) {
            $reason = CRM_Core_DAO::escapeString($params['reason']);
            $fields[] = "reason = '$reason'";
        }
        
        if (isset($params['customer_id'])) {
            $fields[] = "customer_id = {$params['customer_id']}";
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
                $startDate = NULL;
            }
            $fields[] = "start_date = '$startDate'";
        }
        
        if (isset($params['end_date'])) {
            if (!empty($params['end_date'])) {
                $endDate = date("Ymd", strtotime($params['end_date']));
            } else {
                $endDate = NULL;
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
            self::_changeOptionValue($title, $projectId);            
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
     * Function to disable a project
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
        if (empty($projectId) || !is_numeric($projectId)) {
            throw new Exception("Project_id can not be empty and has to be numeric");
        }
        $update = "UPDATE civicrm_project SET is_active = 0 WHERE id = $projectId";
        CRM_Core_DAO::executeQuery($update);
        return;
    }
    /**
     * Function to enable a project
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
    public static function enable($projectId) {
        if (empty($projectId) || !is_numeric($projectId)) {
            throw new Exception("Project_id can not be empty and has to be numeric");
        }
        $update = "UPDATE civicrm_project SET is_active = 1 WHERE id = $projectId";
        CRM_Core_DAO::executeQuery($update);
        return;
    }
    /**
     * Function to retrieve all projects for a program
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 18 Feb 2014
     * @param int $programmeId
     * @return array $result
     * @access public
     * @static
     */
    public static function getAllProjectsByProgrammeId($programmeId) {
        $result = array();
        if (empty($programmeId) || !is_numeric($programmeId)) {
            return $result;
        }
        $query = "SELECT * FROM civicrm_project WHERE programme_id = $programmeId";
        $dao = CRM_Core_DAO::executeQuery($query);
        while ($dao->fetch()) {
            $result[] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to retrieve all projects for a customer
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 26 Mar 2014
     * @param int $customerId
     * @return array $result
     * @access public
     * @static
     */
    public static function getAllProjectsByCustomerId($customerId) {
        $result = array();
        if (empty($customerId) || !is_numeric($customerId)) {
            return $result;
        }
        $query = "SELECT * FROM civicrm_project WHERE customer_id = $customerId";
        $dao = CRM_Core_DAO::executeQuery($query);
        while ($dao->fetch()) {
            $result[] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to retrieve all active projects for a customer sorted by title
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 5 Mar 2014
     * @param int $programmeId
     * @return array $result
     * @access public
     * @static
     */
    public static function getAllActiveProjectsByProgrammeId($programmeId) {
        $result = array();
        if (empty($programmeId) || !is_numeric($programmeId)) {
            return $result;
        }
        $query = "SELECT * FROM civicrm_project WHERE programme_id = $programmeId AND is_active = 1 ORDER BY title";
        $dao = CRM_Core_DAO::executeQuery($query);
        while ($dao->fetch()) {
            $result[] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to check if a proejct can be deleted. Will return TRUE
     * if there are no case children for the project
     * 
     * @author Erik Hommel
     * @date 3 Mar 2014
     * @param int $projectId
     * @return boolean
     * @throws Exception when $projectId non-numeric or empty
     * @access public
     * @static
     */
    public static function checkProjectDeleteable($projectId) {
        if (empty($projectId) || !is_numeric($projectId)) {
            throw new Exception("ProjectId can not be empty or non numeric to check 
                if the project can be deleted");
            return FALSE;
        }
        $projectCases = self::getAllCasesByProjectId($projectId);
        if (empty($projectCases)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    /**
     * Function to retrieve all cases for a project
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 3 Mar 2014
     * @param int $projectId
     * @return array $result
     * @access public
     * @static
     */
    public static function getAllCasesByProjectId($projectId) {
        $result = array();
        /*
         * return empty if projectId empty or non-numeric
         */
        if (empty($projectId) || !is_numeric($projectId)) {
            return $result;
        }
        /*
         * retrieve custom table for cases and custom field for project
         */
        $customGroupParams = array(
            'name'      =>  "caseProject",
        );
        try {
            $customGroup = civicrm_api3('CustomGroup', 'Getsingle', $customGroupParams);
            if (isset($customGroup['table_name'])) {
                $customTableName = $customGroup['table_name'];
            } else {
                return $result;
            }
            if (isset($customGroup['id'])) {
                $customGroupId = $customGroup['id'];
            } else {
                return $result;
            }
            $customFieldParams = array(
                'custom_group_id'   =>  $customGroupId,
                'name'              =>  "Project",
                'return'            =>  'column_name'
            );
            try {
                $customProjectField = civicrm_api3('CustomField', 'Getvalue', $customFieldParams);
                /*
                 * select all entity_ids (case_ids) with $projectId
                 */
                $caseQuery = "SELECT entity_id FROM $customTableName WHERE $customProjectField = $projectId";
                $caseDao = CRM_Core_DAO::executeQuery($caseQuery);
                while ($caseDao->fetch()) {
                    /*
                     * retrieve case data for each case and put in result array
                     */
                    $caseParams = array(
                        'is_deleted'    => 0,
                        'id'            => $caseDao->entity_id
                    );
                    $case = civicrm_api3('Case', 'Getsingle', $caseParams);
                    /*
                     * retrieve client
                     */
                    foreach ($case['client_id'] as $caseClient) {
                        $caseClientId = $caseClient;
                        break;
                    }
                    $caseResult = array();
                    $caseResult['case_id'] = $case['id'];
                    $caseResult['subject'] = $case['subject'];
                    $caseResult['start_date'] = $case['start_date'];
                    $caseResult['end_date'] = $case['end_date'];
                    $caseResult['client_id'] = $caseClientId;
                    /*
                     * get case type option value
                     */
                    $caseTypeGroupParams = array(
                        'name'  =>  "case_type",
                    ); 
                    try {
                        $caseTypeApi = civicrm_api3('OptionGroup', 'Getsingle', $caseTypeGroupParams);
                        $caseTypeGroupId = $caseTypeApi['id'];
                        $caseTypeValueParams = array(
                            'option_group_id'   =>  $caseTypeGroupId,
                            'value'             =>  $case['case_type_id'],
                            'return'            =>  "label"
                        );
                        try {
                            $caseResult['case_type'] = civicrm_api3('OptionValue', 'GetValue', $caseTypeValueParams);
                        } catch (CiviCRM_API3_Exception $e) {
                            $caseResult['case_type'] = "";
                        }
                    } catch (CiviCRM_API3_Exception $e) {
                        $caseResult['case_type'] = "";
                    }
                    /*
                     * get case status option value
                     */
                    $caseStatusGroupParams = array(
                        'name'  =>  "case_status",
                    ); 
                    try {
                        $caseStatusApi = civicrm_api3('OptionGroup', 'Getsingle', $caseStatusGroupParams);
                        $caseStatusGroupId = $caseStatusApi['id'];
                        $caseStatusValueParams = array(
                            'option_group_id'   =>  $caseStatusGroupId,
                            'value'             =>  $case['status_id'],
                            'return'            =>  "label"
                        );
                        try {
                            $caseResult['case_status'] = civicrm_api3('OptionValue', 'GetValue', $caseStatusValueParams);
                        } catch (CiviCRM_API3_Exception $e) {
                            $caseResult['case_status'] = "";
                        }
                    } catch (CiviCRM_API3_Exception $e) {
                        $caseResult['case_status'] = "";
                    }
                    $result[$case['id']] = $caseResult;
                }
                return $result;
            } catch (CiviCRM_API3_Exception $e) {
                return $result;
            }
        } catch (CiviCRM_API3_Exception $e) {
            return $result;
        }
    }
    /**
     * Function to add OptionValue for project
     * 
     * @author Erik Hommel <erik.hommel@civicoop.org>
     * @date 18 Feb 2014
     * @param string $label
     * @param int $value
     * @access private
     * @static
     */
    private static function _changeOptionValue($label, $value) {
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
        $fields = self::fields();
        foreach ($fields as $field) {
            $fieldName = $field['name'];
            if (isset($dao->$fieldName)) {
                $result[$fieldName] = $dao->$fieldName;
            }
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
    /**
     * Function to count the number of projects for a customer
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 26 Mar 2014
     * @param int $customerId
     * @return int $countProjects
     * @access public
     * static
     */
    public static function countCustomerProjects($customerId) {
        $countProjects = 0;
        if (empty($customerId) || !is_numeric($customerId)) {
            return $countProjects;
        }
        $dao = CRM_Core_DAO::executeQuery("SELECT COUNT(*) AS countProjects FROM
            civicrm_project WHERE customer_id = $customerId");
        if ($dao->fetch()) {
            $countProjects = $dao->countProjects;
        }
        return $countProjects;
    }
    /**
     * Function to return a list of all fields in table civicrm_pumproject
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 2 Apr 2014
     * @return array $fields
     * @access public
     * @static
     */
    public static function fields() {
        $fields = array();
        $daoColumns = CRM_Core_DAO::executeQuery('DESCRIBE civicrm_project');
        while ($daoColumns->fetch()) {
            $field = array();
            $field['name'] = $daoColumns->Field;
            $field['type'] = $daoColumns->Type;
            $fields[] = $field;
        }
        return $fields;
    }
}

