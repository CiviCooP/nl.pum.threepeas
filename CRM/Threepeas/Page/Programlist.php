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
        $programs = CRM_Threepeas_PumProgram::getAllPrograms();
        $display_programs = array();
        foreach ($programs as $program) {
            $display_program = array();
            $display_program['id'] = $program['id'];
            $display_program['title'] = $program['title'];
            $display_program['budget'] = CRM_Utils_Money::format($program['budget']);
            $display_program['start_date'] = date("d-m-Y", strtotime($program['start_date']));
            $display_program['end_date'] = date("d-m-Y", strtotime($program['end_date']));
            if ($program['is_active'] == 1) {
                $display_program['is_active'] = ts("Yes");
            } else {
                $display_program['is_active'] = ts("No");
            }
            $display_program['contact_id_manager'] = $program['contact_id_manager'];
            $contact_params = array(
                'id'     =>  $program['contact_id_manager'],
                'return' =>  'display_name'
            );
            $display_program['manager_name'] = civicrm_api3('Contact', 'Getvalue', $contact_params);
            $display_programs[] = $display_program;
        }
        $this->assign('pumPrograms', $display_programs);
        parent::run();
  }
}
