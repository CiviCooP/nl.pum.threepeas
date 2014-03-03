<?php

/**
 * Class PumProgramme for dealing with programmes (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
 */
class CRM_Threepeas_PumProgramme {
    /**
     * Constructor function
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 3 Feb 2014
     */
    function __construct() {
    }
    /**
     * Function to retrieve all programmes
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @return array $result with data
     * @access public
     * @static
     */
    public static function getAllProgrammes() {
        $result = array();
        $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_programme");
        while ($dao->fetch()) {
            $result[$dao->id] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to retrieve all active programmes
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @return array $result with data
     * @access public
     * @static
     */
    public static function getAllActiveProgrammes() {
        $result = array();
        $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_programme WHERE is_active = 1");
        while ($dao->fetch()) {
            $result[$dao->id] = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to retrieve single programme with programme_id
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @param int $programmeId
     * @return array $result
     * @access public
     * @static
     */
    public static function getProgrammeById($programmeId) {
        $result = array();
        if (empty($programmeId) || !is_numeric($programmeId)) {
            return $result;
        }
        $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_programme 
            WHERE id = $programmeId");
        if ($dao->fetch()) {
            $result = self::_daoToArray($dao);
        }
        return $result;
    }
    /**
     * Function to add programme
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param array $params
     * @return int $programmeId (id fo the created programme)
     * @throws Exception when required param missing or empty
     * @throws Exception when programme with title already exists in DB
     * @throws Exception when contact_id (manager) is not numeric
     * @access public
     * @static
     */
    public static function add($params) {
        $programmeId = 0;
        /*
         * array with required parameters
         */
        $mandatoryFields = array("title");
        if (!CRM_Utils_ThreepeasUtils::checkMandatoryFields($mandatoryFields, $params)) {
            throw new Exception("Missing or empty mandatory params ".
                implode("; ", $mandatoryFields));
            return $programmeId;
        }
        /*
         * check if title does not exist yet (has to be unique)
         */
        $title = CRM_Core_DAO::escapeString($params['title']);
        $query = "SELECT COUNT(*) AS count_title FROM civicrm_programme 
            WHERE title = '$title'";
        $dao = CRM_Core_DAO::executeQuery($query);
        if ($dao->fetch()) {
            if ($dao->count_title > 0) {
                throw new Exception("Programme with title $title already exists");
                return $programmeId;
            }
        }
        
        $fields = array();
        $fields[] = "title = '$title'";

        if (isset($params['description'])) {
            $description = CRM_Core_DAO::escapeString($params['description']);
            $fields[] = "description = '$description'";
        }

        if (isset($params['contact_id_manager'])) {
            if (!empty($params['contact_id_manager'])) {
                if (!is_numeric($params['contact_id_manager'])) {
                    throw new Exception("Param contact_id_manager has to be numeric 
                        but holds ".$params['contact_id_manager']);
                    return $programmeId;
                } else {
                    $fields[] = "contact_id_manager = {$params['contact_id_manager']}";
                }
            }
        }

        if (isset($params['budget'])) {
            if (!empty($params['budget'])) {
                if (!is_numeric($params['budget'])) {
                    throw new Exception("Param budget has to be numeric but holds ".
                        $params['budget']);
                    return $programmeId;
                } else {
                    $fields[] = "budget = {$params['budget']}";
                }
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
            $insert = "INSERT INTO civicrm_programme SET ".implode(", ", $fields);
            CRM_Core_DAO::executeQuery($insert);
            $query = "SELECT MAX(id) AS latest_id FROM civicrm_programme";
            $dao = CRM_Core_DAO::executeQuery($query);
            if ($dao->fetch()) {
                $programmeId = $dao->latest_id;
            }
        }
        return $programmeId;
    }
    /**
     * Function to update programme
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param array $params
     * @return array $result
     * @throws Exception when required params not found or empty
     * @throws Exception when programme_id param is not numeric
     * @throws Exception when no programme with id found
     * @throws Exception when contact_id (manager) is not numeric
     * @access public
     * @static
     */
    public static function update($params) {
        $result = array();
        /*
         * array with mandatory parameters
         */
        $mandatoryFields = array("programme_id", "title");
        if (!CRM_Utils_ThreepeasUtils::checkMandatoryFields($mandatoryFields, $params)) {
            throw new Exception("Missing or empty mandatory params ".
                implode("; ", $mandatoryFields));
            return $result;
        }
        if (!is_numeric($params['programme_id'])) {
            throw new Exception("Programme_id has to be numeric, now contains ".
                $params['programme_id']);
            return $result;
        }
         $programmeId = $params['programme_id'];
        /*
         * check if programme exists
         */
        $checkQuery = "SELECT COUNT(*) AS count_programme FROM civicrm_programme 
            WHERE id = $programmeId";
        $daoCheck = CRM_Core_DAO::executeQuery($checkQuery);
        if ($daoCheck->fetch()) {
            if ($daoCheck->count_programme == 0) {
                throw new Exception("No programme found with programme_id $programmeId");
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
                return $programmeId;
            } else {
                $fields[] = "contact_id_manager = {$params['contact_id_manager']}";
            }
        }

        if (isset($params['budget'])) {
            if (!is_numeric($params['budget'])) {
                throw new Exception("Param budget has to be numeric but holds ".
                    $params['budget']);
                return $programmeId;
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
            $update = "UPDATE civicrm_programme SET ".implode(", ", $fields).
                " WHERE id = $programmeId";
            CRM_Core_DAO::executeQuery($update);
            
            $result = self::getProgrammeById($programmeId);
        }
        return $result;
    }
    /**
     * Function to delete programme
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param int $programmeId
     * @return void
     * @throws Exception when programmeId is empty
     * @throws Exception when programmeId is not numeric
     * @access public
     * @static
     */
    public static function delete($programmeId) {
        if (empty($programmeId) || !is_numeric($programmeId)) {
            throw new Exception("Programme_id can not be empty and has to be numeric");
        }
        $delete = "DELETE FROM civicrm_programme WHERE id = $programmeId";
        CRM_Core_DAO::executeQuery($delete);
        return;
    }
    /**
     * Function to disable a programme
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param int $programmeId
     * @return void
     * @throws Exception when programmeId is empty
     * @throws Exception when programmeId is not numeric
     * @access public
     * @static
     */
    public static function disable($programmeId) {
        if (empty($programmeId) || !is_numeric($programmeId)) {
            throw new Exception("Programme_id can not be empty and has to be numeric");
        }
        $update = "UPDATE civicrm_programme SET is_active = 0 WHERE id = $programmeId";
        CRM_Core_DAO::executeQuery($update);
        return;
    }
    /**
     * Function to retrieve all projects for a programme
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param array $params holding programme_id or title
     * @return array $result
     * @access public
     * @static
     */
    public static function getAllProjects($params) {
        $result = array();
        /*
         * programme_id or title is mandatory
         */
        if (!isset($params['programme_id']) || !isset($params['title'])) {
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
         * title can not be empty if set and no programme_id
         */
        if (!isset($params['programme_id']) && isset($params['title'])) {
            if (empty($params['title'])) {
                throw new Exception("Title can not be empty");
                return $result;
            }
            $programmeId = self::getProgrammeIdWithTitle($params['title']);
        }
        if ($programmeId) {
            $result = CRM_Threepeas_PumProject::getAllProjectsByProgrammeId($programmeId);
            return $result;
        }
    }
    /**
     * Function to get the programme id with title
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param string $title
     * @return int $programmeId
     * @access public
     * @static
     */
    public static function getProgrammeIdWithTitle($title) {
        $programmeId = 0;
        if (empty($title)) {
            return $programmeId;
        }
        $title = CRM_Core_DAO::escapeString($title);
        $query = "SELECT id FROM civicrm_programme WHERE title = '$title'";
        $dao = CRM_Core_DAO::executeQuery($query);
        if ($dao->fetch()) {
            $programmeId = $dao->id;
        }
        return $programmeId;
    }
    /**
     * Function to get the programme title with id
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param string $title
     * @return int $programmeId
     * @access public
     * @static
     */
    public static function getProgrammeTitleWithId($programmeId) {
        $programmeTitle = "";
        if (empty($programmeId) || !is_numeric($programmeId)) {
            return $programmeTitle;
        }
        $query = "SELECT title FROM civicrm_programme WHERE id = $programmeId";
        $dao = CRM_Core_DAO::executeQuery($query);
        if ($dao->fetch()) {
            $programmeTitle = $dao->title;
        }
        return $programmeTitle;
    }
    /**
     * Function to check if a programme can be deleted. Will return TRUE
     * if there are no project children for the programme
     * 
     * @author Erik Hommel
     * @date 18 Feb 2014
     * @param int $programmeId
     * @return boolean
     * @throws Exception when programmeId non-numeric or empty
     * @access public
     * @static
     */
    public static function checkProgrammeDeletable($programmeId) {
        if (empty($programmeId) || !is_numeric($programmeId)) {
            throw new Exception("ProgrammeId can not be empty or non numeric to check 
                if the programme can be deletend");
            return FALSE;
        }
        $programmeProjects = CRM_Threepeas_PumProject::getAllProjectsByProgrammeId($programmeId);
        if (empty($programmeProjects)) {
            return TRUE;
        } else {
            return FALSE;
        }
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
}