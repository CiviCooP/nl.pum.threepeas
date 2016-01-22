<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Threepeas_Form_DisabledPumProject extends CRM_Core_Form {
  protected $projectId = null;
  protected $projectType = null;
  protected $context = null;
  /**
   * Function to build the form
   */
  function buildQuickForm() {
    $this->setFormElements();
    parent::buildQuickForm();
  }
  /**
   * Function for processing before building the form
   */
  function preProcess() {
    $this->projectId = CRM_Utils_Request::retrieve('pid', 'Integer', $this);
    $this->context = CRM_Utils_Request::retrieve('context', 'String', $this);
    $this->projectType = strtolower(CRM_Threepeas_BAO_PumProject::getProjectType($this->projectId));
    $this->assign('projectType', $this->projectType);
    $this->setPageTitle();
  }
  /**
   * Function to process form input
   */
  function postProcess() {
    $this->processDelete();
    if ($this->context == 'cases') {
      $message = 'Project '.CRM_Threepeas_BAO_PumProject::getProjectTitleWithId($this->projectId).' removed with all its related cases';
    } else {
      $message = 'Project '.CRM_Threepeas_BAO_PumProject::getProjectTitleWithId($this->projectId).' removed (related cases unlinked)';
    }
    $session = CRM_Core_Session::singleton();
    $session->setStatus($message, 'Project deleted', 'success');
    parent::postProcess();
  }
  /**
   * Function to set form elements based on action
   */
  function setFormElements() {
    $this->add('text', 'title', ts('Title'), array('size' => CRM_Utils_Type::HUGE));
    $this->add('text', 'programme_name', ts('Programme'), array('size' => CRM_Utils_Type::HUGE));
    $this->add('text', 'customer_name', ts('Customer or Country'), array('size' => CRM_Utils_Type::HUGE));
    if ($this->projectType == 'customer') {
      $this->add('text', 'projectmanager_name', ts('Project Manager'), array('size' => CRM_Utils_Type::HUGE));
      $this->add('textarea', 'reason', ts('What is the reason for this request for Assistance?'),
        array('rows'    => 4, 'readonly'=> 'readonly', 'cols'    => 80), false);
      $this->add('textarea', 'work_description', ts('Which project activities do you expect the expert to perform?'),
        array('rows'    => 4, 'readonly'=> 'readonly', 'cols'    => 80), false);
      $this->add('textarea', 'qualifications', ts('Qualifications'),
        array('rows'    => 4, 'readonly'=> 'readonly', 'cols' => 80), false);
      $this->add('text', 'sector_coordinator', ts('Sector Coordinator'), array('size' => CRM_Utils_Type::HUGE));
      $this->add('text', 'representative', ts('Representative'), array('size' => CRM_Utils_Type::HUGE));
      $this->add('text', 'authorised', ts('Authorised Contact'), array('size' => CRM_Utils_Type::HUGE));
    }
    $this->add('textarea', 'expected_results', ts('What are the expected results of the project?'),
      array('rows'    => 4, 'readonly'=> 'readonly', 'cols'    => 80), false);
    $this->add('textarea', 'projectplan', ts('Projectplan (activities to be performed with customer)'), array('rows' => 4, 'readonly'=> 'readonly', 'cols' => 80), false);
    $this->add('text', 'country_coordinator', ts('Country Coordinator'), array('size' => CRM_Utils_Type::HUGE));
    $this->add('text', 'project_officer', ts('Project Officer'), array('size' => CRM_Utils_Type::HUGE));
    $this->addDate('start_date', ts('Start Date'), false);
    $this->addDate('end_date', ts('End Date'), false);
    if ($this->context == 'cases') {
      $confirmTxt = ts('Confirm delete project WITH cases');
    } else {
      $confirmTxt = ts('Confirm delete project ONLY');
    }
    $this->addButtons(array(
      array('type' => 'next', 'name' => $confirmTxt,  'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }
  /**
   * Function to set page title
   */
  function setPageTitle() {
    $formHeader = 'Delete '.ucfirst($this->projectType).' Project ';
    if ($this->context == 'cases') {
      $formHeader .= 'WITH cases';
    } else {
      $formHeader .= 'ONLY';
    }
    CRM_Utils_System::setTitle(ts('Delete Project'));
    $this->assign('formHeader', $formHeader);
  }
  /**
   * Function to set default values
   *
   * @return array $defaults
   */
  function setDefaultValues() {
    $defaults = array();
    $retrievedProject = CRM_Threepeas_BAO_PumProject::getValues(array('id' => $this->projectId));
    $project = $retrievedProject[$this->projectId];
    foreach ($project as $name => $value) {
      switch ($name) {
        case 'customer_id':
          $defaults['customer_name'] = CRM_Threepeas_Utils::getContactName($project['customer_id']);
          break;
        case 'country_id':
          $defaults['customer_name'] = CRM_Threepeas_Utils::getContactName($project['country_id']);
          break;
        case 'programme_id':
          $defaults['programme_name'] = CRM_Threepeas_BAO_PumProgramme::getProgrammeTitleWithId($project['programme_id']);
          break;
        case 'projectmanager_id':
          $defaults['projectmanager_name'] = CRM_Threepeas_Utils::getContactName($project['projectmanager_id']);
          break;
        default:
          $defaults[$name] = $value;
        break;
      }
    }
    if (!empty($project['customer_id'])) {
      $ccId = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($project['customer_id'], 'country_coordinator');
      $scId = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($project['customer_id'], 'sector_coordinator');
      $poId = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($project['customer_id'], 'project_officer');
      $repId = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($project['customer_id'], 'representative');
      $acId = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($project['customer_id'], 'authorised_contact');
    } else {
      $ccId = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($project['country_id'], 'country_coordinator');
      $poId = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($project['country_id'], 'project_officer');
      $repId = CRM_Threepeas_BAO_PumCaseRelation::getRelationId($project['country_id'], 'representative');
    }
    $defaults['country_coordinator'] = CRM_Threepeas_Utils::getContactName($ccId);
    $defaults['project_officer'] = CRM_Threepeas_Utils::getContactName($poId);
    if (!empty($scId)) {
      $defaults['sector_coordinator'] = CRM_Threepeas_Utils::getContactName($scId);
    }
    if (!empty($repId)) {
      $defaults['representative'] = CRM_Threepeas_Utils::getContactName($repId);
    }
    if (!empty($acId)) {
      $defaults['authorised'] = CRM_Threepeas_Utils::getContactName($acId);
    }
    return $defaults;
  }

  /**
   * Method to process the delete of the project, with or without cases
   *
   * @access protected
   */
  protected function processDelete() {
    if ($this->context == 'only') {
      CRM_Threepeas_BAO_PumProject::deleteById($this->projectId);
    } else {
      $projectCases = CRM_Threepeas_BAO_PumProject::getCasesByProjectId($this->projectId);
      foreach ($projectCases as $projectCase) {
        $config = CRM_Threepeas_CaseRelationConfig::singleton();
        $caseParams = array(
          'id' => $projectCase['case_id'],
          'status_id' => $config->getCaseStatusError(),
          'is_deleted' => 1
        );
        try {
          civicrm_api3('Case', 'Create', $caseParams);
        } catch (CiviCRM_API3_Exception $ex) {}
      }
      CRM_Threepeas_BAO_PumProject::deleteById($this->projectId);
    }
  }
}
