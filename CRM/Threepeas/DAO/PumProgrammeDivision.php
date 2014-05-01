<?php
/**
 * DAO PumProgrammeDivision for dealing with programme budget divisions (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 18 Apr 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
 */
class CRM_Threepeas_DAO_PumProgrammeDivision extends CRM_Core_DAO {
  
  /**
   * static instance to hold the field values
   *
   * @var array
   * @static
   */
  static $_fields = null;
  
  /**
   * empty definition for virtual function
   */
  static function getTableName() {
    return 'civicrm_programme_division';
  }
  
  /**
   * returns all the column names of this table
   *
   * @access public
   * @return array
   */
  static function &fields()
  {
    if (!(self::$_fields)) {
      self::$_fields = array(
        'id' => array(
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true
        ) ,
        'programme_id' => array(
          'name' => 'programme_id',
          'type' => CRM_Utils_Type::T_INT
        ) ,
        'country_id' => array(
          'name' => 'country_id',
          'type' => CRM_Utils_Type::T_INT
        ) ,
        'min_projects' => array(
          'name' => 'min_projects',
          'type' => CRM_Utils_Type::T_INT
        ) ,
        'max_projects' => array(
          'name' => 'max_projects',
          'type' => CRM_Utils_Type::T_INT
        ) ,
        'min_budget' => array(
          'name' => 'min_budget',
          'type' => CRM_Utils_Type::T_INT
        ) ,
        'max_budget' => array(
          'name' => 'max_budget',
          'type' => CRM_Utils_Type::T_INT,
        )
      );
    }
    return self::$_fields;
  }
  /**
   * Returns an array containing, for each field, the array key used for that
   * field in self::$_fields.
   *
   * @access public
   * @return array
   */
  static function &fieldKeys()
  {
    if (!(self::$_fieldKeys)) {
      self::$_fieldKeys = array(
        'id'          =>  'id',
        'programme_id'=>  'programme_id',
        'country_id'  =>  'country_id',
        'min_projects'=>  'min_projects',
        'max_projects'=>  'max_projects',
        'min_budget'  =>  'min_budget',
        'max_budget'  =>  'max_budget',
      );
    }
    return self::$_fieldKeys;
  }
  
  
}