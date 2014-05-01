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
    require_once 'CRM/Threepeas/PumProject.php';
    /*
     * retrieve option group for pum_project
     */
    try {
      $optionGroup = civicrm_api3('OptionGroup', 'Getsingle', array('name' => "pum_project"));
      $optionGroupId = $optionGroup['id'];
    } catch (CiviCRM_API3_Exception $e) {
      return _threepeas_civix_civicrm_enable();
    }
    if ($optionGroupId) {
      /*
       * remove all existing option values (directly in database because\
       * API would force me to do record by record
       */
      $delQuery = "DELETE FROM civicrm_option_value WHERE option_group_id = $optionGroupId";
      CRM_Core_DAO::executeQuery($delQuery);
      /*
       * retrieve all active projects and add option values
       */
      $noneParams = array(
        'option_group_id'   =>  $optionGroupId,
        'value'             =>  0,
        'label'             =>  '- none',
        'is_active'         =>  1,
        'is_reserved'       =>  1
      );
      civicrm_api3('OptionValue', 'Create', $noneParams);
      $pumActiveProjects = CRM_Threepeas_PumProject::getAllActiveProjects();
      foreach ($pumActiveProjects as $projectId => $activeProject) {
        $createParams = array(
          'option_group_id'   =>  $optionGroupId,
          'value'             =>  $projectId,
          'label'             =>  $activeProject['title'],
          'is_active'         =>  1,
          'is_reserved'       =>  1
        );
        civicrm_api3('OptionValue', 'Create', $createParams);
      }
    }
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
          'label'      => 'List Products',
          'name'       => 'List Products',
          'url'        => CRM_Utils_System::url('civicrm/case/search', 'reset=1', true),
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
          'label'      => 'Add Product',
          'name'       => 'Programmes Report',
          'url'        => CRM_Utils_System::url('civicrm/case/add', 'reset=1&action=add&atype=13&context=standalone'),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 5,
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
      $projectCount = CRM_Threepeas_PumProject::countCustomerProjects($contactID);
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
  $$threepeasConfig = CRM_Threepeas_Config::singleton();
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
