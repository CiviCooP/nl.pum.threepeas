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
    protected $_programId = 0;
    protected $_programManagerGroup = 0;
    
    function run() {
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);
        $this->_programId = CRM_Utils_Request::retrieve('pid', 'Positive', $this);
        $group = civicrm_api3('Group', 'Getsingle', array('title' => "Program Managers"));
        if (isset($group['id'])) {
            $this->_programManagerGroup = $group['id'];
        }
        /*
         * retrieve program data if not add
         */
        if ($this->_action != 1) {
            $pumProgram = CRM_Threepeas_PumProgram::getProgramById($this->_programId);
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
                $this->buildPageEdit($pumProgram);
                break;
            case 1:
                $this->buildPageAdd();
                break;
            case 4:
                $this->buildPageView($pumProgram);
                break;
        }
        parent::run();
    }
    /**
     * Function to build page for view action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @param type $pumProgram
     * @access private
     */
    private function buildPageView($pumProgram) {
        $doneUrl = CRM_Utils_System::url('civicrm/programlist', null, true);
        $this->assign('doneUrl', $doneUrl);
        
        $this->assign('action', 'view');
        
        if (isset($pumProgram['title'])) {
            $this->assign('programTitle', $pumProgram['title']);
        }
        if (isset($pumProgram['description'])) {
            $descriptionHtml='<textarea readonly="readonly" name="program_description" 
                rows="3" cols="80">'.$pumProgram['description'].'</textarea>';
            $this->assign('programDescription', $descriptionHtml);
        }
        if (isset($pumProgram['contact_id_manager'])) {
            $contactParams = array(
                'id'     =>  $pumProgram['contact_id_manager'],
                'return' =>  'display_name'
            );
            $managerName = civicrm_api3('Contact', 'Getvalue', $contactParams);
            $managerUrl = CRM_Utils_System::url('civicrm/contact/view', null, true).
                    "&rest=1&cid=".$pumProgram['contact_id_manager'];
            $managerHtml = '<a href="'.$managerUrl.'">'.$managerName.'</a>';
            $this->assign('programManager', $managerHtml);
            
        }
        if (isset($pumProgram['budget'])) {
            $this->assign('programBudget', CRM_Utils_Money::format($pumProgram['budget']));
        }
        if (isset($pumProgram['goals'])) {
            $goalsHtml='<textarea readonly="readonly" name="program_goals" 
                rows="3" cols="80">'.$pumProgram['goals'].'</textarea>';
            $this->assign('programGoals', $goalsHtml);
        }
        if (isset($pumProgram['requirements'])) {
            $requirementsHtml='<textarea readonly="readonly" name="program_requirements" 
                rows="3" cols="80">'.$pumProgram['requirements'].'</textarea>';
            $this->assign('programRequirements', $requirementsHtml);
        }
        if (isset($pumProgram['start_date'])) {
            $this->assign('programStartDate', date("d-m-Y", 
                strtotime($pumProgram['start_date'])));
        }
        if (isset($pumProgram['end_date'])) {
            $this->assign('programEndDate', date("d-m-Y", 
                strtotime($pumProgram['end_date'])));
        }
        if (isset($pumProgram['is_active'])) {
            if ($pumProgram['is_active'] == 1) {
                $activeHtml="<tt>[x]</tt>";
            } else {
                $activeHtml = "<tt>[ ]</tt>";
            }
        } else {
            $activeHtml = "<tt>[ ]</tt>";
        }
        $this->assign('programIsActive', $activeHtml);
    }
    /**
     * Function to build page for add action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @param type $pumProgram
     * @access private
     */
    private function buildPageAdd() {
        $this->assign('action', 'add');
        
        $submitUrl = CRM_Utils_System::url('civicrm/actionprocess', null, true);
        $this->assign('submitProgramUrl', $submitUrl);

        $cancelUrl = CRM_Utils_System::url('civicrm/programlist', null, true);
        $this->assign('cancelUrl', $cancelUrl);
        
        $titleHtml = '<input id="program-title" type="text" class="form-text" 
            name="programTitle" size="80" maxlength="80">';
        $this->assign('programTitle', $titleHtml);
        
        $descriptionHtml='<textarea name="programDescription" rows="3" cols="80">
            </textarea>';
        $this->assign('programDescription', $descriptionHtml);
        
        $programManagerHtml = '<select id="program-manager" name="programManager" 
            class="form-select"><option value="0">- none</option>';        
        $contacts = civicrm_api3('Contact', 'Get', array('group' => 
            $this->_programManagerGroup));
        foreach ($contacts['values'] as $contact_id => $contact) {
            $programManagerHtml .= '<option value="'.$contact_id.'">'.
                    $contact['display_name'].'</option>';
        }
        $programManagerHtml .= '</select>';
        $this->assign('programManager', $programManagerHtml);
        
        $budgetHtml = '<input id="program-budget" type="number" class="form=text" 
            name="programBudget">';
        $this->assign('programBudget', $budgetHtml);

        $goalsHtml='<textarea name="programGoals" rows="3" cols="80"></textarea>';
        $this->assign('programGoals', $goalsHtml);
        
        $requirementsHtml='<textarea name="programRequirements" rows="3" cols="80">
            </textarea>';
        $this->assign('programRequirements', $requirementsHtml);

        $enabledHtml = '<input id="is_active" class="form-checkbox" type="checkbox" 
            checked="checked" value="1" name="is_active">';
        $this->assign('programIsActive', $enabledHtml);
    }
    /**
     * Function to build page for edit action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @param type $pumProgram
     * @access private
     */
    private function buildPageEdit($pumProgram) {
        $this->assign('action', 'edit');
        
        $submitUrl = CRM_Utils_System::url('civicrm/actionprocess', null, true);
        $this->assign('submitProgramUrl', $submitUrl);

        $cancelUrl = CRM_Utils_System::url('civicrm/programlist', null, true);
        $this->assign('cancelUrl', $cancelUrl);
        
        $titleHtml = '<input id="program-title" type="text" class="form-text" 
            name="programTitle" value="'.$pumProgram['title'].'">';
        $this->assign('programTitle', $titleHtml);
        
        $descriptionHtml='<textarea name="programDescription" rows="3" cols="80">'
            .$pumProgram['description'].'</textarea>';
        $this->assign('programDescription', $descriptionHtml);
        
        $programManagerHtml = '<select id="program-manager" name="programManager" 
            class="form-select"><option value="0">- none</option>';        
        $contacts = civicrm_api3('Contact', 'Get', array('group' => $this->_programManagerGroup));
        foreach ($contacts['values'] as $contact_id => $contact) {
            if ($contact_id == $pumProgram['contact_id_manager']) {
                $programManagerHtml .= '<option selected="selected" value="'.
                    $contact_id.'">'.$contact['display_name'].'</option>';                
            } else {
                $programManagerHtml .= '<option value="'.$contact_id.'">'.
                    $contact['display_name'].'</option>';
            }
        }
        $programManagerHtml .= '</select>';
        $this->assign('programManager', $programManagerHtml);
        
        $budgetHtml = '<input id="program-budget" type="number" class="form=text" 
            name="programBudget" value="'.$pumProgram['budget'].'">';
        $this->assign('programBudget', $budgetHtml);

        $goalsHtml='<textarea name="programGoals" rows="3" cols="80">'
            .$pumProgram['goals'].'</textarea>';
        $this->assign('programGoals', $goalsHtml);
        
        $requirementsHtml='<textarea name="programRequirements" rows="3" cols="80">'
            .$pumProgram['requirements'].'</textarea>';
        $this->assign('programRequirements', $requirementsHtml);

        if ($pumProgram['is_active'] == 1) {
            $enabledHtml = '<input id="is_active" class="form-checkbox" type="checkbox" 
                checked="checked" value="1" name="is_active">';
        } else {
            $enabledHtml = '<input id="is_active" class="form-checkbox" type="checkbox" 
                value="0" name="is_active">';
        }
        $this->assign('programIsActive', $enabledHtml);
        
        $this->assign('displayStartDate', date("d-m-Y", strtotime($pumProgram['start_date'])));
        $this->assign('displayEndDate', date("d-m-Y", strtotime($pumProgram['end_date'])));

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
