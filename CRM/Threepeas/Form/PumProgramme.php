<?php
/**
 * Class PumProgramme for form processing of PUM Programme
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 16 Apr 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
 */
require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Threepeas_Form_PumProgramme extends CRM_Core_Form {
  
  protected $_programmeManagers = array();
  protected $_divisionCountries = array();
  
  /**
   * Function to build the form
   */  
  function buildQuickForm() {
    /*
     * retrieve Select List Options
     */
    $this->setOptionValues();
    /*
     * add form elements
     */
    $this->setFormElements();
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }
  /**
   * Function for processing before building the form
   */
  function preProcess() {
    /*
     * set user context to return to pumprogramme list
     */
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/programmelist'));
    if ($this->_action != CRM_Core_Action::ADD) {
      $this->_id = CRM_Utils_Request::retrieve('pid', 'Integer', $this);
    }
    /*
     * if action = delete, execute delete immediately
     */
    if ($this->_action == CRM_Core_Action::DELETE) {
      CRM_Threepeas_BAO_PumProgramme::deleteById($this->_id);
      $session->setStatus('Programme deleted', 'Delete', 'success');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/programmelist'));
    }
    /*
     * if action = disable or enable, execute immediately
     */
    if ($this->_action == CRM_Core_Action::DISABLE || $this->_action == CRM_Core_Action::ENABLE) {
      $this->processAble();
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/programmelist'));
    }
    /*
     * if action is not add, retrieve budget divisions
     */
    if ($this->_action != CRM_Core_Action::ADD) {
      $this->getProgrammeDivisions();
    }
    /*
     * set page title based on action
     */
    $this->setPageTitle();
  }
  /**
   * Function to process form input
   */
  function postProcess() {
    $values = $this->exportValues();
    $savedProgramme = $this->saveProgramme($values);
    $session = CRM_Core_Session::singleton();
    if ($this->_action == CRM_Core_Action::UPDATE && $values['_qf_PumProgramme_next'] == 'Add Line') {
      $this->saveProgrammeDivision($values);
      $session->setStatus('Budget Division Added', 'Saved', 'success');
      $session->pushUserContext(CRM_Utils_System::url('civicrm/pumprogramme', 'action=update&pid='.$savedProgramme['id'], true));            
    } else {
      $session->setStatus('Programme Saved', 'Saved', 'success');
      $session->pushUserContext(CRM_Utils_System::url('civicrm/programmelist', '', true));
    }
    parent::postProcess();
  }
  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    /*
     *  The _elements list includes some items which should not be
     * auto-rendered in the loop -- such as "qfKey" and "buttons".  These
     * items don't have labels.  We'll identify renderable by filtering on
     * the 'label'.
     */
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
          $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
  /**
   * Function to set default values
   * 
   * @return array $defaults
   */
  function setDefaultValues() {
    $defaults = array();
    if (isset($this->_id)) {
      $programmeValues = CRM_Threepeas_BAO_PumProgramme::getValues(array('id' => $this->_id));
      foreach ($programmeValues[$this->_id] as $name => $value) {
        $defaults[$name] = $value;
      }
    } else {
      $defaults['is_active'] = 1;
    }
    if ($this->_action == CRM_Core_Action::VIEW) {
      $defaults = $this->correctViewDefaults($defaults);
    }
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $defaults = $this->correctUpdateDefaults($defaults);
    }
    return $defaults;
  }
  /**
   * Function to set form elements based on action
   */
  function setFormElements() {
    switch ($this->_action) {
      case CRM_Core_Action::VIEW:
        $this->setViewElements();
        break;
      case CRM_Core_Action::UPDATE:
        $this->setUpdateElements();
        break;
      case CRM_Core_Action::ADD:
        $this->setAddElements();
        break;
    }
  }
  /**
   * Function to set View Elements
   */
  function setViewElements() {
    $this->add('text', 'title', ts('Title'), array('size' => CRM_Utils_Type::HUGE));
    $this->add('textarea', 'description', ts('Description'), array(
      'rows'    => 4,
      'readonly'=> 'readonly',
      'cols'    => 80), false);
    $this->add('text', 'manager_id', ts('Programme Manager'), array('size' => CRM_Utils_Type::HUGE));
    $this->add('text', 'budget', ts('Budget'));
    $this->add('textarea', 'goals', ts('Goals'), array(
      'rows'    => 4,
      'readonly'=> 'readonly',
      'cols'    => 80), false);
    $this->add('textarea', 'requirements', ts('Requirements'), array(
      'rows'    => 4,
      'readonly'=> 'readonly',
      'cols'    => 80), false);
    $this->addDate('start_date', ts('Start Date'), false);
    $this->addDate('end_date', ts('End Date'), false);
    $this->add('text', 'is_active', ts('Enabled?'));
    $this->addButtons(array(array('type' => 'cancel', 'name' => ts('Done'), 'isDefault' => true)));
  }
  /**
   * Function to set Add Elements
   */
  function setAddElements() {
    $this->add('text', 'title', ts('Title'), array(
      'size'      =>  CRM_Utils_Type::HUGE,
      'maxlength' =>  255), true);
    $this->add('textarea', 'description', ts('Description'), array(
      'rows'  => 4,
      'cols'  => 80), false);
    $this->add('select', 'manager_id', ts('Programme Manager'), $this->_programmeManagers, true);
    $this->add('text', 'budget', ts('Budget'));
    $this->add('textarea', 'goals', ts('Goals'), array(
      'rows'  => 4,
      'cols'  => 80), false);
    $this->add('textarea', 'requirements', ts('Requirements'), array(
      'rows'  => 4,
      'cols'  => 80), false);
    $this->addDate('start_date', ts('Start Date'), false);
    $this->addDate('end_date', ts('End Date'), false);
    $this->add('checkbox', 'is_active', ts('Enabled'));
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }
  /**
   * Function to set page title
   */
  function setPageTitle() {
    switch($this->_action) {
      case CRM_Core_Action::ADD:
        $pageTitle = "New Programme";
        break;
      case CRM_Core_Action::VIEW:
        $pageTitle = "View Programme";
        break;
      case CRM_Core_Action::UPDATE:
        $pageTitle = "Update Programme";
        break;
      default:
        $pageTitle = "Programme";
        break;
    }
    CRM_Utils_System::setTitle(ts($pageTitle));
    $this->assign('formHeader', $pageTitle);
  }
  /**
   * Function to set Update Elements
   */
  function setUpdateElements() {
    $this->add('text', 'title', ts('Title'), array(
      'size'      =>  CRM_Utils_Type::HUGE,
      'maxlength' =>  255), true);
    $this->add('textarea', 'description', ts('Description'), array(
      'rows'  => 4,
      'cols'  => 80), false);
    $this->add('select', 'manager_id', ts('Programme Manager'), $this->_programmeManagers, true);
    $this->add('text', 'budget', ts('Budget'));
    $this->add('textarea', 'goals', ts('Goals'), array(
      'rows'  => 4,
      'cols'  => 80), false);
    $this->add('textarea', 'requirements', ts('Requirements'), array(
      'rows'  => 4,
      'cols'  => 80), false);
    $this->addDate('start_date', ts('Start Date'));
    $this->addDate('end_date', ts('End Date'));
    $this->add('checkbox', 'is_active', ts('Enabled'));
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
    $this->add('select', 'division_country', '', $this->_divisionCountries, true);
    $this->add('text', 'min_projects', '', array('size' => CRM_Utils_Type::TWENTY));
    $this->add('text', 'max_projects', '', array('size' => CRM_Utils_Type::TWENTY));
    $this->add('text', 'min_budget', '', array('size' => CRM_Utils_Type::TWENTY));
    $this->add('text', 'max_budget', '', array('size' => CRM_Utils_Type::TWENTY));
  }
  /**
   * Function to get the option values of the select lists
   */
  function setOptionValues() {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    /*
     * programmeManagers
     */
    $programmeManagers = civicrm_api3('Contact', 'Get', array('group' => $threepeasConfig->programmeManagerGroupId));
    foreach ($programmeManagers['values'] as $managerId => $programmeManager) {
      $this->_programmeManagers[$managerId] = $programmeManager['sort_name'];
    }
    $this->_programmeManagers[0] = '- select -';
    asort($this->_programmeManagers);
    /*
     * countries
     */
    $countries = civicrm_api3('Country', 'Get', array('options' => array('limit' => 9999)));
    foreach ($countries['values'] as $countryId => $country) {
      $this->_divisionCountries[$countryId] = $country['name'];
    }
    $this->_divisionCountries[0] = '- select -';
    asort($this->_divisionCountries);
  }
  /**
   * Function to save the programme
   * 
   * @return array $result with saved programme data
   */
  function saveProgramme($values) {
    $saveProgramme = $values;
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $saveProgramme['id'] = $this->_id;
    }
    $saveProgramme['start_date'] = CRM_Utils_Date::processDate($values['start_date']);
    $saveProgramme['end_date'] = CRM_Utils_Date::processDate($values['end_date']);
    $saveProgramme['is_active'] = 1;
    if (isset($values['is_active'])) {
      $saveProgramme['is_active'] = $values['is_active'];
    }
    $result = CRM_Threepeas_BAO_PumProgramme::add($saveProgramme);
    return $result;
  }
  /**
   * Function to save the programme division line
   */
  function saveProgrammeDivision($values) {
    $saveProgrammeDivision['programme_id'] = $this->_id;
    $saveProgrammeDivision['country_id'] = $values['division_country'];
    if (!empty($values['min_projects'])) {
      $saveProgrammeDivision['min_projects'] = $values['min_projects'];
    }
    if (!empty($values['max_projects'])) {
      $saveProgrammeDivision['max_projects'] = $values['max_projects'];
    }
    if (!empty($values['min_budget'])) {
      $saveProgrammeDivision['min_budget'] = $values['min_budget'];
    }
    if (!empty($values['max_budget'])) {
      $saveProgrammeDivision['max_budget'] = $values['max_budgets'];
    }
    CRM_Threepeas_BAO_PumProgrammeDivision::add($saveProgrammeDivision);
  }
  /**
   * Function to retrieve programme divisions
   */
  function getProgrammeDivisions() {
    $displayDivisions = array();
    $programmeDivisions = CRM_Threepeas_BAO_PumProgrammeDivision::getValues(array('programme_id' => $this->_id));
    foreach ($programmeDivisions as $divisionId => $division) {
      $displayDivision = array();
      $displayDivision['id'] = $divisionId;
      $displayDivision['country'] = CRM_Utils_Array::value($division['country_id'], $this->_divisionCountries);
      $displayDivision['min_projects'] = $division['min_projects'];
      $displayDivision['max_projects'] = $division['max_projects'];
      $displayDivision['min_budget'] = $division['min_budget'];
      $displayDivision['max_budget'] = $division['max_budget'];
      $displayDivisions[] = $displayDivision;
    }
    $this->assign('programmeDivisions', $displayDivisions);
  }
  /**
   * Function to correct defaults for View action
   */
  function correctViewDefaults($defaults) {
    if (isset($defaults['manager_id']) && !empty($defaults['manager_id'])) {
      $defaults['manager_id'] = CRM_Utils_Array::value($defaults['manager_id'], $this->_programmeManagers);
    }
    if (!isset($defaults['start_date'])) {
      $defaults['start_date'] = '';
    }
    if (!isset($defaults['end_date'])) {
      $defaults['end_date'] = '';
    }
    $defaults['budget'] = CRM_Utils_Money::format($defaults['budget']);
    return $defaults;
  }
  /**
   * Function to correct defaults for Edit action
   */
  function correctUpdateDefaults($defaults) {
    list($defaults['start_date']) = CRM_Utils_Date::setDateDefaults($defaults['start_date']);
    list($defaults['end_date']) = CRM_Utils_Date::setDateDefaults($defaults['end_date']);
    return $defaults;
  }
  /**
   * Function to set validation rules
   */
  function addRules() {
    $ruleParams = array('programme_id' => $this->_id, 'action' => $this->_action);
    $this->addFormRule(array('CRM_Threepeas_Form_PumProgramme', 'formRule'), $ruleParams);
  }
  /**
   * Function that executes validation
   * 
   * @param array $fields - form values
   * @return TRUE or array $errors
   */
  static function formRule($fields, $files, $ruleParams) {
    if (!empty($fields['end_date'])) {
      $compEndDate = date('Ymd', strtotime($fields['end_date']));
      $compStartDate = date('Ymd', strtotime($fields['start_date']));
      if ($compEndDate <= $compStartDate) {
          $errors['end_date'] = ts('End date has to be later than start date');
      }
    }
    if ($ruleParams['action'] == CRM_Core_Action::ADD && CRM_Threepeas_BAO_PumProgramme::checkTitleExists($fields['title'])) {
      $errors['title'] = ts('You already have a programme with that title '.$fields['title']);
    }
    /*
     * programmeDivision validation
     */
    if ($fields['_qf_PumProgramme_next'] == 'Add Line') {
      $fields['programme_id'] = $ruleParams['programme_id'];
      self::validateProgrammeDivision($fields, $errors);
    }
    if (empty($errors)) {
      return TRUE;
    } else {
      return $errors;
    }
  }
  /**
   * Function to execute budget division validation rules
   * 
   * @param array $fields - form values
   * @param array $errors by reference
   */
  static function validateProgrammeDivision($fields, &$errors) {
    if (empty($fields['min_projects']) && empty($fields['max_projects']) && empty($fields['min_budget']) && empty($fields['max_budget'])) {
      $errors['min_projects'] = ts('Nothing to add for budget division line!');
    } else {
      if ($fields['division_country'] == 0) {
        $errors['division_country'] = ts('You have to select a country!');
      } else {
        $countryExists = CRM_Threepeas_BAO_PumProgrammeDivision::checkCountryExists(
          $fields['division_country'], $fields['programme_id']);
        if ($countryExists == TRUE) {
          $errors['division_country'] = ts('You already have this country in your divisions, delete it first!');
        }
      }
      if (!empty($fields['max_projects'])) {
        if ($fields['max_projects'] < $fields['min_projects']) {
          $errors['max_projects'] = ts('Maximum projects can not be smaller than minimum projects');
        }
      }
      if (!empty($fields['max_budget'])) {
        if ($fields['max_budget'] < $fields['min_budget']) {
          $errors['max_budget'] = ts('Maximum budget can not be smaller than minimum budget');
        }
      }
    }
  }
  /**
   * Function to process enable/disable programme
   */
  function processAble() {
    if ($this->_action == CRM_Core_Action::ENABLE) {
      $params['is_active'] = 1;
    } else {
      $params['is_active'] = 0;
    }
    $params['id'] = $this->_id;
    CRM_Threepeas_BAO_PumProgramme::add($params);
  }
}
