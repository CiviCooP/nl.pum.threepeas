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
    _threepeas_generate_project_list();
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
 * Implementation of hook_civicrm_managed
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 10 Feb 2014
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function threepeas_civicrm_managed(&$entities) {
  /*
   * create specific groups for PUM
   */
  $entities[] = array(
    'module'    => 'nl.pum.threepeas',
    'name'      => 'Programme Managers',
    'entity'    => 'Group',
    'params'    => array(
      'version'       => 3,
      'name'          => 'Programme Managers',
      'title'         => 'Programme Managers',
      'description'   => 'Group for Possible Programme Managers',
      'is_active'     =>  1,
      'is_reserved'   =>  1,
      'group_type'    =>  array(2 => 1))
  );
  $entities[] = array(
    'module'    => 'nl.pum.threepeas',
    'name'      => 'Sector Coordinators',
    'entity'    => 'Group',
    'params'    => array(
      'version'       => 3,
      'name'          => 'Sector Coordinators',
      'title'         => 'Sector Coordinators',
      'description'   => 'Group for Possible Sector Coordinators',
      'is_active'     =>  1,
      'is_reserved'   =>  1,
      'group_type'    =>  array(2 => 1))
  );
  $entities[] = array(
    'module'    => 'nl.pum.threepeas',
    'name'      => 'Country Coordinators',
    'entity'    => 'Group',
    'params'    => array(
      'version'       => 3,
      'name'          => 'Country Coordinators',
      'title'         => 'Country Coordinators',
      'description'   => 'Group for Possible Country Coordinators',
      'is_active'     =>  1,
      'is_reserved'   =>  1,
      'group_type'    =>  array(2 => 1))
  );
  $entities[] = array(
    'module'    => 'nl.pum.threepeas',
    'name'      => 'Project Officers',
    'entity'    => 'Group',
    'params'    => array(
      'version'       => 3,
      'name'          => 'Project Officers',
      'title'         => 'Project Officers',
      'description'   => 'Group for Possible Project Officers',
      'is_active'     =>  1,
      'is_reserved'   =>  1,
      'group_type'    =>  array(2 => 1))
  );
  return _threepeas_civix_civicrm_managed($entities);
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
 * @author Erik Hommel (erik.hommel@civicoop.org http://www.civicoop.org)
 * @date 29 Jan 2013
 * @param array $params
 */
function threepeas_civicrm_navigationMenu( &$params ) {
  $maxKey = ( max( array_keys($params) ) );
  $params[$maxKey+1] = array (
    'attributes' => array (
      'label'      => 'Programmes, Projects and Products',
      'name'       => 'Programmes, Projects and Products',
      'url'        => null,
      'permission' => null,
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
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 26 Mar 2014
 */
function threepeas_civicrm_tabs(&$tabs, $contactID) {
  /*
   * first check if contact_subtype is customer
   */
  $contact = civicrm_api3('Contact', 'Getsingle', array('id' => $contactID));
  $customerType = FALSE;
  if (!empty($contact['contact_sub_type'])) {
    foreach ($contact['contact_sub_type'] as $contactSubType) {
      if ($contactSubType == "Customer") {
        $customerType = TRUE;
      }
    }
    foreach ($tabs as $tab) {
      if ($tab['title'] == ts("Cases")) {
        $projectWeight = $tab['weight']++;
      }
    }
    if ($customerType == TRUE) {
      $projectCount = CRM_Threepeas_BAO_PumProject::countCustomerProjects($contactID);
      $projectUrl = CRM_Utils_System::url('civicrm/projectlist','snippet=1&cid='.$contactID);
      $tabs[] = array( 
        'id'    => 'customerProjects',
        'url'       => $projectUrl,
        'title'     => 'Projects',
        'weight'    => $projectWeight,
        'count'     => $projectCount);
    }
  }
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
  if ($groupID == $threepeasConfig->projectCustomGroupId && $op == 'create') {
    $pumProject = _threepeas_set_project($params);
    /*
     * retrieve case for subject and client
     */ 
    $apiCase = civicrm_api3('Case', 'Getsingle', array('case_id' => $entityID));
    $pumProject['title'] = $apiCase['subject'];
    $pumProject['customer_id'] = $apiCase['client_id'][1];
    $pumProject['is_active'] = 1;
    CRM_Threepeas_BAO_PumProject::add($pumProject);
  }
}
/**
 * Function to set basic data for pum project
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 23 Apr 2014
 * @param array $params
 * @return array $result
 */
function _threepeas_set_project($params) {
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
  if ($formName == 'CRM_Case_Form_Case') {
    
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $projectList = array();
    $projects = CRM_Threepeas_BAO_PumProject::getValues(array('is_active' => 1));
    foreach ($projects as $projectId => $project) {
      $projectList[$projectId] = $project['title'];
    }
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
  }
  if ($formName == 'CRM_Case_Form_CaseView') {
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
      $form->assign('project_title', $projects[$projectId]['title']);
    }
  }
}
/**
 * Implementation of hook civicrm_postProcess
 * 
 * add data to civicrm_case_project
 */
function threepeas_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Case_Form_Case') {
    $action = $form->getVar('_action');
    if ($action === CRM_Core_Action::ADD) {
      $values = $form->exportValues();
      if (isset($values['project_id']) && !empty($values['project_id'])) {
        /* 
         * retrieve latest case_id
         */
        $daoCase = CRM_Core_DAO::executeQuery('SELECT MAX(id) as maxId FROM civicrm_case');
        if ($daoCase->fetch()) {
          $params = array(
            'case_id' => $daoCase->maxId,
            'project_id' => $values['project_id']);
          CRM_Threepeas_BAO_PumCaseProject::add($params);
        }
      }
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
function _threepeas_generate_project_list() {
  $optionGroupId = _threepeas_create_option_group('pum_project');
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
function _threepeas_create_option_group($name) {
 $countGroup = civicrm_api3('OptionGroup', 'Getcount', array('name' => $name));
 switch ($countGroup) {
   case 0:
     $params = array('name' => $name, 'title' => 'active projects', 'is_active' => 1, 'is_reserved' => 1);
     $optionGroup = civicrm_api3('OptionGroup', 'Create', $params);
     $optionGroupId = $optionGroup['id'];
     break;
   case 1:
     $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', array('name' => $name, 'return' => id));
     break;
   default:
     throw new Exception('Could not create option group pum_project, there are already '
       .$countGroup.' with that name. Correct and try again.');
     break;
 }
 return $optionGroupId;
}
