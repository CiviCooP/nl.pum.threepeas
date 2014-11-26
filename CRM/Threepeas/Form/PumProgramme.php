<?php
/**
 * Class PumProgramme for form processing of PUM Programme
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 16 Apr 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the AGPL-3.0
 */
require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Threepeas_Form_PumProgramme extends CRM_Core_Form {
  
  protected $_programmeManagers = array();
  protected $_linked_donation_entity_ids = array();
  
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
     * set page title based on action
     */
    $this->setPageTitle();
  }
  /**
   * Function to process form input
   */
  function postProcess() {
    $values = $this->exportValues();
    $this->saveProgramme($values);
    $session = CRM_Core_Session::singleton();
    $session->setStatus('Programme Saved', 'Saved', 'success');
    $session->pushUserContext(CRM_Utils_System::url('civicrm/programmelist', '', true));
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
      $this->correctViewDefaults($defaults);
    }
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $this->correctUpdateDefaults($defaults);
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
    $this->setViewDonationLink();
  }
  /**
   * Function to set view elements for donation links
   */
  function setViewDonationLink() {
    $linkedDonations = array();
    $params = array('entity' => 'Programme', 'entity_id' => $this->_id, 'is_active' => 1);
    $currentContributions = CRM_Threepeas_BAO_PumDonorLink::getValues($params);
    foreach ($currentContributions as $currentContribution) {
      $linkedDonations[] = CRM_Threepeas_BAO_PumDonorLink::createViewRow($currentContribution);
    }
    $this->assign('linkedDonations', $linkedDonations);
    $this->assign('linkEntity', 'Programme');
  }
  /**
   * Function to build Donation Link Row
   */
  
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
    $this->setAddUpdateDonationLink();
  }
  /**
   * Function to set add/update elements for donation links
   */
  function setAddUpdateDonationLink() {
    $contributionsList = CRM_Threepeas_BAO_PumDonorLink::get_contributions_list('Programme', '');
    $this->add('advmultiselect', 'new_link', '', $contributionsList, false,  
      array('size' => count($contributionsList), 'style' => 'width:auto; min-width:300px;',
        'class' => 'advmultiselect',
      ));
    $fa_donation_list = $contributionsList;
    $fa_donation_list[0] = '- select -';
    asort($fa_donation_list);
    $this->add('select', 'fa_donor', 'For FA', $fa_donation_list);
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
    $this->setAddUpdateDonationLink();
  }
  /**
   * Function to get the option values of the select lists
   */
  function setOptionValues() {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    /*
     * programmeManagers
     */
    $programmeManagers = civicrm_api3('Contact', 'Get', array('group' => $threepeasConfig->programmeManagersGroupId));
    foreach ($programmeManagers['values'] as $managerId => $programmeManager) {
      $this->_programmeManagers[$managerId] = $programmeManager['sort_name'];
    }
    $this->_programmeManagers[0] = '- select -';
    asort($this->_programmeManagers);
    $this->set_linked_donation_entity_ids();
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
    /*
     * save related donor links
     */
    $this->saveDonorLink($result['id'], $values);
    return;
  }
  /**
   * Function to save donor links if required
   */
  function saveDonorLink($programmeId, $values) {
    /*
     * if update, delete all current donor links for programme
     */
    if ($this->_action == CRM_Core_Action::UPDATE) {
      CRM_Threepeas_BAO_PumDonorLink::deleteByEntityId('Programme', $programmeId);
    }
    /*
     * add new donor links
     */
    foreach ($values['new_link'] as $newLink) {
      $params = array(
        'donation_entity' => 'Contribution', 
        'donation_entity_id' => $newLink,
        'entity' => 'Programme',
        'entity_id' => $programmeId,
        'is_active' => 1);
      if ($newLink == $values['fa_donor']) {
        $params['is_fa_donor'] = 1;
      } else {
        $params['is_fa_donor'] = 0;
      }
      CRM_Threepeas_BAO_PumDonorLink::add($params);
    }
  }
  /**
   * Function to correct defaults for View action
   */
  function correctViewDefaults(&$defaults) {
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
    /*
     * show current donation links
     */
    $params = array('entity' => 'Programme', 'entity_id' => $this->_id, 'donation_entity' => 'Contribution', 'is_active' => 1);
    $currentContributions = CRM_Threepeas_BAO_PumDonorLink::getValues($params);
    foreach ($currentContributions as $currentContribution) {
      $defaults['new_link'][] = $currentContribution['donation_entity_id'];
    }
    $fa_donor = $this->set_default_fa_donor($this->_id);
    if (!empty($fa_donor)) {
      $defaults['fa_donor'] = $fa_donor;
    } else {
      $defaults['fa_donor'] = 0;
    }
  }
  /**
   * Function to set validation rules
   */
  function addRules() {
    $ruleParams = array('action' => $this->_action);
    $this->addFormRule(array('CRM_Threepeas_Form_PumProgramme', 'formRule'), $ruleParams);
    $this->addFormRule(array('CRM_Threepeas_Form_PumProgramme', 'validate_fa_donor'));
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
    if (empty($errors)) {
      return TRUE;
    } else {
      return $errors;
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
  /**
   * Function to set the linked donation entity ids
   */
  protected function set_linked_donation_entity_ids() {
    if (!empty($this->_id)) {
      $params = array(
        'entity' => 'Programme', 
        'entity_id' => $this->_id, 
        'donation_entity' => 'Contribution', 
        'is_active' => 1);
      $donations = CRM_Threepeas_BAO_PumDonorLink::getValues($params);
      foreach ($donations as $donation) {
        $this->_linked_donation_entity_ids[] = $donation['donation_entity_id'];
      }  
    }
  }
  /**
   * Function to validate the fa donor
   */
  static function validate_fa_donor($fields) {
    if ($fields['fa_donor'] == 0) {
      $errors['fa_donor'] = ts('You have to select a donation for FA');
      return $errors;
    } else {
      if (!in_array($fields['fa_donor'], $fields['new_link'])) {
        $errors['fa_donor'] = ts('You have to use a linked donation as the donation for FA');
        return $errors;
      }
    }
    return TRUE;
  }
  /**
   * Function to set default fa donor
   * 
   * @param int $programme_id
   * @return int $fa_donation_id
   * @access protected
   * @static
   */
  protected function set_default_fa_donor($programme_id) {
    $fa_donation_id = 0;
    $params = array(
      'entity_id' => $programme_id,
      'entity' => 'Programme',
      'is_fa_donor' => 1);
    $fa_donation = CRM_Threepeas_BAO_PumDonorLink::getValues($params);
    foreach ($fa_donation as $donation_values) {
      $fa_donation_id = $donation_values['donation_entity_id'];
    }
    return $fa_donation_id;
  }
}
