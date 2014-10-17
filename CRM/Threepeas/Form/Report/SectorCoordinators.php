<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM specific report for PUM <www.pum.nl>                       |
 | part of extension nl.pum.threepeas                                 |
 |                                                                    |
 | @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>          |
 | @date 17 Oct 2014                                                  |
 | Shows Sector Coordinators for Tags (Sector)                        |
 +--------------------------------------------------------------------+
 |                                                                    |
 | Copyright (C) 2014 Coöperatieve CiviCooP U.A.                      |
 | <http://www.civicoop.org>                                          |
 | Licensed to PUM <http://www.pum.nl> and CiviCRM under the          |
 | Academic Free License version 3.0.                                 |
 | <http://opensource.org/licenses/AFL-3.0>                           |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_Threepeas_Form_Report_SectorCoordinators extends CRM_Report_Form {
  protected $_summary = NULL;
  protected $_emailField = FALSE;
  protected $_phoneField = FALSE;
  protected $_genderOptionGroupId = NULL;

  function __construct() {
    $this->setGenderOptionGroupId();
    $this->_add2groupSupported = FALSE;
    $this->_columns = array(
      'civicrm_contact' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
        array(
          'display_name' =>
          array('title' => ts('Contact Name'),
            'required' => TRUE,
            'no_repeat' => TRUE,
          ),
          'is_deceased' =>
          array('title' => ts('Deceased'),
            'required' => TRUE,
          ),
          'gender_id' =>
          array('title' => ts('Gender'),
          ),
          'birth_date' =>
          array('title' => ts('Birth Date'),
          ),
        ),
      ),
      'civicrm_email' =>
      array(
        'dao' => 'CRM_Core_DAO_Email',
        'fields' =>
        array(
          'email' =>
          array('title' => ts('Email'),
            'no_repeat' => TRUE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_phone' =>
      array(
        'dao' => 'CRM_Core_DAO_Phone',
        'fields' =>
        array(
          'phone' =>
          array('default' => TRUE),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_address' =>
      array(
        'dao' => 'CRM_Core_DAO_Address',
        'fields' =>
        array(
          'street_address' =>
          array('default' => TRUE),
          'postal_code' => 
          array('default' => TRUE),
          'city' =>
          array('default' => TRUE),
        ),
        'grouping' => 'contact-fields',
      ),
    );

    $this->_tagFilter = FALSE;
    parent::__construct();
  }
  
  function preProcess() {
    parent::preProcess();
  }

  static function formRule($fields, $files, $self) {
    $errors = $grouping = array();
    return $errors;
  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact']);
    
    /*
     * build WHERE clause using sector Tree
     */
    $rows = $graphRows = array();
    $this->buildRows('', $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }
  
  function buildRows($sql, &$rows) {
    $this->modifyColumnHeaders();
    $sectorLines = $this->getSectorTree();
    $rowNumber = 0;
    foreach ($sectorLines as $sectorId => $sectorLine) {
      $this->buildSectorRows($sectorId, $sectorLine, $rows, $rowNumber);      
    }
  }
  
  private function buildSectorRows($sectorId, $sectorName, &$rows, &$rowNumber) {
    $sectorRowNumber = 1;
    /*
     * get Coordinators
     */
    $sectorCoordinators = CRM_Enhancedtags_BAO_TagEnhanced::getValues(array('tag_id' => $sectorId));
    if (!empty($sectorCoordinators)) {
      foreach($sectorCoordinators as $sectorCoordinator) {
        $this->buildSingleSectorRow($sectorId, $sectorName, $sectorCoordinator, $rows, $sectorRowNumber, $rowNumber);
      }
    } else {
      $rows[$rowNumber]['sector_id'] = $sectorId;
      $rows[$rowNumber]['sector'] = $sectorName;
      $rowNumber++;
    }
  }
  
  private function buildSingleSectorRow($sectorId, $sectorName, $sectorCoordinator, &$rows, &$sectorRowNumber, &$rowNumber) {
    if ($sectorRowNumber == 1) {
      $rows[$rowNumber]['sector'] = $sectorName;
    } else {
      $rows[$rowNumber]['sector'] = '';      
    }
    $rows[$rowNumber]['sector_id'] = $sectorId;
    $rows[$rowNumber]['coordinator_id'] = $sectorCoordinator['coordinator_id'];
    $sectorRowNumber++;
    $contactData = civicrm_api3('Contact', 'Getsingle', array('id' => $sectorCoordinator['coordinator_id']));
    foreach($this->_params['fields'] as $fieldName => $switchedOn) {
      if (isset($contactData[$fieldName])) {
        $rows[$rowNumber][$fieldName] = $contactData[$fieldName];
      }
    }
    $rows[$rowNumber]['is_active'] = $sectorCoordinator['is_active'];
    if (isset($sectorCoordinator['start_date'])) {
      $rows[$rowNumber]['start_date'] = date('d-m-Y', strtotime($sectorCoordinator['start_date']));
    }
    if (isset($sectorCoordinator['end_date'])) {
      $rows[$rowNumber]['end_date'] = date('d-m-Y', strtotime($sectorCoordinator['end_date']));
    }
    $rowNumber++;
  }

  function modifyColumnHeaders() {
    $this->_columnHeaders['sector_id'] = array('title' => ts('Sector ID'), 'type' => 1, 'no_display' => true);
    $this->_columnHeaders['coordinator_id'] = array('title' => ts('Coordinator ID'), 'type' => 1, 'no_display' => true);
    $this->_columnHeaders['sector'] = array('title' => ts('Sector'), 'type' => 2);
    foreach ($this->_params['fields'] as $fieldName => $switchedOn) {
      switch ($fieldName) {
        case 'display_name':
          $this->_columnHeaders[$fieldName] = array('title' => ts('Coordinator'), 'type' => 2);
          break;
        case 'is_deceased':
          $this->_columnHeaders[$fieldName] = array('title' => ts('Deceased?'), 'type' => 2);
          break;
        case 'gender_id':
          $this->_columnHeaders[$fieldName] = array('title' => ts('Gender'), 'type' => 2);
          break;
        case 'birth_date':
          $this->_columnHeaders[$fieldName] = array('title' => ts('Birth Date'), 'type' => 2);
          break;
        case 'email':
          $this->_columnHeaders[$fieldName] = array('title' => ts('Email'), 'type' => 2);
          break;
        case 'phone':
          $this->_columnHeaders[$fieldName] = array('title' => ts('Phone'), 'type' => 2);
          break;
        case 'street_address':
          $this->_columnHeaders[$fieldName] = array('title' => ts('Address'), 'type' => 2);
          break;
        case 'postal_code':
          $this->_columnHeaders[$fieldName] = array('title' => ts('Postal Code'), 'type' => 2);
          break;
        case 'city':
          $this->_columnHeaders[$fieldName] = array('title' => ts('City'), 'type' => 2);
          break;
      }
    }
    $this->_columnHeaders['is_active'] = array('title' => ts('Active?'), 'type' => 2);
    $this->_columnHeaders['start_date'] = array('title' => ts('Start Date'), 'type' => 2);
    $this->_columnHeaders['end_date'] = array('title' => ts('End Date'), 'type' => 2);    
  }
  
  private function getSectorTree() {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $sectorTree = $threepeasConfig->getSectorTree();
    $contactTags = CRM_Core_BAO_Tag::getTags();
    foreach ($contactTags as $contactTagId => $contactTag) {
      if (!in_array($contactTagId, $sectorTree)) {
        unset($contactTags[$contactTagId]);
      }
    }
    return $contactTags;
  }

  function alterDisplay(&$rows) {
    foreach ($rows as $rowNum => $row) {
      $this->alterIsDeceased($row, $rowNum, $rows);
      $this->alterBirthDate($row, $rowNum, $rows);
      $this->alterGenderId($row, $rowNum, $rows);
      $this->alterIsActive($row, $rowNum, $rows);
      $this->alterCoordinator($row, $rowNum, $rows);
    }
  }
  
  private function alterCoordinator($row, $rowNum, &$rows) {
    if (array_key_exists('dislay_name', $row)) {
      $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.
        $row['coordinator_id'], $this->_absoluteUrl);
      $rows[$rowNum]['display_name_link'] = $url;
      $rows[$rowNum]['display_name_hover'] = 'Click to view coordinator details';
    }
  }
  
  private function alterIsDeceased($row, $rowNum, &$rows) {
    if (array_key_exists('is_deceased', $row)) {
      if ($row['is_deceased'] == 1) {
        $rows[$rowNum]['is_deceased'] = 'Yes';
      } else {
        $rows[$rowNum]['is_deceased'] = 'No';          
      }
    }
  }
  
  private function alterIsActive($row, $rowNum, &$rows) {
    if (array_key_exists('is_active', $row)) {
      if ($row['is_active'] == 1) {
        $rows[$rowNum]['is_active'] = 'Yes';
      } else {
        $rows[$rowNum]['is_active'] = 'No';          
      }
    }
  }

  
  private function alterBirthDate($row, $rowNum, &$rows) {
    if (array_key_exists('birth_date', $row)) {
      if (!empty($row['birth_date'])) {
        $rows[$rowNum]['birth_date'] = date('d-m-Y', strtotime($row['birth_date']));
      } else {
        $rows[$rowNum]['birth_date'] = '';
      }
    }
  }
  
  private function alterGenderId($row, $rowNum, &$rows) {
    if (array_key_exists('gender_id', $row)) {
      $rows[$rowNum]['gender_id'] = $this->setGender($row['gender_id']);
    }
  }
  
  private function setGenderOptionGroupId() {
    try {
      $this->_genderOptionGroupId = civicrm_api3('OptionGroup', 'Getvalue', array('name' => 'gender', 'return' => 'id'));
    } catch (CiviCRM_API3_Exception $ex) {
      $this->_genderOptionGroupId = null;
    }
  }
  private function setGender($genderId) {
    $params = array(
      'option_group_id' => $this->_genderOptionGroupId,
      'value' => $genderId,
      'return' => 'label'
    );
    try {
      $genderLabel = civicrm_api3('OptionValue', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $genderLabel = '';
    }
    return $genderLabel;
  }
}

