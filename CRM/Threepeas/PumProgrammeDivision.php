<?php

/**
 * Class PumProgrammeDivision for dealing with budget divisions for programmes (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 14 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
 */
class CRM_Threepeas_PumProgrammeDivision {
    /**
     * Constructor function
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 14 Feb 2014
     */
    function __construct() {
    }
    /**
     * Function to retrieve programmedivisions with programmeDivisionId
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 14 Feb 2014
     * @param int $programmeDivisionId
     * @return array $result with data
     * @throws Exception when programmeDivisionId empty or not numeric
     * @access public
     * @static
     */
    public static function getProgrammeDivisionByProgrammeDivisionId($programmeDivisionId) {
        $result = array();
        if (empty($programmeDivisionId) || !is_numeric($programmeDivisionId)) {
            throw new Exception("ProgrammeDivisionId has to be numeric and can not be empty");
        }
        $query = "SELECT * FROM civicrm_programme_division WHERE id = $programmeDivisionId";
        $dao = CRM_Core_DAO::executeQuery($query);
        while ($dao->fetch()) {
            $result[$dao->id] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to add programme division
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 14 Feb 2014
     * @param array $params
     * @return int $programmeDivisionId (id fo the created programme division)
     * @throws Exception when required param missing or empty
     * @throws Exception when programme_id, min_budget, max_budget, min_projects 
     *         or max_projects not numeric when they have a value
     * @access public
     * @static
     */
    public static function add($params) {
        $programmeDivisionId = 0;
        /*
         * array with required parameters
         */
        $mandatoryFields = array("programme_id", "country_id");
        if (!CRM_Utils_ThreepeasUtils::checkMandatoryFields($mandatoryFields, $params)) {
            throw new Exception("Missing or empty mandatory params ".
                implode("; ", $mandatoryFields));
            return $programmeDivisionId;
        }
        /*
         * check if fields are numeric when set
         */
        $numericFields = array("programme_id", "country_id", "min_budget", "max_budget", 
            "min_projects", "max_projects");
        if (!CRM_Utils_ThreepeasUtils::checkNumericFields($numericFields, $params)) {
            throw new Exception("Fields ".implode(", ", $numericFields)." have to be numeric");
            return $programmeDivisionId;
        }       
        $fields = array();
        $fields[] = "programme_id = {$params['programme_id']}";
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
            $insert = "INSERT INTO civicrm_programme_division SET ".implode(", ", $fields);
            CRM_Core_DAO::executeQuery($insert);
            $query = "SELECT MAX(id) AS latest_id FROM civicrm_programme_division";
            $dao = CRM_Core_DAO::executeQuery($query);
            if ($dao->fetch()) {
                $programmeDivisionId = $dao->latest_id;
            }
        }
        return $programmeDivisionId;
    }
    /**
     * Function to update programme division
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 14 Feb 2014
     * @param array $params
     * @return array $result
     * @throws Exception when required params not found or empty
     * @throws Exception when programme_division_id, programme_id, country_id,
     *         min_projects, max_projects, min_budget, max_budget not numeric
     * @throws Exception when no programme division with id found
     * @access public
     * @static
     */
    public static function update($params) {
        $result = array();
        /*
         * array with mandatory parameters
         */
        $mandatoryFields = array("programme_division_id", "programme_id", "country_id");
        if (!CRM_Utils_ThreepeasUtils::checkMandatoryFields($mandatoryFields, $params)) {
            throw new Exception("Missing or empty mandatory params ".
                implode("; ", $mandatoryFields));
            return $result;
        }
        /*
         * check if fields are numeric when set
         */
        $numericFields = array("programme_id", "country_id", "min_budget", "max_budget", 
            "min_projects", "max_projects");
        if (!CRM_Utils_ThreepeasUtils::checkNumericFields($numericFields, $params)) {
            throw new Exception("Fields ".implode(", ", $numericFields)." have to be numeric");
            return $programmeDivisionId;
        }       
        $programmeDivisionId = $params['programe_division_id'];
        $programmeId = $params['programme_id'];
        $countryId = $params['country_id'];
        /*
         * check if program division exists
         */
        $checkQuery = "SELECT COUNT(*) AS count_programme_division FROM 
            civicrm_programme_division WHERE id = $programmeDivisionId";
        $daoCheck = CRM_Core_DAO::executeQuery($checkQuery);
        if ($daoCheck->fetch()) {
            if ($daoCheck->count_programme_division == 0) {
                throw new Exception("No programme division found with programme_id 
                    $programmeDivisionId");
            }
        }
        
        $fields = array();
        
        $fields[] = "programme_id = $programmeId";
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
            $update = "UPDATE civicrm_programme_division SET ".implode(", ", $fields).
                " WHERE id = $programmeDivisionId";
            CRM_Core_DAO::executeQuery($update);
            
            $result = self::getProgrammeDivisionByProgrammeDivisionId($programmeDivisionId);
        }
        return $result;
    }
    /**
     * Function to delete programme division
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 14 Feb 2014
     * @param int $programmeDivisionId
     * @return void
     * @throws Exception when $programmeDivisionId is empty
     * @throws Exception when $programmeDivisionId is not numeric
     * @access public
     * @static
     */
    public static function delete($programmeDivisionId) {
        if (empty($programmeDivisionId) || !is_numeric($programmeDivisionId)) {
            throw new Exception("programmaDivisionId can not be empty and has to be numeric");
        }
        $delete = "DELETE FROM civicrm_programme_division WHERE id = $programmeDivisionId";
        CRM_Core_DAO::executeQuery($delete);
        return;
    }
    /**
     * Function to retrieve all programme divisions for a programme
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param array $params holding programme_id or title
     * @return array $result
     * @access public
     * @static
     */
    public static function getAllProgrammeDivisionsForProgram($params) {
        $result = array();
        /*
         * programme_id or title is mandatory
         */
        if (!isset($params['programme_id']) && !isset($params['title'])) {
            throw new Exception("Params has to contain programme_id or title");
            return $result;
        }
        /*
         * programme_id has to be numeric and can not be empty if set
         */
        if (isset($params['programme_id'])) {
            if (empty($params['programme_id']) || !is_numeric($params['programme_id'])) {
                throw new Exception("Programme_id can not be empty and has to be 
                    numeric, now contains ".$params['programme_id']);
            }
            $programmeId = $params['programme_id'];
        }
        /*
         * title can not be empty if set and no program_id
         */
        if (!isset($params['programme_id']) && isset($params['title'])) {
            if (empty($params['title'])) {
                throw new Exception("Title can not be empty");
                return $result;
            }
            $programmeId = CRM_Threepeas_PumProgramme::getProgrammeIdWithTitle($params['title']);
        }
        $query = "SELECT * FROM civicrm_programme_division WHERE programme_id = $programmeId";
        $dao = CRM_Core_DAO::executeQuery($query);
        while ($dao->fetch()) {
            $result[$dao->id] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to retrieve all programme divisions for a country
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param int $countryId
     * @return array $result
     * @access public
     * @static
     */
    public static function getAllProgrammeDivisionsForCountry($country_id) {
        $result = array();
        /*
         * country_id has to be numeric and can not be empty if set
         */
        if (empty($countryId) || !is_numeric($countryId)) {
            throw new Exception("Country_id can not be empty and has to be 
                numeric");
        }
        $query = "SELECT * FROM civicrm_programme_division WHERE country_id = $countryId";
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
        if (isset($dao->programme_id)) {
            $result['programme_id'] = $dao->programme_id;
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

