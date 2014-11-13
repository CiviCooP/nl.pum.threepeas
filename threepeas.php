<?php
require_once 'threepeas.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function threepeas_civicrm_config(&$config) {
  _threepeas_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function threepeas_civicrm_xmlMenu(&$files) {
  _threepeas_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install

 *  * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function threepeas_civicrm_install() {
  return _threepeas_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function threepeas_civicrm_uninstall() {
  return _threepeas_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 * - populate option values table for PUM projects with PUM projects if 
 *   they do not exist yet
 * - check if extension org.civicoop.general.api.country is active
 * - define constant PUMPROJ_CUSTOM_ID
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 18 Feb 2014
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function threepeas_civicrm_enable() {
  $extensionParams = array('full_name' => 'org.civicoop.general.api.country');
  $extensionDefaults = array();
  $countryApiExtension = CRM_Core_BAO_Extension::retrieve($extensionParams, $extensionDefaults);
  if (!empty($countryApiExtension) && $countryApiExtension->is_active == 1) {
    /*
     * retrieve option group for pum_project
     */
    _threepeasGenerateProjectList();
    return _threepeas_civix_civicrm_enable();
  } else {
    CRM_Core_Error::fatal("Could not enable extension, the required extension org.civicoop.general.api.country is not active in this environment!");
  }
}
/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function threepeas_civicrm_disable() {
  return _threepeas_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function threepeas_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _threepeas_civix_civicrm_upgrade($op, $queue);
}
/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function threepeas_civicrm_caseTypes(&$caseTypes) {
  _threepeas_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function threepeas_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _threepeas_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
/**
 * Implementation of hook civicrm_navigationMenu
 * to create a programmes, projects and products menu and menu items
 * 
 * @param array $params
 */
function threepeas_civicrm_navigationMenu( &$params ) {
  $maxKey = ( max( array_keys($params) ) );
  $params[$maxKey+1] = array (
    'attributes' => array (
      'label'      => 'Programmes, Projects and Products',
      'name'       => 'Programmes, Projects and Products',
      'url'        => null,
      'permission' => 'edit all contacts',
      'operator'   => null,
      'separator'  => null,
      'parentID'   => null,
      'navID'      => $maxKey+1,
      'active'     => 1
    ),
    'child' =>  array (
      '1' => array (
        'attributes' => array (
            'label'      => 'List Programmes',
            'name'       => 'List Programmes',
            'url'        => 'civicrm/programmelist',
            'operator'   => null,
            'separator'  => 0,
            'parentID'   => $maxKey+1,
            'navID'      => 1,
            'active'     => 1
            ),
        'child' => null
      ), 
      '2' => array (
        'attributes' => array (
          'label'      => 'New Programme',
          'name'       => 'New Programme',
          'url'        => CRM_Utils_System::url('civicrm/pumprogramme', 'action=add', true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 2,
          'active'     => 1
        ),
        'child' => null
      ),
      '3' => array (
        'attributes' => array (
          'label'      => 'List Projects',
          'name'       => 'List Projects',
          'url'        => 'civicrm/projectlist',
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 3,
          'active'     => 1
        ),
        'child' => null
      ), 
      '4' => array (
        'attributes' => array (
          'label'      => 'New Country Project',
          'name'       => 'New Country Project',
          'url'        => CRM_Utils_System::url('civicrm/pumproject', 'action=add', true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 4,
          'active'     => 1
        ),
        'child' => null
      ), 
      '5' => array (
        'attributes' => array (
          'label'      => 'List Products',
          'name'       => 'List Products',
          'url'        => CRM_Utils_System::url('civicrm/case/search', 'reset=1', true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 5,
          'active'     => 1
        ),
        'child' => null
      ), 
      '6' => array (
        'attributes' => array (
          'label'      => 'Add Product',
          'name'       => 'Programmes Report',
          'url'        => CRM_Utils_System::url('civicrm/case/add', 'reset=1&action=add&atype=13&context=standalone'),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 6,
          'active'     => 1
        ),
        'child' => null                
      )));
}
/**
 * Implementation of hook civicrm_tabs to add a tab for Projects for 
 * contract subtype Customer
 * 
 * Remove all tabs save Documentation and Project for contact_sub_type Country
 * 
 * @param array $tabs
 * @param int $contactID
 */
function threepeas_civicrm_tabs(&$tabs, $contactID) {
  $threepeasConfig = CRM_Threepeas_Config::singleton();
  /*
   * first check if contact_subtype is country
   */
  if (_threepeasContactIsCountry($contactID) == TRUE) {
    $activeCountryTabs = array('contact_documents', 'rel', 'case', 'activity', 'participant');
    foreach ($tabs as $tabKey => $tab) {
      $projectWeight = $tab['weight']++;
      if (!in_array($tab['id'], $activeCountryTabs)) {
        unset($tabs[$tabKey]);
      }
    }
    $tabs[] = _threepeasAddProjectTab($contactID, $threepeasConfig->countryContactType, $projectWeight);
  } else {
    if (_threepeasContactIsCustomer($contactID) == TRUE) {
    foreach ($tabs as $tabKey => $tab) {
      $projectWeight = $tab['weight']++;
    }
      $tabs[] = _threepeasAddProjectTab($contactID, $threepeasConfig->customerContactType, $projectWeight);
    }
  }
}
/**
 * Function to add the project tab to the summary page
 * 
 * @param int $contactId
 * @param string $customerType
 * @param int $projectWeight
 * @return array $projectTab
 */
function _threepeasAddProjectTab($contactId, $customerType, $projectWeight = 0) {
  $projectCount = CRM_Threepeas_BAO_PumProject::countCustomerProjects($contactId, $customerType);
  $projectUrl = CRM_Utils_System::url('civicrm/projectlist','snippet=1&cid='.$contactId.'&type='.$customerType);
  $projectTab = array( 
    'id'    => 'customerProjects',
    'url'       => $projectUrl,
    'title'     => 'Projects',
    'weight'    => $projectWeight++,
    'count'     => $projectCount);
  return $projectTab;
}
/**
 * Implementation of hook_civicrm_custom
 * - automatically create project in table civicrm_project when custom group
 *   Projectinformation gets new record. Created based on webform Projectrequest
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 23 Apr 2014
 * @param string $op
 * @param int $groupID
 * @param int $entityID
 * @param array $params
 */
function threepeas_civicrm_custom($op, $groupID, $entityID, &$params ) {
  /*
   * if groupID = PUM project custom group and option is create, create
   * pum project
   */
  $threepeasConfig = CRM_Threepeas_Config::singleton();
  /*
   * project request from webform
   */
  if ($groupID == $threepeasConfig->projectCustomGroupId && $op == 'create') {
    /*
     * only add project if case projectintake is NOT created from CiviCRM UI
     */
    if (isset($GLOBALS['pum_project_ignore']) && $GLOBALS['pum_project_ignore'] == 1) {
      $GLOBALS['pum_project_ignore'] = 0;
    } else {
      $pumProject = _threepeasSetProject($params);
      /*
       * retrieve case for subject and client
       */ 
      $apiCase = civicrm_api3('Case', 'Getsingle', array('case_id' => $entityID));
      if (isset($apiCase['client_id'][1])) {
        $pumProject['customer_id'] = $apiCase['client_id'][1];
      }
      $pumProject['is_active'] = 1;
      $createdProject = CRM_Threepeas_BAO_PumProject::add($pumProject);
      _threepeasGenerateProjectTitle($createdProject['id'], $createdProject['customer_id']);
      $pumCaseProject = array('case_id' => $entityID, 'project_id' => $createdProject['id'], 'is_active' => 1);
      CRM_Threepeas_BAO_PumCaseProject::add($pumCaseProject);
    } 
  }
  /*
   * country project from webform
   */
    if ($groupID == $threepeasConfig->capCustomGroupId && $op == 'create') {
    /*
     * only add project if case CAP is NOT created from CiviCRM UI
     */
    if (isset($GLOBALS['pum_project_ignore']) && $GLOBALS['pum_project_ignore'] == 1) {
      $GLOBALS['pum_project_ignore'] = 0;
    } else {
      $pumProject = _threepeasSetCountryProject($params);
      /*
       * retrieve case for country
       */ 
      $apiCase = civicrm_api3('Case', 'Getsingle', array('case_id' => $entityID));
      if (isset($apiCase['client_id'][1])) {
        $pumProject['country_id'] = $apiCase['client_id'][1];
      }
      $pumProject['is_active'] = 1;
      $createdProject = CRM_Threepeas_BAO_PumProject::add($pumProject);
      if (isset($createdProject['country_id']) && !empty($createdProject['country_id'])) {
        _threepeasGenerateProjectTitle($createdProject['id'], $createdProject['country_id']);
      }
      $pumCaseProject = array('case_id' => $entityID, 'project_id' => $createdProject['id'], 'is_active' => 1);
      CRM_Threepeas_BAO_PumCaseProject::add($pumCaseProject);
    } 
  }
}
/**
 * Function to generate the project title (Issue 90)
 */
function _threepeasGenerateProjectTitle($projectId, $customerId) {
  try {
    $customerName = civicrm_api3('Contact', 'Getvalue', array('id' => $customerId, 'return' => 'display_name'));
  } catch (CiviCRM_API3_Exception $ex) {
    $customerName = '';
  }
  $projectTitle = 'Project '.$customerName.'-'.$projectId;
  $params = array('id' => $projectId, 'title' => $projectTitle);
  CRM_Threepeas_BAO_PumProject::add($params);
}
/**
 * Function to set basic data for pum project
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 23 Apr 2014
 * @param array $params
 * @return array $result
 */
function _threepeasSetProject($params) {
  $result = array();
  $usedCustomFields = array('reason', 'activities', 'expected_results');
  $threepeasConfig = CRM_Threepeas_Config::singleton();
  $customFields = civicrm_api3('CustomField', 'Get', array('custom_group_id' => $threepeasConfig->projectCustomGroupId));
  foreach ($customFields['values'] as $customFieldId => $customField) {
    if (in_array($customField['name'], $usedCustomFields)) {
      foreach ($params as $param) {
        if ($param['custom_field_id'] == $customFieldId) {
          switch($customField['name']) {
            case "activities":
              $result['work_description'] = trim($param['value']);
              break;
            case "expected_results":
              $result['expected_results'] = trim($param['value']);
              break;
            case "reason":
              $result['reason'] = trim($param['value']);
              break;
          }
        }
      }
    }
  }
  return $result;
}
/**
 * Function to set basic data for pum country project
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 15 Sep 2014
 * @param array $params
 * @return array $result
 */
function _threepeasSetCountryProject($params) {
  $result = array();
  $usedCustomFields = array('goals');
  $threepeasConfig = CRM_Threepeas_Config::singleton();
  $customFields = civicrm_api3('CustomField', 'Get', array('custom_group_id' => $threepeasConfig->capCustomGroupId));
  foreach ($customFields['values'] as $customFieldId => $customField) {
    if (in_array($customField['name'], $usedCustomFields)) {
      foreach ($params as $param) {
        if ($param['custom_field_id'] == $customFieldId) {
          switch($customField['name']) {
            case "goals":
              $result['goals'] = trim($param['value']);
              break;
          }
        }
      }
    }
  }
  return $result;
}
/**
 * Implementation of hook civicrm_buildForm
 * 
 * add project to CRM_Case_Form_Case and CRM_Case_Form_CaseView
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 19 May 2014
 */
function threepeas_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Case_Form_CaseView') {
    _threepeasAddProjectElementCaseView($form);
    $caseId = $form->getVar('_caseID');
    $form->addButtons(array(
        array('type' => 'cancel',
          'name' => ts("Done"),
          'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
          'isDefault' => TRUE,
        ),
        array('type' => 'next', 'name' => ts('Save Donation Links')),
      )
    );
    $contributionsList = _threepeasGetContributionsList();
    $form->add('advmultiselect', 'new_link', '', $contributionsList, false,  
      array('size' => count($contributionsList), 'style' => 'width:auto; min-width:300px;',
        'class' => 'advmultiselect',
      ));
    $params = array('entity' => 'Case', 'entity_id' => $caseId, 'donation_entity' => 'Contribution', 'is_active' => 1);
    $currentContributions = CRM_Threepeas_BAO_PumDonorLink::getValues($params);
    foreach ($currentContributions as $currentContribution) {
      $defaults['new_link'][] = $currentContribution['donation_entity_id'];
    }
    if (!empty($defaults)) {
      $form->setDefaults($defaults);
    }
  }
  if ($formName == 'CRM_Contribute_Form_ContributionView') {
    $action = $form->getVar('_action');
    if ($action != CRM_Core_Action::DELETE) {
      $contributionId = CRM_Utils_Request::retrieve('id', 'Positive');
      _threepeasAddDonorLinkElements($action, $form, $contributionId);
    }
  }
  if ($formName == 'CRM_Contribute_Form_Contribution') {
    $action = $form->getVar('_action');
    if ($action != CRM_Core_Action::DELETE) {
      $contributionId = $form->getVar('_id');
      _threepeasAddDonorLinkElements($action, $form, $contributionId);
    }    
  }
  if ($formName == 'CRM_Case_Form_Case') {
    $action = $form->getVar('_action');
    if ($action != CRM_Core_Action::DELETE) {
      _threepeasSetDefaultCaseSubject($form);
      _threepeasAddProjectElementCase($form);
      $contributionsList = _threepeasGetContributionsList();
      $form->add('advmultiselect', 'new_link', '', $contributionsList, false,  
        array('size' => count($contributionsList), 'style' => 'width:auto; min-width:300px;',
          'class' => 'advmultiselect',
        ));
    }
  }
}
/**
 * Implementation of hook civicrm_postProcess
 */
function threepeas_civicrm_postProcess($formName, &$form) {
  /*
   * manage case donation links
   */
  if ($formName == 'CRM_Case_Form_CaseView') {
    $values = $form->exportValues();
    $caseId = $form->getVar('_caseID');
    _threepeasCaseDonationLinks($values, $caseId);
    $session = CRM_Core_Session::singleton();
    CRM_Utils_System::redirect($session->readUserContext());
  }
  /*
   * manage data in civicrm_case_project
   */
  if ($formName == 'CRM_Case_Form_Case') {
    $action = $form->getVar('_action');
    switch ($action) {
      case CRM_Core_Action::ADD:
        $values = $form->exportValues();
        _threepeasAddCaseProject($values);
        _threepeasProcessCaseDonorLink($values);
        break;
      case CRM_Core_Action::DELETE:
        $caseId = $form->getVar('_caseId');
        _threepeasDisableCaseProject($caseId);
        break;
    }
  }
  /*
   * manage data in civicrm_donor_link
   */
  if ($formName == 'CRM_Contribute_Form_Contribution') {
    $exportValues = $form->exportValues();
    $action = $form->getVar('_action');
    $contributionId = $form->getVar('_id');
    _threepeasProcessDonorLinkData($action, $contributionId, $exportValues);
  }
}
/**
 * Function to process donor link data from form into tables
 * civicrm_contribution_number_projects and civicrm_donor_link
 */
function _threepeasProcessDonorLinkData($action, $contributionId, $formValues) {
  if ($action == CRM_Core_Action::ADD) {
    $contributionId = _threepeasGetLatestContributionId();
  }
  if (isset($formValues['numberProjects'])) {
    _threepeasCreateContributionNumberProjects($contributionId, $formValues['numberProjects']);
  }
  if (!empty($formValues['programmeSelect']) || !empty($formValues['projectSelect']) || !empty($formValues['caseSelect'])) {
    _threepeasCreateDonorLink($contributionId, $formValues);
  }
}
/**
 * Function to add or update donor link record
 */
function _threepeasCreateDonorLink($contributionId, $formValues) {
  $params = array('donation_entity' => 'Contribution', 'donation_entity_id' => $contributionId, 
    'is_active' => 1);
  if (!empty($formValues['programmeSelect'])) {
    $params['entity'] = 'Programme';
    $params['entity_id'] = $formValues['programmeSelect'];
    CRM_Threepeas_BAO_PumDonorLink::add($params);
  }
  if (!empty($formValues['projectSelect'])) {
    $params['entity'] = 'Project';
    $params['entity_id'] = $formValues['projectSelect'];
    CRM_Threepeas_BAO_PumDonorLink::add($params);
  }
  if (!empty($formValues['caseSelect'])) {
    $params['entity'] = 'Case';
    $params['entity_id'] = $formValues['caseSelect'];
    CRM_Threepeas_BAO_PumDonorLink::add($params);
  }
}
/**
 * Function to update or create record in civicrm_contribution_number_projects
 */
function _threepeasCreateContributionNumberProjects($contributionId, $numberProjects) {
  $params = array('contribution_id' => $contributionId, 'number_projects' => $numberProjects);
  CRM_Threepeas_BAO_PumContributionProjects::add($params);
}
/**
 * Function to get latest contribution Id
 */
function _threepeasGetLatestContributionId() {
  $contributionId = 0;
  $daoContribution = CRM_Core_DAO::executeQuery('SELECT MAX(id) AS maxId FROM civicrm_contribution');
  if ($daoContribution->fetch()) {
    $contributionId = $daoContribution->maxId;
  }
  return $contributionId;
}
/**
 * Function to disable CaseProject
 * 
 * @param int $caseId
 */
function _threepeasDisableCaseProject($caseId) {
  if (!empty($caseId)) {
    CRM_Threepeas_BAO_PumCaseProject::disableByCaseId($caseId);
  }
}
/**
 * Function to add CaseProject
 * 
 * @param array $values
 */
function _threepeasAddCaseProject($values) {
  if (isset($values['project_id']) && !empty($values['project_id'])) {
    /*
     * retrieve latest case_id
     */
    $daoCase = CRM_Core_DAO::executeQuery('SELECT MAX(id) as maxId FROM civicrm_case');
    if ($daoCase->fetch()) {
      $params = array(
        'is_active' => 1,
        'case_id' => $daoCase->maxId,
        'project_id' => $values['project_id']);
      CRM_Threepeas_BAO_PumCaseProject::add($params);
    }
  }
}
/**
 * Function to retrieve project option values
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 21 May 2014
 *
 */
function _threepeasGenerateProjectList() {
  $optionGroupId = _threepeasCreateOptionGroup('pum_project');
  $delQuery = "DELETE FROM civicrm_option_value WHERE option_group_id = ".$optionGroupId;
  CRM_Core_DAO::executeQuery($delQuery);
  $params['option_group_id'] = $optionGroupId;
  $params['is_active'] = 1;
  $params['is_reserved'] = 1;
  $params['value'] = 0;
  $params['label'] = '- none';
  civicrm_api3('OptionValue', 'Create', $params);
  $query = 'SELECT * FROM civicrm_project WHERE is_active = 1';
  $dao = CRM_Core_DAO::executeQuery($query);
  while ($dao->fetch()) {
    $params['value'] = $dao->id;
    $params['label'] = $dao->title;
    civicrm_api3('OptionValue', 'Create', $params);
  }
}
/**
 * Function to create option group if not exist
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 21 May 2014
 * @param string $name
 * @return int $optionGroupId
 */
function _threepeasCreateOptionGroup($name) {
 $countGroup = civicrm_api3('OptionGroup', 'Getcount', array('name' => $name));
 switch ($countGroup) {
   case 0:
     $params = array('name' => $name, 'title' => 'active projects', 'is_active' => 1, 'is_reserved' => 1);
     $optionGroup = civicrm_api3('OptionGroup', 'Create', $params);
     $optionGroupId = $optionGroup['id'];
     $showError = FALSE;
     break;
   case 1:
     $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', array('name' => $name, 'return' => 'id'));
     $showError = FALSE;
     break;
   default:
     throw new Exception('Could not create option group pum_project, there are already '
       .$countGroup.' option groups with that name. Correct and try again.');
     break;
 }
 return $optionGroupId;
}
/**
 * Function to add donation application to form
 */
function _threepeasAddDonorLinkElements($action, &$form, $contributionId) {
  $programmeList = _threepeasGetDonorLinkProgrammeList($contributionId);
  $projectList = _threepeasGetDonorLinkProjectList($contributionId);
  $caseList = _threepeasGetDonorLinkCaseList($contributionId);
  $form->addElement('text', 'programmeCount', ts('Current Linked Programmes'));
  $form->addElement('select', 'programmeSelect', ts('Link to Programme :'), $programmeList);
  $form->addElement('text', 'projectCount', ts('Current Linked Projects'));
  $form->addElement('select', 'projectSelect', ts('Link to Project :'), $projectList);
  $form->addElement('text', 'caseCount', ts('Current Linked Main Act.'));
  $form->addElement('select', 'caseSelect', ts('Link to Main Activity :'), $caseList);
  $form->addElement('text', 'numberProjects', ts('Number of Projects'));
  $defaults = _threepeasDonorLinkDefaults($contributionId, $action);
  if (!empty($defaults)) {
    $form->setDefaults($defaults);
  }
  CRM_Core_Region::instance('page-body')->add(array('template' => 'CRM/Threepeas/Page/ContributionDonorLink.tpl'));
}
/**
 * Function to retrieve programmeList for contribution donor link
 */
function _threepeasGetDonorLinkProgrammeList($contributionId) {
  /*
   * get all programmes from config
   */
  $threepeasConfig = CRM_Threepeas_Config::singleton();
  $allPrograms = $threepeasConfig->activeProgrammeList;
  /*
   * remove entries that are already linked to contribution
   */
  $params = array('entity' => 'Programme', 'donation_entity_id' => $contributionId, 'is_active' => 1);
  $linkedPrograms = CRM_Threepeas_BAO_PumDonorLink::getValues($params);
  foreach ($linkedPrograms as $linkedProgram) {
    unset($allPrograms[$linkedProgram['entity_id']]);
  }
  return $allPrograms;
}
/**
 * Function to retrieve projectlist for contribution donor link
 */
function _threepeasGetDonorLinkProjectList($contributionId) {
  /*
   * get all projects from config
   */
  $threepeasConfig = CRM_Threepeas_Config::singleton();
  $allProjects = $threepeasConfig->activeProjectList;
  /*
   * remove entries that are already linked to contribution
   */
  $params = array('entity' => 'Project', 'donation_entity_id' => $contributionId, 'is_active' => 1);
  $linkedProjects = CRM_Threepeas_BAO_PumDonorLink::getValues($params);
  foreach ($linkedProjects as $linkedProject) {
    unset($allProjects[$linkedProject['entity_id']]);
  }
  return $allProjects;
}
/**
 * Function to retrieve caselist for contribution donor link
 */
function _threepeasGetDonorLinkCaseList($contributionId) {
  /*
   * get all cases from config
   */
  $threepeasConfig = CRM_Threepeas_Config::singleton();
  $allCases = $threepeasConfig->activeCaseList;
  /*
   * remove entries that are already linked to contribution
   */
  $params = array('entity' => 'Case', 'donation_entity_id' => $contributionId, 'is_active' => 1);
  $linkedCases = CRM_Threepeas_BAO_PumDonorLink::getValues($params);
  foreach ($linkedCases as $linkedCase) {
    unset($allCases[$linkedCase['entity_id']]);
  }
  return $allCases;
}
/**
 * Function to set defaults for donor link data
 */
function _threepeasDonorLinkDefaults($contributionId, $action) {
  $defaults = array();
  if ($action == CRM_Core_Action::UPDATE || $action == CRM_Core_Action::VIEW) {
    $contributionProjects = CRM_Threepeas_BAO_PumContributionProjects::getValues(
      array('contribution_id' => $contributionId));
    if (isset($contributionProjects[$contributionId]['number_projects'])) {
      $defaults['numberProjects'] = $contributionProjects[$contributionId]['number_projects'];
    }
    $caseCount = _threepeasGetContributionDonorLink('Case',$contributionId);
    $defaults['caseCount'] = $caseCount;
    $projectCount = _threepeasGetContributionDonorLink('Project',$contributionId);
    $defaults['projectCount'] = $projectCount;
    $programmeCount = _threepeasGetContributionDonorLink('Programme', $contributionId);
    $defaults['programmeCount'] = $programmeCount;
  }
  return $defaults;
}
/**
 * Function to get donor links for contribution
 */
function _threepeasGetContributionDonorLink($donationEntity, $donationEntityId) {
  if (empty($donationEntity) || empty($donationEntityId)) {
    return 0;
  }
  $count = CRM_Threepeas_BAO_PumDonorLink::getContributionCount($donationEntity, $donationEntityId);
  return $count;
}
/**
 * Function to add project element to case
 */
function _threepeasAddProjectElementCase(&$form) {
  $projectParams = array();
  $projectParams['is_active'] = 1;
  $currentlyViewedContactId = $form->getVar('_currentlyViewedContactId');
  if (!empty($currentlyViewedContactId)) {
    if (_threepeasContactIsCountry($currentlyViewedContactId) == TRUE) {
      $projectParams['country_id'] = $currentlyViewedContactId;
    } else {
      $projectParams['customer_id'] = $currentlyViewedContactId;
    }
    $projectList = array();
    $projects = CRM_Threepeas_BAO_PumProject::getValues($projectParams);
    foreach ($projects as $projectId => $project) {
      $projectList[$projectId] = $project['title'];
    }
    $projectList[0] = '- select -';
    asort($projectList);
    $form->addElement('select', 'project_id', ts('Parent Project'), $projectList);
    /*
     * if option = create, check if there is a project id in the entryURL and if so
     * default to that value
     */
    $action = $form->getvar('_action');
    if ($action === CRM_Core_Action::ADD) {
      $projectId = CRM_Utils_Request::retrieve('pid', 'String');
      if ($projectId) {
        $defaults['project_id'] = $projectId;
        $form->setDefaults($defaults);
        $form->freeze('project_id');
      }
    }
    /*
     * set global var to ensure no new project is added for projectintake
     */
    $GLOBALS['pum_project_ignore'] = 1;
  }
}
/*
 * Function to add project element to case view
 */
function _threepeasAddProjectElementCaseView(&$form) {
  /*
   * retrieve and show project title
   */
  $caseId = $form->getVar('_caseID');
  $caseProjects = CRM_Threepeas_BAO_PumCaseProject::getValues(array('case_id' => $caseId));
  foreach ($caseProjects as $caseProject) {
    $projectId = $caseProject['project_id'];
  }
  if (isset($projectId) && !empty($projectId)) {
    $projects = CRM_Threepeas_BAO_PumProject::getValues(array('id' => $projectId));
    if (isset($projects[$projectId]['title'])) {
      $form->assign('project_title', $projects[$projectId]['title']);
    }
  }
}
/**
 * Implementation of hook civicrm_post
 * Issue 116: delete projects and case/projects when contact is deleted
 *            (core issue CRM-9562 make sure not deleted when trashed
 *             because trash functionality trigger post hook with trash operation
 *             AND delete operation
 *            https://issues.civicrm.org/jira/browse/CRM-9562?jql=text%20~%20%22post%20hook%20contact%20trash%22)
 * 
 * Issue 86: set default PUM case roles on Open Case activity (because
 *           post on Case Create does not have client yet)
 */
function threepeas_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  /*
   * issue 116
   */
  if ($objectName == 'Organization') {
    if ($op == 'trash') {
      $GLOBALS['trashedOrganizationId'] = $objectId;
    }
    if ($op == 'delete') {
      _threepeasDeleteProject($objectId);
    }
  }
  /*
   * issue 86
   */
  if ($objectName =='Activity' && $op == 'create') {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    /*
     * issue 810 attach sector coordinator after Assessment Rep
     */
    if ($objectRef->activity_type_id == $threepeasConfig->getAssessmentRepActTypeId()) {
      CRM_Threepeas_BAO_PumCaseRelation::set_sector_coordinator_from_activity($objectRef);
    }
    
    if ($objectRef->activity_type_id == $threepeasConfig->openCaseActTypeId) {
      $caseQry = 'SELECT case_type_id, start_date FROM civicrm_case WHERE id = %1';
      $caseParams = array(1 => array($objectRef->case_id, 'Positive'));
      $daoCase = CRM_Core_DAO::executeQuery($caseQry, $caseParams);
      if ($daoCase->fetch()) {
        /*
         * strip Core_DAO::VALUE_SEPARATORs from case_type
         */
        $typeParts = explode(CRM_Core_DAO::VALUE_SEPARATOR, $daoCase->case_type_id);
        if (isset($typeParts[1])) {
          $typeId = $typeParts[1];
        }
        /*
         * reform open case subject if necessary
         */
        _threepeasReformOpenCaseSubject($objectRef->case_id, $typeId, $objectId, $objectRef->subject);
        
        if (isset($threepeasConfig->caseTypes[$typeId])) {
          if (empty($daoCase->start_date)) {
            $caseStartDate = date('Ymd');
          } else {
            $caseStartDate = date('Ymd', strtotime($daoCase->start_date));
          }
          $actContactQry = 'SELECT contact_id FROM civicrm_activity_contact WHERE '
            . 'activity_id = %1 AND record_type_id = %2';
          $actContactParams = array(
            1 => array($objectId, 'Positive'),
            2 => array($threepeasConfig->actTargetRecordType, 'Positive'));
          $daoActContact = CRM_Core_DAO::executeQuery($actContactQry, $actContactParams);
          if ($daoActContact->fetch()) {
            CRM_Threepeas_BAO_PumCaseRelation::create_default_case_roles($objectRef->case_id, 
              $daoActContact->contact_id, $caseStartDate, $typeId);
          }
        }
      }
    }
  }
  if ($objectName == 'Contribution') {
    if ($op == 'delete') {
      _threepeasDeleteContributionEnhancedData($objectId);
    }
  }
  if ($objectName == 'Case' && $op == 'create') {
    /*
     * issue 515 put case type and case_id in subject if required
     */
    _threepeasReformCaseSubject($objectId, $objectRef->subject, $objectRef->case_type_id);
  }
  /*
   * issue 772 Remove tag if contact type is Country
   */
  if ($objectName == 'EntityTag' && $op == 'create') {
    _threepeasRemoveCountryTag($objectId, $objectRef);
  }
}
/**
 * Function to delete Tags for Countries
 */
function _threepeasRemoveCountryTag($tagId, $objectRef) {
  foreach ($objectRef as $refElement) {
    if (!is_array($refElement)) {
      $entity = $refElement;
    } else {
      $entityId = $refElement[0];
    }
    if (isset($entity) && $entity == 'civicrm_contact' && 
      _threepeasContactIsCountry($entityId) == TRUE) {
      $query = 'DELETE FROM civicrm_entity_tag WHERE entity_table = %1 '
        . 'AND entity_id = %2 AND tag_id = %3';
      $params = array(
        1 => array('civicrm_contact', 'String'),
        2 => array($entityId, 'Positive'),
        3 => array($tagId, 'Positive'));
      CRM_Core_DAO::executeQuery($query, $params);
    }
  } 
}
/**
 * Function to delete additional data for contribution
 * (civicrm_donor_link and civicrm_contribution_number_projects)
 */
function _threepeasDeleteContributionEnhancedData($contributionId) {
  CRM_Threepeas_BAO_PumContributionProjects::deleteById($contributionId);
  CRM_Threepeas_BAO_PumDonorLink::deleteByDonationEntityId('Contribution', $contributionId);
  /*
   * issue 86
   */
  if ($objectName =='Activity' && $op == 'create') {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    if ($objectRef->activity_type_id == $threepeasConfig->openCaseActTypeId) {
      /*
       * case and later activity contact retrieved from DB and not with API because 
       * API transaction mucks up the Case transaction causing weird errors like 
       * can not find xml file for case type
       */
      $caseQry = 'SELECT case_type_id, start_date FROM civicrm_case WHERE id = %1';
      $caseParams = array(1 => array($objectRef->case_id, 'Positive'));
      $daoCase = CRM_Core_DAO::executeQuery($caseQry, $caseParams);
      if ($daoCase->fetch()) {
        /*
         * substr because case_type_id is between Core_DAO::VALUE_SEPARATORs
         */
        $typeId = substr($daoCase->case_type_id, 1, 1);
        if (isset($threepeasConfig->caseTypes[$typeId])) {
          if (empty($daoCase->start_date)) {
            $caseStartDate = date('Ymd');
          } else {
            $caseStartDate = date('Ymd', strtotime($daoCase->start_date));
          }
          $actContactQry = 'SELECT contact_id FROM civicrm_activity_contact WHERE activity_id = %1 AND record_type_id = %2';
          $actContactParams = array(
            1 => array($objectId, 'Positive'),
            2 => array($threepeasConfig->actTargetRecordType, 'Positive'));
          $daoActContact = CRM_Core_DAO::executeQuery($actContactQry, $actContactParams);
          if ($daoActContact->fetch()) {
            CRM_Threepeas_BAO_PumProject::setDefaultCaseRoles($objectRef->case_id, $daoActContact->contact_id, $caseStartDate);
          }
        }
      }
    }
  }
}
/**
 * Function to delete projects for a contact
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 24 Jun 2014
 * @param int $contactId
 */
function _threepeasDeleteProject($contactId) {
  $deleteProjects = TRUE;
  if (isset($GLOBALS['trashedOrganizationId'])) {
    if ($contactId == $GLOBALS['trashedOrganizationId']) {
      $deleteProjects = FALSE;
      unset($GLOBALS['trashedOrganizationId']);
    }
  }
  if ($deleteProjects == TRUE) {
    $deleteProjects = FALSE;
    try {
      $contact = civicrm_api3('Contact', 'Getsingle', array('contact_id' => $contactId));
      $threepeasConfig = CRM_Threepeas_Config::singleton();
      foreach($contact['contact_sub_type'] as $subType) {
        if ($subType == $threepeasConfig->countryContactType 
          || $subType == $threepeasConfig->customerContactType) {
          $deleteProjects = TRUE;
        }
      }
      if ($deleteProjects == TRUE) {
        CRM_Threepeas_BAO_PumProject::deleteByContactId($contact['contact_id'], $subType);
      }
    } catch (CiviCRM_API3_Exception $ex) {
    }
  }  
}
/**
 * Function to check if the contact is a country (sub type specific for PUM)
 * 
 * @param int $contactId
 * @return boolean
 */
function _threepeasContactIsCountry($contactId) {
  if (empty($contactId)) {
    return FALSE;
  }
  try {
    $contactData = civicrm_api3('Contact', 'Getsingle', array('contact_id' => $contactId));
    if (isset($contactData['contact_sub_type']) && !empty($contactData['contact_sub_type'])) {
      $threepeasConfig = CRM_Threepeas_Config::singleton();
      foreach ($contactData['contact_sub_type'] as $contactSubType) {
        if ($contactSubType == $threepeasConfig->countryContactType) {
          return TRUE;
        }
      }
    } else {
      return FALSE;
    }
  } catch (CiviCRM_API3_Exception $ex) {
    return FALSE;
  }
}
/**
 * Function to check if the contact is a customer (sub type specific for PUM)
 * 
 * @param int $contactId
 * @return boolean
 */
function _threepeasContactIsCustomer($contactId) {
  if (empty($contactId)) {
    return FALSE;
  }
  try {
    $contactData = civicrm_api3('Contact', 'Getsingle', array('contact_id' => $contactId));
    if (isset($contactData['contact_sub_type']) && !empty($contactData['contact_sub_type'])) {
      $threepeasConfig = CRM_Threepeas_Config::singleton();
      foreach ($contactData['contact_sub_type'] as $contactSubType) {
        if ($contactSubType == $threepeasConfig->customerContactType) {
          return TRUE;
        }
      }
    } else {
      return FALSE;
    }
  } catch (CiviCRM_API3_Exception $ex) {
    return FALSE;
  }
}
/**
 * Implementation of hook civicrm_alterTemplateFile
 * Use special template for contact sub type Country
 * 
 * @param string $formName
 * @param object $form
 * @param string $context
 * @param string $tplName
 */
function threepeas_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName) {
  if ($formName === 'CRM_Contact_Page_View_Summary') {
    $contactId = $form->getVar('_contactId');
    if (_threepeasContactIsCountry($contactId) == TRUE) {
      $tplName = 'CRM/Threepeas/Page/CountryView.tpl';
    }
  }
}
/**
 * Function to retrieve active contributions for select list
 */
function _threepeasGetContributionsList() {
  $optionContributions = array();
  $threepeasConfig = CRM_Threepeas_Config::singleton();
  $params = array('is_test' => 0, 'options' => array('limit' => 9999));
  $contributions = civicrm_api3('Contribution', 'Get', $params);
  /*
   * add active contributions to option list
   */
  foreach ($contributions['values'] as $contribution) {
    if (isset($threepeasConfig->activeContributionStatus[$contribution['contribution_status_id']])) {
      $status = $threepeasConfig->activeContributionStatus[$contribution['contribution_status_id']];
      $optionText = $contribution['display_name'];
      $optionContributions[$contribution['contribution_id']] = $optionText;
    }
  }
  asort($optionContributions);
  return $optionContributions;
}
/**
 * Function to create links from contribution to cases
 */
function _threepeasProcessCaseDonorLink($values) {
  if (isset($values['new_link'])) {
    if (!isset($values['case_id'])) {
      $daoMaxCaseId = CRM_Core_DAO::executeQuery('SELECT MAX(id) AS maxCaseId FROM civicrm_case');
      if ($daoMaxCaseId->fetch()) {
        $caseId = $daoMaxCaseId->maxCaseId;
      }
    } else {
      $caseId = $values['case_id'];
    }
    if (!empty($caseId)) {
      CRM_Threepeas_BAO_PumDonorLink::deleteByEntityId('Case', $caseId);
      foreach ($values['new_link'] as $newLink) {
        $params = array(
          'donation_entity' => 'Contribution', 
          'donation_entity_id' => $newLink,
          'entity' => 'Case',
          'entity_id' => $caseId,
          'is_active' => 1);
        CRM_Threepeas_BAO_PumDonorLink::add($params);
      }
    }
  }
}
/**
 * function to reset the donation links for cases
 */
function _threepeasCaseDonationLinks($values, $caseId) {
  /*
   * if update, delete all current donor links for case
   */
  CRM_Threepeas_BAO_PumDonorLink::deleteByEntityId('Case', $caseId);
  /*
   * add new donor links
   */
  foreach ($values['new_link'] as $newLink) {
    $params = array(
      'donation_entity' => 'Contribution', 
      'donation_entity_id' => $newLink,
      'entity' => 'Case',
      'entity_id' => $caseId,
      'is_active' => 1);
    CRM_Threepeas_BAO_PumDonorLink::add($params);
  }  
}
/**
 * Function to set a default case subject
 */
function _threepeasSetDefaultCaseSubject(&$form) {
  $activitySubject = null;
  $caseId = $form->getVar('_caseId');
  $caseTypeId = $form->getVar('_caseTypeId');
  $currentContact = $form->getVar('_currentlyViewedContactId');
  if (!empty($currentContact)) {
    $activitySubject = civicrm_api3('Contact', 'Getvalue', array('id' => $currentContact, 'return' => 'display_name'));
  } else {
    $activitySubject = '{contactName}'; 

  }
  if (!empty($caseTypeId)) {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $activitySubject .= '-'.CRM_Utils_Array::value($caseTypeId, $threepeasConfig->caseTypes);
  } else {
    $activitySubject .= '-{caseType}';
  }
  if (!empty($caseId)) {
    $activitySubject .= '-'.$caseId;
  } else {
    $activitySubject .= '-{caseId}'; 
  }
  $defaults['activity_subject'] = $activitySubject;
  $form->setDefaults($defaults);
}
/**
 * Function to modify case subject if required
 */
function _threepeasReformCaseSubject($caseId, $subject, $caseTypeId, $contactName = '') {
  if (!empty($subject)) {
    if (!empty($contactName)) {
      $subject = str_replace('{contactName}', $contactName, $subject);      
    }
    $typeParts = explode(CRM_Core_DAO::VALUE_SEPARATOR, $caseTypeId);
    if (isset($typeParts[1])) {
      $caseTypeId = $typeParts[1];
    }
    $subject = str_replace('{caseId}', $caseId, $subject);
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    
    $caseType = $threepeasConfig->caseTypes[$caseTypeId];
    $subject = str_replace('{caseType}', $caseType, $subject);
    $query = 'UPDATE civicrm_case SET subject = %1 WHERE id = %2';
    $params = array(1 => array($subject, 'String'), 2 => array($caseId, 'Positive'));
    CRM_Core_DAO::executeQuery($query, $params);
  }
}
/**
 * Function to modify open case activity subject if required
 */
function _threepeasReformOpenCaseSubject($caseId, $caseTypeId, $activityId, $subject) {
  if (!empty($subject)) {
    $caseData = civicrm_api3('Case', 'Getsingle', array('id' => $caseId));
    foreach ($caseData['client_id'] as $caseClientId) {
      $contactParams = array('id' => $caseClientId, 'return' => 'display_name');
      $contactName = civicrm_api3('Contact', 'Getvalue', $contactParams);
    }
    $subject = str_replace('{contactName}', $contactName, $subject);
    $subject = str_replace('{caseId}', $caseId, $subject);
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $caseType = $threepeasConfig->caseTypes[$caseTypeId];
    $subject = str_replace('{caseType}', $caseType, $subject);
    $query = 'UPDATE civicrm_activity SET subject = %1 WHERE id = %2';
    $params = array(1 => array($subject, 'String'), 2 => array($activityId, 'Positive'));
    CRM_Core_DAO::executeQuery($query, $params);
    /*
     * modify case subject too if req'uired. This to ensure that adding a case without
     * a customer works too.
     */
    _threepeasReformCaseSubject($caseId, $caseData['subject'], $caseTypeId, $contactName);
  }
}