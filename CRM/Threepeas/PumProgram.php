<?php

/**
 * Class PumProgram for dealing with programs
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (erik.hommel@civicoop.org, http://www.civicoop.org)
 * @date 3 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A.
 * Licensed to PUM and CiviCRM under the Academic Free License version 3.0.
 */
class CRM_Threepeas_PumProgram {
    private $_table = "";
    public $id = 0;
    public $title = "";
    public $description = "";
    public $contact_id_manager = 0;
    public $budget = 0;
    public $goals = "";
    public $requirements = "";
    public $start_date = "";
    public $end_date = "";
    public $is_active = 0;
    /**
     * Constructor function
     * 
     * @author Erik Hommel (erik.hommel@civicoop.org)
     * @date 3 Feb 2014
     */
    function __construct() {
        $this->_table = "civicrm_program";
    }
}

