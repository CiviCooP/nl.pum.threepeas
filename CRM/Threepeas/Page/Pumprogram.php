<?php
/**
 * Page Pumprogram to add, edit, view or delete a program (PUM)
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 10 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the Academic Free License version 3.0.
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Pumprogram extends CRM_Core_Page {
    protected $_action = "";
    protected $_program_id = 0;
    protected $_program_manager_group = 0;
    
    function run() {
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);
        $this->_program_id = CRM_Utils_Request::retrieve('pid', 'Positive', $this);
        $group = civicrm_api3('Group', 'Getsingle', array('title' => "Program Managers"));
        if (isset($group['id'])) {
            $this->_program_manager_group = $group['id'];
        }
        /*
         * retrieve program data if not add
         */
        if ($this->_action != 1) {
            $pum_program = CRM_Threepeas_PumProgram::getProgramById($this->_program_id);
        }
        /*
         * set labels for page
         */
        $this->setLabels();
        /*
         * prepare page based on action (edit=0, add=1, view=4, delete=8)
         */
        switch($this->_action) {
            case 0:
                $this->buildPageEdit($pum_program);
                break;
            case 1:
                $this->buildPageAdd();
                break;
            case 4:
                $this->buildPageView($pum_program);
                break;
        }
        parent::run();
    }
    /**
     * Function to build page for view action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @param type $pum_program
     * @access private
     */
    private function buildPageView($pum_program) {
        $done_url = CRM_Utils_System::url('civicrm/programlist', null, true);
        $this->assign('doneUrl', $done_url);
        
        $this->assign('action', 'view');
        
        if (isset($pum_program['title'])) {
            $this->assign('programTitle', $pum_program['title']);
        }
        if (isset($pum_program['description'])) {
            $description_html='<textarea readonly="readonly" name="program_description" 
                rows="3" cols="80">'.$pum_program['description'].'</textarea>';
            $this->assign('programDescription', $description_html);
        }
        if (isset($pum_program['contact_id_manager'])) {
            $contact_params = array(
                'id'     =>  $pum_program['contact_id_manager'],
                'return' =>  'display_name'
            );
            $manager_name = civicrm_api3('Contact', 'Getvalue', $contact_params);
            $manager_url = CRM_Utils_System::url('civicrm/contact/view', null, true).
                    "&rest=1&cid=".$pum_program['contact_id_manager'];
            $manager_html = '<a href="'.$manager_url.'">'.$manager_name.'</a>';
            $this->assign('programManager', $manager_html);
            
        }
        if (isset($pum_program['budget'])) {
            $this->assign('programBudget', CRM_Utils_Money::format($pum_program['budget']));
        }
        if (isset($pum_program['goals'])) {
            $goals_html='<textarea readonly="readonly" name="program_goals" 
                rows="3" cols="80">'.$pum_program['goals'].'</textarea>';
            $this->assign('programGoals', $goals_html);
        }
        if (isset($pum_program['requirements'])) {
            $requirements_html='<textarea readonly="readonly" name="program_requirements" 
                rows="3" cols="80">'.$pum_program['requirements'].'</textarea>';
            $this->assign('programRequirements', $requirements_html);
        }
        if (isset($pum_program['start_date'])) {
            $this->assign('programStartDate', date("d-m-Y", 
                strtotime($pum_program['start_date'])));
        }
        if (isset($pum_program['end_date'])) {
            $this->assign('programEndDate', date("d-m-Y", 
                strtotime($pum_program['end_date'])));
        }
        if (isset($pum_program['is_active'])) {
            if ($pum_program['is_active'] == 1) {
                $active_html="<tt>[x]</tt>";
            } else {
                $active_html = "<tt>[ ]</tt>";
            }
        } else {
            $active_html = "<tt>[ ]</tt>";
        }
        $this->assign('programIsActive', $active_html);
    }
    /**
     * Function to build page for add action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @param type $pum_program
     * @access private
     */
    private function buildPageAdd() {
        $this->assign('action', 'add');
        
        $submit_url = CRM_Utils_System::url('civicrm/actionprocess', null, true);
        $this->assign('submitProgramUrl', $submit_url);

        $cancel_url = CRM_Utils_System::url('civicrm/programlist', null, true);
        $this->assign('cancelUrl', $cancel_url);
        
        $title_html = '<input id="program-title" type="text" class="form-text" 
            name="programTitle" size="80" maxlength="80">';
        $this->assign('programTitle', $title_html);
        
        $description_html='<textarea name="programDescription" rows="3" cols="80">
            </textarea>';
        $this->assign('programDescription', $description_html);
        
        $program_manager_html = '<select id="program-manager" name="programManager" 
            class="form-select"><option value="0">- none</option>';        
        $contacts = civicrm_api3('Contact', 'Get', array('group' => 
            $this->_program_manager_group));
        foreach ($contacts['values'] as $contact_id => $contact) {
            $program_manager_html .= '<option value="'.$contact_id.'">'.
                    $contact['display_name'].'</option>';
        }
        $program_manager_html .= '</select>';
        $this->assign('programManager', $program_manager_html);
        
        $budget_html = '<input id="program-budget" type="number" class="form=text" 
            name="programBudget">';
        $this->assign('programBudget', $budget_html);

        $goals_html='<textarea name="programGoals" rows="3" cols="80"></textarea>';
        $this->assign('programGoals', $goals_html);
        
        $requirements_html='<textarea name="programRequirements" rows="3" cols="80">
            </textarea>';
        $this->assign('programRequirements', $requirements_html);

        $enabled_html = '<input id="is_active" class="form-checkbox" type="checkbox" 
            checked="checked" value="1" name="is_active">';
        $this->assign('programIsActive', $enabled_html);
    }
    /**
     * Function to build page for edit action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @param type $pum_program
     * @access private
     */
    private function buildPageEdit($pum_program) {
        $this->assign('action', 'edit');
        
        $submit_url = CRM_Utils_System::url('civicrm/actionprocess', null, true);
        $this->assign('submitProgramUrl', $submit_url);

        $cancel_url = CRM_Utils_System::url('civicrm/programlist', null, true);
        $this->assign('cancelUrl', $cancel_url);
        
        $title_html = '<input id="program-title" type="text" class="form-text" 
            name="programTitle" value="'.$pum_program['title'].'">';
        $this->assign('programTitle', $title_html);
        
        $description_html='<textarea name="programDescription" rows="3" cols="80">'
            .$pum_program['description'].'</textarea>';
        $this->assign('programDescription', $description_html);
        
        $program_manager_html = '<select id="program-manager" name="programManager" 
            class="form-select"><option value="0">- none</option>';        
        $contacts = civicrm_api3('Contact', 'Get', array('group' => $this->_program_manager_group));
        foreach ($contacts['values'] as $contact_id => $contact) {
            if ($contact_id == $pum_program['contact_id_manager']) {
                $program_manager_html .= '<option selected="selected" value="'.
                    $contact_id.'">'.$contact['display_name'].'</option>';                
            } else {
                $program_manager_html .= '<option value="'.$contact_id.'">'.
                    $contact['display_name'].'</option>';
            }
        }
        $program_manager_html .= '</select>';
        $this->assign('programManager', $program_manager_html);
        
        $budget_html = '<input id="program-budget" type="number" class="form=text" 
            name="programBudget" value="'.$pum_program['budget'].'">';
        $this->assign('programBudget', $budget_html);

        $goals_html='<textarea name="programGoals" rows="3" cols="80">'
            .$pum_program['goals'].'</textarea>';
        $this->assign('programGoals', $goals_html);
        
        $requirements_html='<textarea name="programRequirements" rows="3" cols="80">'
            .$pum_program['requirements'].'</textarea>';
        $this->assign('programRequirements', $requirements_html);

        if ($pum_program['is_active'] == 1) {
            $enabled_html = '<input id="is_active" class="form-checkbox" type="checkbox" 
                checked="checked" value="1" name="is_active">';
        } else {
            $enabled_html = '<input id="is_active" class="form-checkbox" type="checkbox" 
                value="0" name="is_active">';
        }
        $this->assign('programIsActive', $enabled_html);
        
        $this->assign('displayStartDate', date("d-m-Y", strtotime($pum_program['start_date'])));
        $this->assign('displayEndDate', date("d-m-Y", strtotime($pum_program['end_date'])));

    }
    /**
     * Function to set labels for page fields
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @access private
     */
    private function setLabels() {
        $labels['program_title'] = '<label for="Title">'.ts('Title').'<span class="crm-marker" title="This field is required.">*</span></label>';
        $labels['program_desc'] = '<label for="Description">'.ts('Description').'</label>';
        $labels['program_manager'] = '<label for="Manager">'.ts('Manager').'</label>';
        $labels['budget'] = '<label for="Budget">'.ts('Budget').'<label>';
        $labels['goals'] = '<label for="Goals">'.ts('Goals').'</label>';
        $labels['requirements'] = '<label for="Requirements">'.ts('Requirements').'</label>';
        $labels['start_date'] = '<label for="Start Date">'.ts('Start Date').'</label>';
        $labels['end_date'] = '<label for="End Date">'.ts('End Date').'</label>';
        $labels['is_active'] = '<label for="Is Active">'.ts('Enabled').'</label>';
        $this->assign('labels', $labels);        
    }
}
