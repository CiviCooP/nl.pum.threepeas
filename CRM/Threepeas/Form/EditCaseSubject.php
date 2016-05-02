<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Threepeas_Form_EditCaseSubject extends CRM_Core_Form {
  private $caseId;
  /**
   * Method to build Quick Form
   *
   */
  function buildQuickForm() {

    $this->add("hidden",  "case_id");
    $this->add("text",  "case_subject", ts("Subject"), array(), true);
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  /**
   * Method for processing before building the form
   */
  function preProcess() {
    $this->caseId = CRM_Utils_Request::retrieve("case_id", "Integer");
    CRM_Utils_System::setTitle(ts("Edit Case Subject"));
  }

  /**
   * Method to set default values
   *
   * @return array $defaults
   */
  function setDefaultValues() {
    $params = array("id" => $this->caseId, "return" => "subject");
    try {
      $defaults['case_subject'] = civicrm_api3("Case", "Getvalue", $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $defaults['case_subject'] = "";
    }
    $defaults['case_id'] = $this->caseId;
    return $defaults;
  }

  /**
   * Method to process the submitted form
   */
  function postProcess() {
    $values = $this->exportValues();
    if ($values['case_subject']) {
      $params = array('id' => $values['case_id'], 'subject' => $values['case_subject']);
      try {
        civicrm_api3("Case", "Create", $params);
        CRM_Core_Session::setStatus(ts("Case Subject changed to ".$values['case_subject']), ts('Saved'), "success");
      } catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Session::setStatus(ts("Could not save Case Subject to ".$values['case_subject']), ts('Not Saved'), "error");
      }
    }
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
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
