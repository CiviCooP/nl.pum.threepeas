<?php

use CRM_Threepeas_ExtensionUtil as E;

/**
 * Form controller class for remote coaching type
 * It hooks on the custom field 'Type of Remote Coaching' to generate a custom form which allows us to create a conditional civicrm form
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Threepeas_Form_TypeOfRemoteCoaching extends CRM_Core_Form {
  public $case_id;
  public $client_id;

  public function preProcess(){
    if(!empty($_GET['id'])){
      $this->case_id = $_GET['id'];
    }
    if(!empty($_GET['cid'])){
      $this->client_id = $_GET['cid'];
    }
  }

  public function setDefaultValues() {
    $defaults = array();
    $columns = array();

    $cg_typeremotecoaching = civicrm_api('CustomGroup', 'getsingle', array('version' => 3, 'sequential' => 1, 'name' => 'Type_of_Remote_Coaching'));
    $cf_typeremotecoaching = civicrm_api('CustomField', 'get', array('version' => 3, 'sequential' => 1, 'custom_group_id' => $cg_typeremotecoaching['id']));

    foreach($cf_typeremotecoaching['values'] as $key => $value){
      if($value['name'] == 'Type_of_Remote_Coaching'){
        $columns['remote_coaching'] = $value['column_name'];
        $values['remote_coaching'] = $value['id'];
      }
      if($value['name'] == 'Number_of_participants'){
        $columns['number_participants'] = $value['column_name'];
        $values['number_participants'] = $value['id'];
      }
      if($value['name'] == 'Participating_countries'){
        $columns['participating_countries'] = $value['column_name'];
        $values['participating_countries'] = $value['id'];
      }
    }
    $sql = "SELECT * FROM {$cg_typeremotecoaching['table_name']} WHERE entity_id = %1";
    $query = CRM_Core_DAO::executeQuery($sql, array(1 => array((int)$this->case_id, 'Integer')));

    while($query->fetch()){
      $ov_typeremotecoaching = civicrm_api('OptionValue', 'getsingle', array('version' => 3, 'sequential' => 1, 'value' => $query->$columns['remote_coaching'], 'option_group_title' => 'Type of Remote Coaching'));

      if(isset($query->$columns['remote_coaching']) && !empty($query->$columns['remote_coaching'])){
        $defaults['type_remote_coaching'] = $ov_typeremotecoaching['id'];
        $defaults['number_participants'] = $query->id;

        $defaults['countries'] = @unserialize($query->$columns['participating_countries']);
      }
    }

    return $defaults;
  }

  public function buildQuickForm() {
    CRM_Utils_System::setTitle('Type of Remote Coaching for Case ID '.$this->case_id);
    // add form elements
    $this->add(
      'select', // field type
      'type_remote_coaching', // field name
      'Type of Remote Coaching', // field label
      $this->getRemoteCoachingTypes(), // list of options
      TRUE // is required
    );

    $this->add(
      'text', // field type
      'number_participants', // field name
      'Number of Participants', // field label
      TRUE // is required
    );

    $countrySelect = $this->addElement('advmultiselect', 'countries', ts('Countries'), $this->getCountries(),
    array('id' => 'countries','class' => 'advmultselect', 'size' => 10, 'style' => 'width:auto;'),TRUE);

    $countrySelect->setButtonAttributes('add', array('value' => ts('Add country')." >>"));
    $countrySelect->setButtonAttributes('remove', array('value' => "<< ".ts('Remove country')));

    $this->add(
      'hidden',
      'caseId',
      $this->case_id,
      TRUE);

    $this->add(
      'hidden',
      'clientId',
      $this->client_id,
      TRUE);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    //Retrieve custom fields
    $params_og_remotecoachingtypes = array(
      'version' => 3,
      'sequential' => 1,
      'title' => 'Type of Remote Coaching'
    );
    $result_og_remotecoachingtypes = civicrm_api('OptionGroup', 'getsingle', $params_og_remotecoachingtypes);

    $params_cg_remotecoachingtypes = array(
      'version' => 3,
      'sequential' => 1,
      'name' => 'Type_of_Remote_Coaching',
    );
    $result_cg_remotecoachingtypes = civicrm_api('CustomGroup', 'getsingle', $params_cg_remotecoachingtypes);

    $params_cf_typeremotecoaching = array(
      'version' => 3,
      'sequential' => 1,
      'custom_group_id' => $result_cg_remotecoachingtypes['id'],
    );
    $result_cf_typeremotecoaching = civicrm_api('CustomField', 'get', $params_cf_typeremotecoaching);
    $field_value_type_remotecoaching = civicrm_api('OptionValue', 'getsingle', array('version' => 3, 'sequential' => 1, 'id' => $values['type_remote_coaching']));
    $field_value_number_participants = civicrm_api('OptionValue', 'getsingle', array('version' => 3, 'sequential' => 1, 'id' => $values['number_participants']));
    $field_value_countries = @serialize($values['countries']);

    $fields = array();
    foreach($result_cf_typeremotecoaching['values'] as $key => $field){
      if($field['label'] == 'Type of Remote Coaching'){
        $fields['type_remote_coaching'] = $field['column_name'];
      }
      if($field['label'] == 'Number of participants'){
        $fields['number_participants'] = $field['column_name'];
      }
      if($field['label'] == 'Participating countries'){
        $fields['participating_countries'] = $field['column_name'];
      }
    }

    //Check if value already exists
    $sql = "SELECT * FROM {$result_cg_remotecoachingtypes['table_name']} WHERE `entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array((int)$values['caseId'], 'Integer')));
    while($dao->fetch()){
      $found = $dao;
    }

    //Update fields in database
    if($found->N > 0) {
      $sql = "UPDATE {$result_cg_remotecoachingtypes['table_name']} SET {$fields['type_remote_coaching']} = %2, {$fields['number_participants']} = %3, {$fields['participating_countries']} = %4 WHERE `entity_id` = %1"; // moeten nog: {$fields['number_participants']} = %3, {$fields['participating_countries']} = %4
      $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array((int)$values['caseId'], 'Integer'), 2 => array($field_value_type_remotecoaching['value'],'String'), 3 => array($field_value_number_participants['value'],'Integer'), 4 => array($field_value_countries, 'String')));
    } else {
      $sql = "INSERT INTO {$result_cg_remotecoachingtypes['table_name']} (`entity_id`, ".$fields['type_remote_coaching'].", ".$fields['number_participants'].", ".$fields['participating_countries'].") VALUES (%1, %2, %3, %4)";
      $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array((int)$values['caseId'], 'Integer'), 2 => array($field_value_type_remotecoaching['value'], 'String'), 3 => array($field_value_number_participants, 'String'), 4 => array($field_value_countries, 'String')));
    }

    CRM_Utils_System::redirect('/civicrm/contact/view/case?reset=1&action=view&cid='.$values['clientId'].'&id='.$values['caseId']);

    parent::postProcess();
  }

  /**
   * Method to get list of remote coaching types for option list
   *
   * @return array $options
   */
  public function getRemoteCoachingTypes() {
    $options = array();

    $result_og_remotecoachingtypes = civicrm_api('OptionGroup', 'getsingle', array('version' => 3, 'sequential' => 1, 'title' => 'Type of Remote Coaching'));
    $remote_coaching_types = civicrm_api('OptionValue', 'get', array('version' => 3, 'sequential' => 1, 'option_group_id' => $result_og_remotecoachingtypes['id']));

    $options = array('' => E::ts('- select -'));

    if(is_array($remote_coaching_types['values'])){
      foreach($remote_coaching_types['values'] as $key => $value){
        $options[$value['id']] = $value['label'];
      }
    }

    return $options;
  }

  /**
   * Method to get available countries
   *
   * @return array $result
   */
  private function getCountries() {
    $result = array();
    try {
      $countries = civicrm_api3('Country', 'get', array(
        'version' => 3,
        'sequential' => 1,
        'rowCount' => 0
      ));

      foreach ($countries['values'] as $country) {
        $result[$country['id']] = $country['name'];
      }
    } catch (CiviCRM_API3_Exception $ex) {}

    return $result;
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
