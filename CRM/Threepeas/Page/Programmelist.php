<?php
/**
 * Page Programmelist to list all programmes (PUM)
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 17 Jan 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the Academic Free License version 3.0.
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Programmelist extends CRM_Core_Page {
    function run() {
        CRM_Utils_System::setTitle(ts('List of Programmes'));
        $programmes = CRM_Threepeas_PumProgramme::getAllProgrammes();
        $displayProgrammes = array();
        
        foreach ($programmes as $programme) {
            $displayProgramme = array();
            $displayProgramme['id'] = $programme['id'];
            $displayProgramme['title'] = $programme['title'];
            if (isset($programme['budget'])) {
                $displayProgramme['budget'] = CRM_Utils_Money::format($programme['budget']);
            }
            if (isset($programme['start_date'])) {
                $displayProgramme['start_date'] = date("d-m-Y", strtotime($programme['start_date']));
            } else {
                $displayProgramme['start_date'] = NULL;
            }
            if (isset($programme['end_date'])) {
                $displayProgramme['end_date'] = date("d-m-Y", strtotime($programme['end_date']));
            } else {
                $displayProgramme['end_date'] = NULL;
            }
            if ($programme['is_active'] == 1) {
                $displayProgramme['is_active'] = ts("Yes");
            } else {
                $displayProgramme['is_active'] = ts("No");
            }
            if (isset($programme['contact_id_manager']) && !empty($programme['contact_id_manager'])) {
                $displayProgramme['contact_id_manager'] = $programme['contact_id_manager'];
                $contactParams = array(
                    'id'     =>  $programme['contact_id_manager'],
                    'return' =>  'display_name'
                );
                $displayProgramme['manager_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
            }
            /*
             * set page actions, first generic part (view, edit, divide budget)
             * then in foreach the specifics (enable or disable and delete only if allowed)
             */
            $editUrl = CRM_Utils_System::url('civicrm/pumprogramme', "action=edit&pid={$programme['id']}", true);
            $viewUrl = CRM_Utils_System::url('civicrm/pumprogramme', "action=view&pid={$programme['id']}", true);
            $delUrl = CRM_Utils_System::url('civicrm/actionprocess', "pumAction=detele&pumEntity=programme&programmeId={$programme['id']}", true);
            $drillUrl = CRM_Utils_System::url('civicrm/pumdrill', 'pumEntity=programme&pid='.$programme['id'], true);
            $disableUrl = CRM_Utils_System::url('civicrm/actionprocess', "&pumAction=disable&pumEntity=programme&programmeId={$programme['id']}", true);
            $enableUrl = CRM_Utils_System::url('civicrm/actionprocess', "&pumAction=enable&pumEntity=programme&programmeId={$programme['id']}", true);
            $divideUrl = CRM_Utils_System::url('civicrm/pumprogrammedivision', "pid={$programme['id']}", true);
            $pageActions = array();
            $pageActions[] = '<a class="action-item" title="View programme details" href="'.$viewUrl.'">View</a>';
            $pageActions[] = '<a class="action-item" title="Edit programme" href="'.$editUrl.'">Edit</a>';
            $pageActions[] = '<a class="action-item" title="Drill down programme" href="'.$drillUrl.'">Drill Down</a>';
            $pageActions[] = '<a class="action-item" title="Divide budget" href="'.$divideUrl.'">Divide budget</a>';
            if ($programme['is_active'] == 1) {
                $pageActions[] = '<a class="action-item" title="Disable programme" href="'.$disableUrl.'">Disable</a>';
            } else {
                $pageActions[] = '<a class="action-item" title="Enable programme" href="'.$enableUrl.'">Enable</a>';                
            }
            if (CRM_Threepeas_PumProgramme::checkProgrammeDeleteable($programme['id'])) {
                $pageActions[] = '<a class="action-item" title="Delete programme" href="'.$delUrl.'">Delete</a>';
            }
            $displayProgramme['actions'] = $pageActions;
            $displayProgrammes[] = $displayProgramme;
        }
        $this->assign('pumProgrammes', $displayProgrammes);
        $addUrl = CRM_Utils_System::url('civicrm/pumprogramme', "action=add", true);
        $this->assign('addUrl', $addUrl);
        parent::run();
  }
}
