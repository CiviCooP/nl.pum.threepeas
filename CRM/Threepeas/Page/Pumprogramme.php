<?php
/**
 * Page Pumprogramme to add, edit, view or delete a programme (PUM)
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 10 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the Academic Free License version 3.0.
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Pumprogramme extends CRM_Core_Page {
    protected $_action = 0;
    protected $_programmeId = 0;
    protected $_programmeManagerGroup = 0;
    
    function run() {
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);
        $this->_programmeId = CRM_Utils_Request::retrieve('pid', 'Positive', $this);
        $group = civicrm_api3('Group', 'Getsingle', array('title' => "Programme Managers"));
        if (isset($group['id'])) {
            $this->_programmeManagerGroup = $group['id'];
        }
        /*
         * retrieve programme data if not add
         */
        if ($this->_action != 1) {
            $pumProgramme = CRM_Threepeas_PumProgramme::getProgrammeById($this->_programmeId);
        }
        /*
         * set labels for page
         */
        $this->setLabels();
        /*
         * prepare page based on action (edit=0, add=1, view=4, delete=8)
         */
        switch($this->_action) {
            case CRM_Core_Action::UPDATE:
                $this->buildPageEdit($pumProgramme);
                break;
            case CRM_Core_Action::ADD:
                $this->buildPageAdd();
                break;
            case CRM_Core_Action::VIEW:
                $this->buildPageView($pumProgramme);
                break;
        }
        parent::run();
    }
    /**
     * Function to build page for view action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @param array $pumProgramme
     * @access private
     */
    private function buildPageView($pumProgramme) {
        $doneUrl = CRM_Utils_System::url('civicrm/programmelist', null, true);
        $this->assign('doneUrl', $doneUrl);
        
        $this->assign('action', 'view');
        
        if (isset($pumProgramme['title'])) {
            $this->assign('programmeTitle', $pumProgramme['title']);
        }
        if (isset($pumProgramme['description'])) {
            $descriptionHtml='<textarea readonly="readonly" name="programme_description" 
                rows="3" cols="80">'.$pumProgramme['description'].'</textarea>';
            $this->assign('programmeDescription', $descriptionHtml);
        }
        if (isset($pumProgramme['contact_id_manager'])) {
            $contactParams = array(
                'id'     =>  $pumProgramme['contact_id_manager'],
                'return' =>  'display_name'
            );
            $managerName = civicrm_api3('Contact', 'Getvalue', $contactParams);
            $managerUrl = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$pumProgramme['contact_id_manager'], true);
            $managerHtml = '<a href="'.$managerUrl.'">'.$managerName.'</a>';
            $this->assign('programmeManager', $managerHtml);
            
        }
        if (isset($pumProgramme['budget'])) {
            $this->assign('programmeBudget', CRM_Utils_Money::format($pumProgramme['budget']));
        }
        if (isset($pumProgramme['goals'])) {
            $goalsHtml='<textarea readonly="readonly" name="programme_goals" 
                rows="3" cols="80">'.$pumProgramme['goals'].'</textarea>';
            $this->assign('programmeGoals', $goalsHtml);
        }
        if (isset($pumProgramme['requirements'])) {
            $requirementsHtml='<textarea readonly="readonly" name="programme_requirements" 
                rows="3" cols="80">'.$pumProgramme['requirements'].'</textarea>';
            $this->assign('programmeRequirements', $requirementsHtml);
        }
        if (isset($pumProgramme['start_date'])) {
            $this->assign('programmeStartDate', date("d-m-Y", 
                strtotime($pumProgramme['start_date'])));
        }
        if (isset($pumProgramme['end_date'])) {
            $this->assign('programmeEndDate', date("d-m-Y", 
                strtotime($pumProgramme['end_date'])));
        }
        if (isset($pumProgramme['is_active'])) {
            if ($pumProgramme['is_active'] == 1) {
                $activeHtml="<tt>[x]</tt>";
            } else {
                $activeHtml = "<tt>[ ]</tt>";
            }
        } else {
            $activeHtml = "<tt>[ ]</tt>";
        }
        $this->assign('programmeIsActive', $activeHtml);
        /*
         * retrieve list of programme divisions for programme
         */
        $divisionParams['programme_id'] = $pumProgramme['id'];
        $programmeDivisions = CRM_Threepeas_PumProgrammeDivision::getAllProgrammeDivisionsForProgramme($divisionParams);
        $displayDivisions = array();
        foreach($programmeDivisions as $programmeDivision) {
            $displayDivision = array();
            $countryParams = array(
                'id'        =>  $programmeDivision['country_id'],
                'return'    =>  "name"
            );
            $displayDivision['country'] = civicrm_api3('Country', 'Getvalue', $countryParams);
            if (isset($programmeDivision['min_projects']) && !empty($programmeDivision['min_projects'])) {
                $displayDivision['min_projects'] = $programmeDivision['min_projects'];
            }
            if (isset($programmeDivision['max_projects']) && !empty($programmeDivision['max_projects'])) {
                $displayDivision['max_projects'] = $programmeDivision['max_projects'];
            }
            if (isset($programmeDivision['min_budget']) && !empty($programmeDivision['min_budget'])) {
                $displayDivision['min_budget'] = CRM_Utils_Money::format($programmeDivision['min_budget']);
            }
            if (isset($programmeDivision['max_budget']) && !empty($programmeDivision['max_budget'])) {
                $displayDivision['max_budget'] = CRM_Utils_Money::format($programmeDivision['max_budget']);
            }
            $displayDivisions[] = $displayDivision;
            
        }
        $this->assign('pumProgrammeDivisions', $displayDivisions);
    }
    /**
     * Function to build page for add action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @access private
     */
    private function buildPageAdd() {
        $this->assign('action', 'add');
        
        $submitUrl = CRM_Utils_System::url('civicrm/actionprocess', null, true);
        $this->assign('submitProgrammeUrl', $submitUrl);

        $cancelUrl = CRM_Utils_System::url('civicrm/programmelist', null, true);
        $this->assign('cancelUrl', $cancelUrl);
        
        $titleHtml = '<input id="programme-title" type="text" class="form-text" 
            name="programmeTitle" size="80" maxlength="80">';
        $this->assign('programmeTitle', $titleHtml);
        
        $descriptionHtml='<textarea name="programmeDescription" rows="3" cols="80"></textarea>';
        $this->assign('programmeDescription', $descriptionHtml);
        
        $programmeManagerHtml = '<select id="programme-manager" name="programmeManager" 
            class="form-select"><option value="0">- none</option>';        
        $contacts = civicrm_api3('Contact', 'Get', array('group' => 
            $this->_programmeManagerGroup));
        foreach ($contacts['values'] as $contact_id => $contact) {
            $programmeManagerHtml .= '<option value="'.$contact_id.'">'.
                    $contact['display_name'].'</option>';
        }
        $programmeManagerHtml .= '</select>';
        $this->assign('programmeManager', $programmeManagerHtml);
        
        $budgetHtml = '<input id="programme-budget" type="number" class="form=text" 
            name="programmeBudget">';
        $this->assign('programmeBudget', $budgetHtml);

        $goalsHtml='<textarea name="programmeGoals" rows="3" cols="80"></textarea>';
        $this->assign('programmeGoals', $goalsHtml);
        
        $requirementsHtml='<textarea name="programmeRequirements" rows="3" cols="80"></textarea>';
        $this->assign('programmeRequirements', $requirementsHtml);

        $enabledHtml = '<input id="programmeIsActive" class="form-checkbox" type="checkbox" 
            checked="checked" value="1" name="programmeIsActive">';
        $this->assign('programmeIsActive', $enabledHtml);
    }
    /**
     * Function to build page for edit action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @param array $pumProgramme
     * @access private
     */
    private function buildPageEdit($pumProgramme) {
        $this->assign('action', 'edit');
        $this->assign('programmeId', $this->_programmeId);
        
        $submitUrl = CRM_Utils_System::url('civicrm/actionprocess', null, true);
        $this->assign('submitProgrammeUrl', $submitUrl);

        $cancelUrl = CRM_Utils_System::url('civicrm/programmelist', null, true);
        $this->assign('cancelUrl', $cancelUrl);
        
        $titleHtml = '<input id="programme-title" type="text" class="form-text" 
            name="programmeTitle" value="'.$pumProgramme['title'].'">';
        $this->assign('programmeTitle', $titleHtml);
        
        $descriptionHtml='<textarea name="programmeDescription" rows="3" cols="80">'.$pumProgramme['description'].'</textarea>';
        $this->assign('programmeDescription', $descriptionHtml);
        
        $programmeManagerHtml = '<select id="programme-manager" name="programmeManager" 
            class="form-select"><option value="0">- none</option>';        
        $contacts = civicrm_api3('Contact', 'Get', array('group' => $this->_programmeManagerGroup));
        foreach ($contacts['values'] as $contact_id => $contact) {
            if ($contact_id == $pumProgramme['contact_id_manager']) {
                $programmeManagerHtml .= '<option selected="selected" value="'.
                    $contact_id.'">'.$contact['display_name'].'</option>';                
            } else {
                $programmeManagerHtml .= '<option value="'.$contact_id.'">'.
                    $contact['display_name'].'</option>';
            }
        }
        $programmeManagerHtml .= '</select>';
        $this->assign('programmeManager', $programmeManagerHtml);
        
        $budgetHtml = '<input id="programme-budget" type="number" class="form=text" 
            name="programmeBudget" value="'.$pumProgramme['budget'].'">';
        $this->assign('programmeBudget', $budgetHtml);

        $goalsHtml='<textarea name="programmeGoals" rows="3" cols="80">'.$pumProgramme['goals'].'</textarea>';
        $this->assign('programmeGoals', $goalsHtml);
        
        $requirementsHtml='<textarea name="programmeRequirements" rows="3" cols="80">'.$pumProgramme['requirements'].'</textarea>';
        $this->assign('programmeRequirements', $requirementsHtml);

        if (!isset($pumProgramme['is_active']) || $pumProgramme['is_active'] == 0) {
            $enabledHtml = '<input id="programme-is_active" class="form-checkbox" type="checkbox" 
                name="programmeIsActive">';
        } else {
            $enabledHtml = '<input id="programme-is-active" class="form-checkbox" type="checkbox" 
                checked="checked" value="1" name="programmeIsActive">';            
        }
        $this->assign('programmeIsActive', $enabledHtml);
        
        if (isset($pumProgramme['start_date']) && !empty($pumProgramme['start_date'])) {
            $this->assign('displayStartDate', date("d-m-Y", strtotime($pumProgramme['start_date'])));
        }
        
        if (isset($pumProgramme['end_date']) && !empty($pumProgramme['end_date'])) {
            $this->assign('displayEndDate', date("d-m-Y", strtotime($pumProgramme['end_date'])));
        }

    }
    /**
     * Function to set labels for page fields
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 10 Feb 2014
     * @access private
     */
    private function setLabels() {
        $labels['programmeTitle'] = '<label for="Title">'.ts('Title').'<span class="crm-marker" title="This field is required.">*</span></label>';
        $labels['programmeDescription'] = '<label for="Description">'.ts('Description').'</label>';
        $labels['programmeManager'] = '<label for="Manager">'.ts('Manager').'</label>';
        $labels['budget'] = '<label for="Budget">'.ts('Budget').'<label>';
        $labels['goals'] = '<label for="Goals">'.ts('Goals').'</label>';
        $labels['requirements'] = '<label for="Requirements">'.ts('Requirements').'</label>';
        $labels['startDate'] = '<label for="Start Date">'.ts('Start Date').'</label>';
        $labels['endDate'] = '<label for="End Date">'.ts('End Date').'</label>';
        $labels['isActive'] = '<label for="Is Active">'.ts('Enabled').'</label>';
        $this->assign('labels', $labels);        
    }
}
