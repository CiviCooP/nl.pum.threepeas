<?php
/**
 * Page Programlist to list all programs (PUM)
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 17 Jan 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the Academic Free License version 3.0.
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Programlist extends CRM_Core_Page {
    function run() {
        $this->assign('pumProgramUrl', CRM_Utils_System::url('civicrm/pumprogram', null, true));
        $this->assign('delProgramUrl', CRM_Utils_System::url('civicrm/pumaction', null, true));
        $this->assign('divideProgramUrl', CRM_Utils_System::url('civicrm/pumprogramdivision', null, true));
        $programs = CRM_Threepeas_PumProgram::getAllPrograms();
        $displayPrograms = array();
        foreach ($programs as $program) {
            $displayProgram = array();
            $displayProgram['id'] = $program['id'];
            $displayProgram['title'] = $program['title'];
            $displayProgram['budget'] = CRM_Utils_Money::format($program['budget']);
            $displayProgram['start_date'] = date("d-m-Y", strtotime($program['start_date']));
            $displayProgram['end_date'] = date("d-m-Y", strtotime($program['end_date']));
            if ($program['is_active'] == 1) {
                $displayProgram['is_active'] = ts("Yes");
            } else {
                $displayProgram['is_active'] = ts("No");
            }
            $displayProgram['contact_id_manager'] = $program['contact_id_manager'];
            $contactParams = array(
                'id'     =>  $program['contact_id_manager'],
                'return' =>  'display_name'
            );
            $displayProgram['manager_name'] = civicrm_api3('Contact', 'Getvalue', $contactParams);
            $displayPrograms[] = $displayProgram;
        }
        $this->assign('pumPrograms', $displayPrograms);
        parent::run();
  }
}
