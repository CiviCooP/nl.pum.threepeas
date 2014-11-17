<?php
/**
 * Class PumProject for form processing of PUM Project
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 Apr 2014
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
class CRM_Threepeas_Form_PumProject extends CRM_Core_Form {
  
  protected $_projectCustomers = array();
  protected $_projectCountries = array();
  protected $_projectManagers = array();
  protected $_programmes = array();
  protected $_projectType = NULL;
  
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
    /*
     * only allow edit if user has 'edit all contacts'
     */
    if (CRM_Core_Permission::check('edit all contacts')) {
      $permission = CRM_Core_Permission::ALL;
    } else {
      $permission = CRM_Core_Permission::VIEW;
    }
    $this->assign('permission', $permission);
    parent::buildQuickForm();
  }
  /**
   * Function for processing before building the form
   */
  function preProcess() {
    if ($this->_action != CRM_Core_Action::ADD) {
      $this->_id = CRM_Utils_Request::retrieve('pid', 'Integer', $this);
      $this->_projectType = CRM_Threepeas_BAO_PumProject::getProjectType($this->_id);
    }
    /*
     * if action = delete, execute delete immediately
     */
    if ($this->_action == CRM_Core_Action::DELETE) {
      CRM_Threepeas_BAO_PumProject::deleteById($this->_id);
      $session->setStatus('Project deleted', 'Delete', 'success');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/projectlist'));
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
    $savedProject = $this->saveProject($values);
    $session = CRM_Core_Session::singleton();
    $session->setStatus('Project '.$savedProject['title'].' Saved', 'Saved', 'success');
    parent::postProcess();
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
    $this->add('text', 'programme_id', ts('Programme'), array('size' => CRM_Utils_Type::HUGE));
    $this->add('text', 'customer_id', ts('Customer or Country'), array('size' => CRM_Utils_Type::HUGE));
    if ($this->_projectType == 'Customer') {
      $this->add('text', 'projectmanager_id', ts('Project Manager'), array('size' => CRM_Utils_Type::HUGE));
      $this->add('textarea', 'reason', ts('What is the reason for this request for Assistance?'), 
        array('rows'    => 4, 'readonly'=> 'readonly', 'cols'    => 80), false);
      $this->add('textarea', 'work_description', ts('Which project activities do you expect the expert to perform?'), 
        array('rows'    => 4, 'readonly'=> 'readonly', 'cols'    => 80), false);
      $this->add('textarea', 'qualifications', ts('Qualifications'), 
        array('rows'    => 4, 'readonly'=> 'readonly', 'cols' => 80), false);    
      $this->add('text', 'sector_coordinator', ts('Sector Coordinator'), array('size' => CRM_Utils_Type::HUGE));
      $this->add('text', 'representative', ts('Representative'), array('size' => CRM_Utils_Type::HUGE));
    }
    $this->add('textarea', 'expected_results', ts('What are the expected results of the project? '), 
      array('rows'    => 4, 'readonly'=> 'readonly', 'cols'    => 80), false);
    $this->add('text', 'country_coordinator', ts('Country Coordinator'), array('size' => CRM_Utils_Type::HUGE));
    $this->add('text', 'project_officer', ts('Project Officer'), array('size' => CRM_Utils_Type::HUGE));
    $this->addDate('start_date', ts('Start Date'), false);
    $this->addDate('end_date', ts('End Date'), false);
    $this->add('text', 'is_active', ts('Enabled?'));
    $this->addButtons(array(array('type' => 'cancel', 'name' => ts('Done'), 'isDefault' => true)));
    $this->setViewDonationLink();
  }
  /**
   * Function to set Add Elements (country projects only)
   */
  function setAddElements() {
    $this->add('text', 'title', ts('Title'), array(
      'size' => CRM_Utils_Type::HUGE, 'maxlength' =>  255), true);
    $this->add('select', 'programme_id', ts('Programme'), $this->_programmes);
    $this->add('select', 'customer_id', ts('Customer'), $this->_projectCustomers);
    $this->add('select', 'country_id', ts('Country'), $this->_projectCountries);
    if ($this->_projectType == 'Customer') {
      $this->add('select', 'projectmanager_id', ts('Project Manager'), $this->_projectManagers);
      $this->add('textarea', 'reason', ts('Reason'), array('rows'  => 4, 'cols'  => 80), false);
      $this->add('textarea', 'work_description', ts('Work description'), array('rows'  => 4,  'cols'  => 80), false);
      $this->add('textarea', 'qualifications', ts('Qualifications'), array('rows'  => 4,  'cols'  => 80), false);
    }
    $this->add('textarea', 'expected_results', ts('Expected results'), array('rows'  => 4,  'cols'  => 80), false);
    $this->addDate('start_date', ts('Start Date'), false);
    $this->addDate('end_date', ts('End Date'), false);
    $this->add('checkbox', 'is_active', ts('Enabled'));
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
    $this->setAddUpdateDonationLink();
  }
  /**
   * Function to set Update Elements
   */
  function setUpdateElements() {
    $this->add('text', 'title', ts('Title'), array(
      'size' => CRM_Utils_Type::HUGE, 'maxlength' =>  255), true);
    $this->add('select', 'programme_id', ts('Programme'), $this->_programmes);
    if ($this->_projectType === 'Country') {
      $this->add('text', 'country_id', ts('Customer or Country'), array('size' => CRM_Utils_Type::HUGE));
    } else {
      $this->add('text', 'customer_id', ts('Customer or Country'), array('size' => CRM_Utils_Type::HUGE));
    }
    if ($this->_projectType == 'Customer') {
      $this->add('select', 'projectmanager_id', ts('Project Manager'), $this->_projectManagers);
      $this->add('textarea', 'reason', ts('Reason'), array('rows'  => 4, 'cols'  => 80), false);
      $this->add('textarea', 'work_description', ts('Work description'), array('rows'  => 4,  'cols'  => 80), false);
      $this->add('textarea', 'qualifications', ts('Qualifications'), array('rows'  => 4,  'cols'  => 80), false);
      $this->add('text', 'sector_coordinator', ts('Sector Coordinator'), array('size' => CRM_Utils_Type::HUGE));
      $this->add('text', 'representative', ts('Representative'), array('size' => CRM_Utils_Type::HUGE));
    }
    $this->add('text', 'country_coordinator', ts('Country Coordinator'), array('size' => CRM_Utils_Type::HUGE));
    $this->add('text', 'project_officer', ts('Project Officer'), array('size' => CRM_Utils_Type::HUGE));
    $this->add('textarea', 'expected_results', ts('Expected results'), array('rows'  => 4,  'cols'  => 80), false);
    $this->addDate('start_date', ts('Start Date'), false);
    $this->addDate('end_date', ts('End Date'), false);
    $this->add('checkbox', 'is_active', ts('Enabled'));
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));   
    $this->setAddUpdateDonationLink();
  }
  /**
   * Function to set page title
   */
  function setPageTitle() {
    switch($this->_action) {
      case CRM_Core_Action::ADD:
        $pageTitle = "New Project";
        break;
      case CRM_Core_Action::VIEW:
        $pageTitle = "View Project";
        break;
      case CRM_Core_Action::UPDATE:
        $pageTitle = "Update Project";
        break;
      default:
        $pageTitle = "Project";
        break;
    }
    CRM_Utils_System::setTitle(ts($pageTitle));
    $this->assign('formHeader', $pageTitle);
  }
  /**
   * Function to get the option values of the select lists
   */
  function setOptionValues() {
    $this->setProgrammeList();
    $this->setProjectCustomerList();
    $this->setProjectCountryList();
    $this->setProjectmanagersList();
  }
  /**
   * Function to get the list of programmes
   */
  function setProgrammeList() {
    $activeProgrammes = CRM_Threepeas_BAO_PumProgramme::getValues(array('is_active' => 1));
    foreach ($activeProgrammes as $programmeId => $programme) {
      $this->_programmes[$programmeId] = $programme['title'];      
    }
    $this->_programmes[0] = '- select -';
    asort($this->_programmes);
  }
  /**
   * Function to get the list of customers
   */
  function setProjectCustomerList() {
    $threepeasConfig = CRM_Threepeas_Config::singleton();    
    $customerParams = array('contact_sub_type' => $threepeasConfig->customerContactType, 'is_deleted' => 0);
    $this->_projectCustomers = $this->retrieveContacts($customerParams);
    $this->_projectCustomers[0] = '- select -';
    asort($this->_projectCustomers);
  }
  /**
   * Function to get the list of project managers
   */
  function setProjectmanagersList() {
    $this->_projectManagers = [];
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $groupContactParams = array('group_id' => $threepeasConfig->projectmanagerGroupId);
    try {
      $projectManagers = civicrm_api3('GroupContact', 'Get', $groupContactParams);
    } catch (CiviCRM_API3_Exception $ex) {
      $this->_projectManagers = array();
    }
    foreach ($projectManagers['values'] as $projectManager) {
      $nameParams = array('id' => $projectManager['contact_id'], 'return' => 'display_name');
      $this->_projectManagers[$projectManager['contact_id']] = civicrm_api3('Contact', 'Getvalue', $nameParams);      
    }
    $this->_projectManagers[0] = '- select -';
    asort($this->_projectManagers);
  }
  /**
   * function to get the list of countries
   */
  function setProjectCountryList() {
    $params = array(
      'contact_sub_type' => 'Country',
      'options' => array('limit' => 99999));
    $countries = civicrm_api3('Contact', 'Get', $params);
    foreach ($countries['values'] as $countryId => $country) {
      $this->_projectCountries[$countryId] = $country['display_name'];
    }
    $this->_projectCountries[0] = '- select -';
    asort($this->_projectCountries);
  }
  /**
   * Function to save the project
   * 
   * @return array $result with saved project data
   */
  function saveProject($values) {
    $saveProject = $values;
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $saveProject['id'] = $this->_id;
      unset($saveProject['customer_id']);
      unset($saveProject['country_id']);
    }
    $saveProject['start_date'] = CRM_Utils_Date::processDate($values['start_date']);
    $saveProject['end_date'] = CRM_Utils_Date::processDate($values['end_date']);
    $saveProject['is_active'] = 1;
    if (isset($values['is_active'])) {
      $saveProject['is_active'] = $values['is_active'];
    }
    $result = CRM_Threepeas_BAO_PumProject::add($saveProject);
    /*
     * save related donor links
     */
    $this->saveDonorLink($result['id'], $values);
    return $result;
  }
  /**
   * Function to correct defaults for View action
   */
  function correctViewDefaults(&$defaults) {
    if (isset($defaults['programme_id']) && !empty($defaults['programme_id'])) {
      $defaults['programme_id'] = CRM_Utils_Array::value($defaults['programme_id'], $this->_programmes);
    }
    if (isset($defaults['projectmanager_id']) && !empty($defaults['projectmanager_id'])) {
      $defaults['projectmanager_id'] = $this->_projectManagers[$defaults['projectmanager_id']];
    }
    if (!isset($defaults['start_date'])) {
      $defaults['start_date'] = '';
    }
    if (!isset($defaults['end_date'])) {
      $defaults['end_date'] = '';
    }
  }
  /**
   * Function to retrieve contacts with params for option list
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 23 Apr 2014
   * @param array $params
   * @return array $result
   */
  function retrieveContacts($params) {
    $result = array();
    if (!is_array($params)) {
      return $result;
    }
    $apiContacts = civicrm_api3('Contact', 'Get', $params);
    foreach ($apiContacts['values'] as $contactId => $apiContact) {
      $result[$contactId] = $apiContact['sort_name'];
    }
    asort($result);
    return $result;
  }
  /**
   * Function to add validation rules
   */
  function addRules() {
    if ($this->_action == CRM_Core_Action::ADD) {
      $this->addFormRule(array('CRM_Threepeas_Form_PumProject', 'validateCountry'));
      $this->addFormRule(array('CRM_Threepeas_Form_PumProject', 'validateTitle'));
    }
    $this->addFormRule(array('CRM_Threepeas_Form_PumProject', 'validateDates'));
  }
  /**
   * Function to validate title
   */
  static function validateTitle($fields) {
    if (CRM_Threepeas_BAO_PumProject::checkTitleExists($fields['title']) == TRUE) {
      $errors['title'] = ts('You already have a project with title '.$fields['title']);
      return $errors;
    } else {
      return TRUE;
    }
  }
  /**
   * Function to validate country
   */
  static function validateCountry($fields) {
    if (empty($fields['country_id']) || $fields['country_id'] == 0) {
      $errors['country_id'] = ts('You have to select a country for the project');
      return $errors;
    } else {
      return TRUE;
    }
  }
  /**
   * Function to validate start and end date
   */
  static function validateDates($fields) {
    if (!empty($fields['end_date'])) {
      $compEndDate = date('Ymd', strtotime($fields['end_date']));
      $compStartDate = date('Ymd', strtotime($fields['start_date']));
      if ($compEndDate <= $compStartDate) {
          $errors['end_date'] = ts('End date has to be later than start date');
          return $errors;
      }
    }
    return TRUE;
  }
  /**
   * Function to set view elements for donation links
   */
  function setViewDonationLink() {
    $linkedDonations = array();
    $params = array('entity' => 'Project', 'entity_id' => $this->_id, 'is_active' => 1);
    $currentContributions = CRM_Threepeas_BAO_PumDonorLink::getValues($params);
    foreach ($currentContributions as $currentContribution) {
      $linkedDonations[] = CRM_Threepeas_BAO_PumDonorLink::createViewRow($currentContribution);
    }
    $this->assign('linkedDonations', $linkedDonations);
    $this->assign('linkEntity', 'Project');
  }
  /**
   * Function to set add/update elements for donation links
   */
  function setAddUpdateDonationLink() {
    $contributionsList = _threepeasGetContributionsList();
    $this->add('advmultiselect', 'new_link', '', $contributionsList, false,  
      array('size' => count($contributionsList), 'style' => 'width:auto; min-width:300px;',
        'class' => 'advmultiselect',
      ));
  }
  /**
   * Function to correct defaults in Add mode
   */
  function correctAddDefaults(&$defaults) {
    $defaults['is_active'] = 1;
    if (isset($defaults['customer_id']) && !empty($defaults['customer_id'])) {
      $defaults['customer_id'] = CRM_Utils_Array::value($defaults['customer_id'], $this->_projectCustomers);
    } else {
      if (isset($defaults['country_id']) && !empty($defaults['country_id'])) {
        $defaults['country_id'] = CRM_Utils_Array::value($defaults['country_id'], $this->_projectCountries);
      }  
    }
    $new_year = (int) date('Y') + 1;
    list($defaults['start_date']) = CRM_Utils_Date::setDateDefaults($new_year.'-01-01');
    list($defaults['end_date']) = CRM_Utils_Date::setDateDefaults($new_year.'-12-31');
  }
  /**
   * Function to correct defaults for Edit action
   */
  function correctUpdateDefaults(&$defaults) {
    if (isset($defaults['start_date'])) {
      list($defaults['start_date']) = CRM_Utils_Date::setDateDefaults($defaults['start_date']);
    }
    if (isset($defaults['end_date'])) {
      list($defaults['end_date']) = CRM_Utils_Date::setDateDefaults($defaults['end_date']);
    }
    $params = array('entity' => 'Project', 'entity_id' => $this->_id, 'donation_entity' => 'Contribution', 'is_active' => 1);
    $currentContributions = CRM_Threepeas_BAO_PumDonorLink::getValues($params);
    foreach ($currentContributions as $currentContribution) {
      $defaults['new_link'][] = $currentContribution['donation_entity_id'];
    }
  }
  /**
   * Function to save donor links if required
   */
  function saveDonorLink($projectId, $values) {
    /*
     * if update, delete all current donor links for project
     */
    if ($this->_action == CRM_Core_Action::UPDATE) {
      CRM_Threepeas_BAO_PumDonorLink::deleteByEntityId('Project', $projectId);
    }
    /*
     * add new donor links
     */
    foreach ($values['new_link'] as $newLink) {
      $params = array(
        'donation_entity' => 'Contribution', 
        'donation_entity_id' => $newLink,
        'entity' => 'Project',
        'entity_id' => $projectId,
        'is_active' => 1);
      CRM_Threepeas_BAO_PumDonorLink::add($params);
    }
  }
}
