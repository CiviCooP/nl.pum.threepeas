<?php

/**
 * Class PumProgram for dealing with programs (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 14 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
 */
class CRM_Threepeas_PumProgramDivision {
    /**
     * Constructor function
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 14 Feb 2014
     */
    function __construct() {
    }
    /**
     * Function to retrieve programdivision with program_id
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 14 Feb 2014
     * @param int $program_id
     * @return array $result with data
     * @throws Exception when programId empty or not numeric
     * @access public
     * @static
     */
    public static function getProgramDivisionByProgramDivisionId($programDivisionId) {
        $result = array();
        if (empty($programDivisionId) || !is_numeric($programDivisionId)) {
            throw new Exception("ProgramId has to be numeric and can not be empty");
        }
        $query = "SELECT * FROM civicrm_program_division WHERE id = $programDivisionId";
        $dao = CRM_Core_DAO::executeQuery($query);
        while ($dao->fetch()) {
            $result[$dao->id] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to add program division
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 14 Feb 2014
     * @param array $params
     * @return int $program_division_id (id fo the created program division)
     * @throws Exception when required param missing or empty
     * @throws Exception when program_id, min_budget, max_budget, min_projects 
     *         or max_projects not numeric when they have a value
     * @access public
     * @static
     */
    public static function add($params) {
        $programDivisionId = 0;
        /*
         * array with required parameters
         */
        $mandatoryFields = array("program_id", "country_id");
        if (!CRM_Utils_ThreepeasUtils::checkMandatoryFields($mandatoryFields, $params)) {
            throw new Exception("Missing or empty mandatory params ".
                implode("; ", $mandatoryFields));
            return $programDivisionId;
        }
        /*
         * check if fields are numeric when set
         */
        $numericFields = array("program_id", "country_id", "min_budget", "max_budget", 
            "min_projects", "max_projects");
        if (!CRM_Utils_ThreepeasUtils::checkNumericFields($numericFields, $params)) {
            throw new Exception("Fields ".implode(", ", $numericFields)." have to be numeric");
            return $programDivisionId;
        }       
        $fields = array();
        $fields[] = "program_id = {$params['program_id']}";
        $fields[] = "country_id = {$params['country_id']}";

        if (isset($params['min_projects'])) {
            $fields[] = "min_projects = {$params['min_projects']}";
        }      

        if (isset($params['max_projects'])) {
            $fields[] = "max_projects = {$params['max_projects']}";
        }      

        if (isset($params['min_budget'])) {
            $fields[] = "min_budget = {$params['min_budget']}";
        }      

        if (isset($params['max_budget'])) {
            $fields[] = "max_budget = {$params['max_budget']}";
        }      
        
        if (!empty($fields)) {
            $insert = "INSERT INTO civicrm_program_division SET ".implode(", ", $fields);
            CRM_Core_DAO::executeQuery($insert);
            $query = "SELECT MAX(id) AS latest_id FROM civicrm_program_division";
            $dao = CRM_Core_DAO::executeQuery($query);
            if ($dao->fetch()) {
                $programDivisionId = $dao->latest_id;
            }
        }
        return $programDivisionId;
    }
    /**
     * Function to update program division
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 14 Feb 2014
     * @param array $params
     * @return array $result
     * @throws Exception when required params not found or empty
     * @throws Exception when program_division_id, program_id, country_id,
     *         min_projects, max_projects, min_budget, max_budget not numeric
     * @throws Exception when no program division with id found
     * @access public
     * @static
     */
    public static function update($params) {
        $result = array();
        /*
         * array with mandatory parameters
         */
        $mandatoryFields = array("program_division_id", "program_id", "country_id");
        if (!CRM_Utils_ThreepeasUtils::checkMandatoryFields($mandatoryFields, $params)) {
            throw new Exception("Missing or empty mandatory params ".
                implode("; ", $mandatoryFields));
            return $result;
        }
        /*
         * check if fields are numeric when set
         */
        $numericFields = array("program_id", "country_id", "min_budget", "max_budget", 
            "min_projects", "max_projects");
        if (!CRM_Utils_ThreepeasUtils::checkNumericFields($numericFields, $params)) {
            throw new Exception("Fields ".implode(", ", $numericFields)." have to be numeric");
            return $programDivisionId;
        }       
        $programDivisionId = $params['program_division_id'];
        $programId = $params['program_id'];
        $countryId = $params['country_id'];
        /*
         * check if program division exists
         */
        $checkQuery = "SELECT COUNT(*) AS count_program_division FROM 
            civicrm_program_division WHERE id = $programDivisionId";
        $daoCheck = CRM_Core_DAO::executeQuery($checkQuery);
        if ($daoCheck->fetch()) {
            if ($daoCheck->count_program_division == 0) {
                throw new Exception("No program division found with program_id 
                    $programDivisionId");
            }
        }
        
        $fields = array();
        
        $fields[] = "program_id = $programId";
        $fields[] = "country_id = $countryId";
        
        if (isset($params['min_projects'])) {
            $fields[] = "min_projects = {$params['min_projects']}";
        }

        if (isset($params['max_projects'])) {
            $fields[] = "max_projects = {$params['max_projects']}";
        }

        if (isset($params['min_budget'])) {
            $fields[] = "min_budget = {$params['min_budget']}";
        }

        if (isset($params['max_budget'])) {
            $fields[] = "max_budget = {$params['max_budget']}";
        }

        
        if (!empty($fields)) {
            $update = "UPDATE civicrm_program_division SET ".implode(", ", $fields).
                " WHERE id = $programDivisionId";
            CRM_Core_DAO::executeQuery($update);
            
            $result = self::getProgramDivisionByProgramDivisionId($programDivisionId);
        }
        return $result;
    }
    /**
     * Function to delete program division
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 14 Feb 2014
     * @param int $program_division_id
     * @return void
     * @throws Exception when program_division_id is empty
     * @throws Exception when program_division_id is not numeric
     * @access public
     * @static
     */
    public static function delete($programDivisionId) {
        if (empty($programDivisionId) || !is_numeric($programDivisionId)) {
            throw new Exception("Program_division_id can not be empty and has to be numeric");
        }
        $delete = "DELETE FROM civicrm_program_division WHERE id = $programDivisionId";
        CRM_Core_DAO::executeQuery($delete);
        return;
    }
    /**
     * Function to retrieve all program divisions for a program
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param array $params holding program_id or title
     * @return array $result
     * @access public
     * @static
     */
    public static function getAllProgamDivisionsForProgram($params) {
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
            $programId = CRM_Threepeas_PumProgram::getProgramIdWithTitle($params['title']);
        }
        $query = "SELECT * FROM civicrm_program_division WHERE program_id = $programId";
        $dao = CRM_Core_DAO::executeQuery($query);
        while ($dao->fetch()) {
            $result[$dao->id] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to retrieve all program divisions for a country
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param int $countryId
     * @return array $result
     * @access public
     * @static
     */
    public static function getAllProgamDivisionsForCountry($country_id) {
        $result = array();
        /*
         * country_id has to be numeric and can not be empty if set
         */
        if (empty($countryId) || !is_numeric($countryId)) {
            throw new Exception("Country_id can not be empty and has to be 
                numeric");
        }
        $query = "SELECT * FROM civicrm_program_division WHERE country_id = $countryId";
        $dao = CRM_Core_DAO::executeQuery($query);
        while ($dao->fetch()) {
            $result[$dao->id] = self::_daoToArray($dao);
        }
        return $result;
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
        if (isset($dao->program_id)) {
            $result['program_id'] = $dao->program_id;
        }
        if (isset($dao->country_id)) {
            $result['country_id'] = $dao->country_id;
        }
        if (isset($dao->min_projects)) {
            $result['min_projects'] = $dao->min_projects;
        }
        if (isset($dao->max_projects)) {
            $result['max_projects'] = $dao->max_projects;
        }
        if (isset($dao->min_budget)) {
            $result['min_budget'] = $dao->min_budget;
        }
        if (isset($dao->max_budget)) {
            $result['max_budget'] = $dao->max_budget;
        }
        return $result;
    }
}

