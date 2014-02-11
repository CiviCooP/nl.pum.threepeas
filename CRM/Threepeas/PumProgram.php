<?php

/**
 * Class PumProgram for dealing with programs (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
 */
class CRM_Threepeas_PumProgram {
    /**
     * Constructor function
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 3 Feb 2014
     */
    function __construct() {
    }
    /**
     * Function to retrieve all programs
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @return array $result with data
     * @access public
     * @static
     */
    public static function getAllPrograms() {
        $result = array();
        $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_program");
        while ($dao->fetch()) {
            $result[$dao->id] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to retrieve single program with program_id
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @param int $program_id
     * @return array $result
     * @access public
     * @static
     */
    public static function getProgramById($programId) {
        $result = array();
        if (empty($programId) || !is_numeric($programId)) {
            return $result;
        }
        $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_program 
            WHERE id = $programId");
        if ($dao->fetch()) {
            $result = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to add program
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param array $params
     * @return int $program_id (id fo the created program)
     * @throws Exception when required param missing or empty
     * @throws Exception when program with title already exists in DB
     * @throws Exception when contact_id (manager) is not numeric
     * @access public
     * @static
     */
    public static function add($params) {
        $programId = 0;
        /*
         * array with required parameters
         */
        $mandatoryFields = array("title");
        if (!self::checkMandatoryFields($mandatoryFields, $params)) {
            throw new Exception("Missing or empty mandatory params ".
                implode("; ", $mandatoryFields));
            return $programId;
        }
        /*
         * check if title does not exist yet (has to be unique)
         */
        $title = CRM_Core_DAO::escapeString($params['title']);
        $query = "SELECT COUNT(*) AS count_title FROM civicrm_program 
            WHERE title = '$title'";
        $dao = CRM_Core_DAO::executeQuery($query);
        if ($dao->fetch()) {
            if ($dao->count_title > 0) {
                throw new Exception("Program with title $title already exists");
                return $programId;
            }
        }
        
        $fields = array();
        $fields[] = "title = '$title'";

        if (isset($params['description'])) {
            $description = CRM_Core_DAO::escapeString($params['description']);
            $fields[] = "description = '$description'";
        }

        if (isset($params['contact_id_manager'])) {
            if (!is_numeric($params['contact_id_manager'])) {
                throw new Exception("Param contact_id_manager has to be numeric 
                    but holds ".$params['contact_id_manager']);
                return $programId;
            } else {
                $fields[] = "contact_id_manager = {$params['contact_id_manager']}";
            }
        }

        if (isset($params['budget'])) {
            if (!is_numeric($params['budget'])) {
                throw new Exception("Param budget has to be numeric but holds ".
                    $params['budget']);
                return $programId;
            } else {
                $fields[] = "budget = {$params['budget']}";
            }
        }
        
        if (isset($params['goals'])) {
            $goals = CRM_Core_DAO::escapeString($params['goals']);
            $fields[] = "goals = '$goals'";
        }
        
        if (isset($params['requirements'])) {
            $requirements = CRM_Core_DAO::escapeString($params['requirements']);
            $fields[] = "requirements = '$requirements'";
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
            $insert = "INSERT INTO civicrm_program SET ".implode(", ", $fields);
            CRM_Core_DAO::executeQuery($insert);
            $query = "SELECT MAX(id) AS latest_id FROM civicrm_program";
            $dao = CRM_Core_DAO::executeQuery($query);
            if ($dao->fetch()) {
                $programId = $dao->latest_id;
            }
        }
        return $programId;
    }
    /**
     * Function to update program
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Fe 2014
     * @param array $params
     * @return array $result
     * @throws Exception when required params not found or empty
     * @throws Exception when program_id param is not numeric
     * @throws Exception when no program with id found
     * @throws Exception when contact_id (manager) is not numeric
     * @access public
     * @static
     */
    public static function update($params) {
        $result = array();
        /*
         * array with mandatory parameters
         */
        $mandatoryFields = array("program_id", "title");
        if (!self::checkMandatoryFields($mandatoryFields, $params)) {
            throw new Exception("Missing or empty mandatory params ".
                implode("; ", $mandatoryFields));
            return $result;
        }
        if (!is_numeric($params['program_id'])) {
            throw new Exception("Program_id has to be numeric, now contains ".
                $params['program_id']);
            return $result;
        }
         $programId = $params['program_id'];
        /*
         * check if program exists
         */
        $checkQuery = "SELECT COUNT(*) AS count_program FROM civicrm_program 
            WHERE id = $programId";
        $daoCheck = CRM_Core_DAO::executeQuery($checkQuery);
        if ($daoCheck->fetch()) {
            if ($daoCheck->count_program == 0) {
                throw new Exception("No program found with program_id $programId");
            }
        }
        
        $fields = array();
        
        $title = CRM_Core_DAO::escapeString($params['title']);
        $fields[] = "title = '$title'";
        
        if (isset($params['description'])) {
            $description = CRM_Core_DAO::escapeString($params['description']);
            $fields[] = "description = '$description'";
        }

        if (isset($params['contact_id_manager'])) {
            if (!is_numeric($params['contact_id_manager'])) {
                throw new Exception("Param contact_id_manager has to be numeric 
                    but holds ".$params['contact_id_manager']);
                return $programId;
            } else {
                $fields[] = "contact_id_manager = {$params['contact_id_manager']}";
            }
        }

        if (isset($params['budget'])) {
            if (!is_numeric($params['budget'])) {
                throw new Exception("Param budget has to be numeric but holds ".
                    $params['budget']);
                return $programId;
            } else {
                $fields[] = "budget = {$params['budget']}";
            }
        }
        
        if (isset($params['goals'])) {
            $goals = CRM_Core_DAO::escapeString($params['goals']);
            $fields[] = "goals = '$goals'";
        }
        
        if (isset($params['requirements'])) {
            $requirements = CRM_Core_DAO::escapeString($params['requirements']);
            $fields[] = "requirements = '$requirements'";
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
            $update = "UPDATE civicrm_program SET ".implode(", ", $fields).
                " WHERE id = $programId";
            CRM_Core_DAO::executeQuery($update);
            
            $result = self::getProgramById($programId);
        }
        return $result;
    }
    /**
     * Function to delete program
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param int $program_id
     * @return void
     * @throws Exception when program_id is empty
     * @throws Exception when program_id is not numeric
     * @access public
     * @static
     */
    public static function delete($programId) {
        if (empty($programId) || !is_numeric($programId)) {
            throw new Exception("Program_id can not be empty and has to be numeric");
        }
        $delete = "DELETE FROM civicrm_program WHERE id = $programId";
        CRM_Core_DAO::executeQuery($delete);
        return;
    }
    /**
     * Function to disable a program
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param int $program_id
     * @return void
     * @throws Exception when program_id is empty
     * @throws Exception when program_id is not numeric
     * @access public
     * @static
     */
    public static function disable($programId) {
        return;
        if (empty($programId) || !is_numeric($programId)) {
            throw new Exception("Program_id can not be empty and has to be numeric");
        }
        $update = "UPDATE civicrm_program SET is_active = 0 WHERE id = $programId";
        CRM_Core_DAO::executeQuery($update);
        return;
    }
    /**
     * Function to retrieve all projects for a program
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param array $params holding program_id or title
     * @return array $result
     * @access public
     * @static
     */
    public static function getAllProjects($params) {
        $result = array();
        /*
         * program_id or title is mandatory
         */
        if (!isset($params['program_id']) || !isset($params['title'])) {
            throw new Exception("Params has to contain program_id or title");
            return $result;
        }
        /*
         * program_id has to be numeric and can not be empty if set
         */
        if (isset($params['program_id'])) {
            if (empty($params['program_id']) || !is_numeric($params['program_id'])) {
                throw new Exception("Program_id can not be empty and has to be 
                    numeric, now contains ".$params['program_id']);
            }
            $programId = $params['program_id'];
        }
        /*
         * title can not be empty if set and no program_id
         */
        if (!isset($params['program_id']) && isset($params['title'])) {
            if (empty($params['title'])) {
                throw new Exception("Title can not be empty");
                return $result;
            }
            $programId = self::getProgramIdWithTitle($params['title']);
        }
        $result = CRM_Threepeas_PumProject::getAllProjectsByProgramId($programId);
        return $result;
    }
    /**
     * Function to get the program id with title
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param string $title
     * @return int $program_id
     * @access public
     * @static
     */
    public static function getProgramIdWithTitle($title) {
        $programId = 0;
        if (empty($title)) {
            return $programId;
        }
        $title = CRM_Core_DAO::escapeString($title);
        $query = "SELECT id FROM civicrm_program WHERE title = '$title'";
        $dao = CRM_Core_DAO::executeQuery($query);
        if ($dao->fetch()) {
            $programId = $dao->id;
        }
        return $programId;
    }
    /**
     * Function to populate array with dao
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
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
        if (isset($dao->description)) {
            $result['description'] = $dao->description;
        }
        if (isset($dao->contact_id_manager)) {
            $result['contact_id_manager'] = $dao->contact_id_manager;
        }
        if (isset($dao->budget)) {
            $result['budget'] = $dao->budget;
        }
        if (isset($dao->goals)) {
            $result['goals'] = $dao->goals;
        }
        if (isset($dao->requirements)) {
            $result['requirements'] = $dao->requirements;
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
     * Function to check if mandatory fields are in params
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param array $mandatoryFields
     * @param array $params
     * @return boolean (TRUE if OK, FALSE if error
     * @access private
     * @static
     */
    private static function checkMandatoryFields($mandatoryFields, $params) {
        foreach ($mandatoryFields as $mandatoryField) {
            if (!isset($params[$mandatoryField])) {
                return FALSE;
            } else {
                if (empty($params[$mandatoryField])) {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }
}

