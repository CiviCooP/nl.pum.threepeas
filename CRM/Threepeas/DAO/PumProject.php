<?php
/**
 * DAO PumProject for dealing with projects (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 16 Apr 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the AGPL-3.0
 */
class CRM_Threepeas_DAO_PumProject extends CRM_Core_DAO {
  
  /**
   * static instance to hold the field values
   *
   * @var array
   * @static
   */
  static $_fields = null;
  static $_export = null;
  
  /**
   * empty definition for virtual function
   */
  static function getTableName() {
    return 'civicrm_project';
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
        'title' => array(
          'name' => 'title',
          'type' => CRM_Utils_Type::T_STRING,
          'required' => true,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE
        ) ,
        'programme_id' => array(
          'name' => 'programme_id',
          'type' => CRM_Utils_Type::T_INT
        ) ,
        'reason' => array(
          'name' => 'reason',
          'type' => CRM_Utils_Type::T_TEXT
        ) ,
        'work_description' => array(
          'name' => 'work_description',
          'type' => CRM_Utils_Type::T_TEXT
        ) ,
        'qualifications' => array(
          'name' => 'qualifications',
          'type' => CRM_Utils_Type::T_TEXT
        ) ,
        'expected_results' => array(
          'name' => 'expected_results',
          'type' => CRM_Utils_Type::T_TEXT
        ) ,
        'projectplan' => array(
          'name' => 'projectplan',
          'type' => CRM_Utils_Type::T_TEXT
        ),
        'customer_id' => array(
          'name' => 'customer_id',
          'type' => CRM_Utils_Type::T_INT
        ),
        'country_id' => array(
          'name' => 'country_id',
          'type' => CRM_Utils_Type::T_INT
        ),
        'projectmanager_id' => array(
          'name' => 'projectmanager_id',
          'type' => CRM_Utils_Type::T_INT
        ),
        'anamon_id' => array(
          'name' => 'anamon_id',
          'type' => CRM_Utils_Type::T_INT
        ),
        'country_coordinator_id' => array(
          'name' => 'country_coordinator_id',
          'type' => CRM_Utils_Type::T_INT
        ),
        'project_officer_id' => array(
          'name' => 'project_officer_id',
          'type' => CRM_Utils_Type::T_INT
        ),
        'sector_coordinator_id' => array(
          'name' => 'sector_coordinator_id',
          'type' => CRM_Utils_Type::T_INT
        ),
        'start_date' => array(
          'name' => 'start_date',
          'type' => CRM_Utils_Type::T_DATE,
        ) ,
        'end_date' => array(
          'name' => 'end_date',
          'type' => CRM_Utils_Type::T_DATE,
        ) ,
        'is_active' => array(
          'name' => 'is_active',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'default' => '1',
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
        'id'                    =>  'id',
        'title'                 =>  'title',
        'programme_id'          =>  'programme_id',
        'reason'                =>  'reason',
        'qualifications'        =>  'qualifications',
        'expected_results'      =>  'expected_results',
        'projectplan'           =>  'projectplan',
        'customer_id'           =>  'customer_id',
        'country_id'            =>  'country_id',
        'projectmanager_id'     =>  'projectmanager_id',
        'anamon_id'             =>  'anamon_id',
        'country_coordinator_id'=>  'country_coordinator_id',
        'project_officer_id'    =>  'project_officer_id',
        'sector_coordinator_id' =>  'sector_coordinator_id',
        'start_date'            =>  'start_date',
        'end_date'              =>  'end_date',
        'is_active'             =>  'is_active'
      );
    }
    return self::$_fieldKeys;
  }
  /**
   * returns the list of fields that can be exported
   *
   * @access public
   * return array
   * @static
   */
  static function &export($prefix = false)
  {
    if (!(self::$_export)) {
      self::$_export = array();
      $fields = self::fields();
      foreach($fields as $name => $field) {
        if (CRM_Utils_Array::value('export', $field)) {
          if ($prefix) {
            self::$_export['activity'] = & $fields[$name];
          } else {
            self::$_export[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_export;
  }
}