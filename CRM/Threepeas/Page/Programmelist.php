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
        $this->assign('pumProgrammeUrl', CRM_Utils_System::url('civicrm/pumprogramme', null, true));
        $this->assign('delProgrammeUrl', CRM_Utils_System::url('civicrm/actionprocess', null, true));
        $this->assign('divideProgrammeUrl', CRM_Utils_System::url('civicrm/pumprogrammedivision', null, true));
        $programmes = CRM_Threepeas_PumProgramme::getAllProgrammes();
        $displayProgrammes = array();
        foreach ($programmes as $programme) {
            $displayProgramme = array();
            $displayProgramme['id'] = $programme['id'];
            $displayProgramme['title'] = $programme['title'];
            $displayProgramme['budget'] = CRM_Utils_Money::format($programme['budget']);
            if (isset($programme['start_date'])) {
                $displayProgramme['start_date'] = date("d-m-Y", strtotime($programme['start_date']));
            } else {
                $displayProgramme['start_date'] = NULL;
            }
            if (isset($programme['end_date'])) {
                $displayProgramme['end_date'] = date("d-m-Y", strtotime($programme['end_date']));
            } else {
                $displayProgramma['end_date'] = NULL;
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
            $displayProgrammes[] = $displayProgramme;
        }
        $this->assign('pumProgrammes', $displayProgrammes);
        parent::run();
  }
}
