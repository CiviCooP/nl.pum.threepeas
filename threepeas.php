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
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
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
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function threepeas_civicrm_enable() {
  return _threepeas_civix_civicrm_enable();
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
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function threepeas_civicrm_managed(&$entities) {
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
 * to create a programs, projects and products menu and menu items
 * 
 * @author Erik Hommel (erik.hommel@civicoop.org http://www.civicoop.org)
 * @date 29 Jan 2013
 * @param array $params
 */
function threepeas_civicrm_navigationMenu( &$params ) {
    $maxKey = ( max( array_keys($params) ) );
    $params[$maxKey+1] = array (
        'attributes' => array (
            'label'      => 'Programs, Projects and Products',
            'name'       => 'Programs, Projects and Products',
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
                    'label'      => 'List Programs',
                    'name'       => 'List Programs',
                    'url'        => 'civicrm/programlist',
                    'operator'   => null,
                    'separator'  => 0,
                    'parentID'   => $maxKey+1,
                    'navID'      => 1,
                    'active'     => 1
                ),
                'child' => null
            ) 
        ), 
        'child' =>  array (
            '1' => array (
                'attributes' => array (
                    'label'      => 'List Programs',
                    'name'       => 'List Programs',
                    'url'        => 'civicrm/programlist',
                    'operator'   => null,
                    'separator'  => 0,
                    'parentID'   => $maxKey+1,
                    'navID'      => 2,
                    'active'     => 1
                ),
                'child' => null
            ) 
        ), 
        'child' =>  array (
            '1' => array (
                'attributes' => array (
                    'label'      => 'List Projects',
                    'name'       => 'List Projects',
                    'url'        => 'civicrm/projectlist',
                    'operator'   => null,
                    'separator'  => 1,
                    'parentID'   => $maxKey+1,
                    'navID'      => 3,
                    'active'     => 1
                ),
                'child' => null
            ) 
        ), 
        'child' =>  array (
            '1' => array (
                'attributes' => array (
                    'label'      => 'List Products',
                    'name'       => 'List Products',
                    'url'        => 'civicrm/productlist',
                    'operator'   => null,
                    'separator'  => 1,
                    'parentID'   => $maxKey+1,
                    'navID'      => 4,
                    'active'     => 1
                ),
                'child' => null
            ) 
        ), 
        'child' =>  array (
            '1' => array (
                'attributes' => array (
                    'label'      => 'Programs Report',
                    'name'       => 'Programs Report',
                    'url'        => '#',
                    'operator'   => null,
                    'separator'  => 1,
                    'parentID'   => $maxKey+1,
                    'navID'      => 5,
                    'active'     => 1
                ),
                'child' => null
            ) 
        ), 
        'child' =>  array (
            '1' => array (
                'attributes' => array (
                    'label'      => 'Projects Report',
                    'name'       => 'Projects Report',
                    'url'        => '#',
                    'operator'   => null,
                    'separator'  => 1,
                    'parentID'   => $maxKey+1,
                    'navID'      => 6,
                    'active'     => 1
                ),
                'child' => null
            ) 
        ), 
    );
}
