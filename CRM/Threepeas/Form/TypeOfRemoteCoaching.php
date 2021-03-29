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

  /**
   * CRM_Threepeas_Form_TypeOfRemoteCoaching::preProcess()
   *
   * Retrieve the current case id and client id to know which entry we are working on
   *
   * @return void
   */
  public function preProcess(){
    if(!empty($_GET['id'])){
      $this->case_id = $_GET['id'];
    }
    if(!empty($_GET['cid'])){
      $this->client_id = $_GET['cid'];
    }
  }

  /**
   * CRM_Threepeas_Form_TypeOfRemoteCoaching::setDefaultValues()
   *
   * This retrieves the existing values in the database for the corresponding case
   * If there is data available, it fills the default values of the form with the data from the database.
   *
   * @return $defaults
   */
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
        $defaults['number_participants'] = $query->$columns['number_participants'];

        $defaults['countries'] = @unserialize($query->$columns['participating_countries']);
      }
    }

    return $defaults;
  }

  /**
   * CRM_Threepeas_Form_TypeOfRemoteCoaching::buildQuickForm()
   *
   * This method adds the form elements to the form
   *
   * @return void
   */
  public function buildQuickForm() {
    CRM_Utils_System::setTitle('Type of Remote Coaching for Case ID '.$this->case_id);
    // add form elements
    $this->add(
      'select',                         // field type
      'type_remote_coaching',           // field name
      'Type of Remote Coaching',        // field label
      $this->getRemoteCoachingTypes(),  // list of options
      FALSE                              // is required
    );

    $this->add(
      'text',
      'number_participants',
      'Number of Participants',
      TRUE
    );

    $countrySelect = $this->addElement('advmultiselect', 'countries', ts('Participating countries'), $this->getCountries(),
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

  /**
   * CRM_Threepeas_Form_TypeOfRemoteCoaching::postProcess()
   *
   * This method processes the input of the form.
   * It first retrieves the custom fields, then it updates or inserts the new value into the database depending on the selected option in the form.
   *
   * @return void
   */
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
    if(!empty($values['type_remote_coaching'])){
      $field_value_type_remotecoaching = civicrm_api('OptionValue', 'getsingle', array('version' => 3, 'sequential' => 1, 'id' => $values['type_remote_coaching']));
    }
    if(!empty($values['number_participants'])){
      $field_value_number_participants = civicrm_api('OptionValue', 'getsingle', array('version' => 3, 'sequential' => 1, 'id' => $values['number_participants']));
    }
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
    if($dao->N > 0) {
      $arValues = array();
      $num_participants = 0;
      $arValues[1] = array((int)$values['caseId'], 'Integer');
      $arValues[2] = array(!empty($field_value_type_remotecoaching['value'])?$field_value_type_remotecoaching['value']:'','String');

      $sql = "UPDATE {$result_cg_remotecoachingtypes['table_name']} SET `{$fields['type_remote_coaching']}` = %2 ";

      if($field_value_type_remotecoaching['value'] == 'webinar_single_country' || $field_value_type_remotecoaching['value'] == 'webinar_multiple_countries'){
        $num_participants = !empty($field_value_number_participants['value'])?(int)$values['number_participants']:0;
        $arValues[3] = array($num_participants,'Integer');
        $arValues[4] = array($field_value_countries, 'String');

        $sql .= ", `".$fields['number_participants']."` = %3, `".$fields['participating_countries']."` = %4 ";
      } else {
        $arValues[4] = array('', 'String');

        $sql .= ", `".$fields['number_participants']."` = NULL, `".$fields['participating_countries']."` = %4 ";
      }

      $sql .= "WHERE `entity_id` = %1";

      $dao = CRM_Core_DAO::executeQuery($sql, $arValues);
    } else {
      $num_participants = !empty($values['number_participants'])?(int)$values['number_participants']:0;
      $arValues = array();
      $arValues[1] = array((int)$values['caseId'], 'Integer');
      $arValues[2] = array(!empty($field_value_type_remotecoaching['value'])?$field_value_type_remotecoaching['value']:'', 'String');
      $arValues[3] = array($num_participants, 'Integer');
      $arValues[4] = array(!empty($field_value_countries)?$field_value_countries:'', 'String');

      $sql = "INSERT INTO {$result_cg_remotecoachingtypes['table_name']} (`entity_id`, `".$fields['type_remote_coaching']."`, `".$fields['number_participants']."`, `".$fields['participating_countries']."`) ";
      $sql .= "VALUES (%1, %2, %3, %4)";

      $dao = CRM_Core_DAO::executeQuery($sql, $arValues);
    }

    CRM_Utils_System::redirect('/civicrm/contact/view/case?reset=1&action=view&cid='.$values['clientId'].'&id='.$values['caseId']);

    parent::postProcess();
  }

  /**
   * Method to get list of remote coaching types
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
   * Method to get a list of available countries
   *
   * @return array $countries
   */
  private function getCountries() {
    $countries = array();
    try {
      $api_result = civicrm_api3('Country', 'get', array(
        'version' => 3,
        'sequential' => 1,
        'rowCount' => 0
      ));

      foreach ($api_result['values'] as $country) {
        $countries[$country['id']] = $country['name'];
      }
    } catch (CiviCRM_API3_Exception $ex) {}

    asort($countries);
    return $countries;
  }

  /**
   * Overridden parent method to set validation rules
   */
  public function addRules() {
    $this->addFormRule(array('CRM_Threepeas_Form_TypeOfRemoteCoaching', 'validateInput'));
  }

  /**
   * Method to validate the form input on submission of the form
   *
   * @param $fields
   * @return bool|array $errors
   */
  public static function validateInput($fields) {
    $remotecoaching_types = self::getRemoteCoachingTypes();
    if($remotecoaching_types[$fields['type_remote_coaching']] == 'Webinar single country' && !is_int((int)$fields['countries']) ){
      $errors['number_participants'] = ts('Please enter a number in "Number of Participants" field');
      return $errors;
    }
    if($remotecoaching_types[$fields['type_remote_coaching']] == 'Webinar multiple countries' && count($fields['countries']) < 2){
      $errors['countries'] = ts('Please select multiple countries or choose for "Webinar single country"');
      return $errors;
    }

    return TRUE;
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
