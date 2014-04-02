<?php
/**
 * Page Pumproject to add, update, view or delete a project (PUM)
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 17 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the Academic Free License version 3.0.
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Pumproject extends CRM_Core_Page {
    protected $_action = "";
    protected $_projectId = 0;
    protected $_sectorCoordinatorGroup = 0;
    protected $_countryCoordinatorGroup = 0;
    protected $_projectOfficerGroup = 0;
    protected $_customerContactType = "";
    
    function run() {
        CRM_Utils_System::setTitle(ts('Project Proposal'));
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);
        $this->_projectId = CRM_Utils_Request::retrieve('pid', 'Positive', $this);
        
        $sectorCoordinatorParams = array(
            'title' => "Sector Coordinators",
            'return'=> "id"
        );
        try {
            $this->_sectorCoordinatorGroup = civicrm_api3('Group', 'Getvalue', $sectorCoordinatorParams);
        } catch (CiviCRM_API3_Exception $e) {
            throw new Exception('Could not find a group for Sector Coordinators, message from API Group Getvalue : '.$e->getMessage());
        }
        
        $countryCoordinatorParams = array(
            'title' => "Country Coordinators",
            'return'=> "id"
        );
        try {
            $this->_countryCoordinatorGroup = civicrm_api3('Group', 'Getvalue', $countryCoordinatorParams);
        } catch (CiviCRM_API3_Exception $e) {
            throw new Exception('Could not find a group for Country Coordinators, message from API Group Getvalue : '.$e->getMessage());
        }
        
        $projectOfficerParams = array(
            'title' => "Project Officers",
            'return'=> "id"
        );
        try {
            $this->_projectOfficerGroup = civicrm_api3('Group', 'Getvalue', $projectOfficerParams);
        } catch (CiviCRM_API3_Exception $e) {
            throw new Exception('Could not find a group for Project Officers, message from API Group Getvalue : '.$e->getMessage());
        }
        
        $this->_customerContactType = "Customer";
        $customerContactTypeParams = array(
            'name'  =>  $this->_customerContactType,
            'return'=>  "id"
        );
        try {
            civicrm_api3('ContactType', 'Getvalue', $customerContactTypeParams);
        } catch (CiviCRM_API3_Exception $e) {
            $this->_customerContactType = "";
            throw new Exception('Could not find a contact subtype for Customer, message from API ContactType Getvalue : '.$e->getMessage());
        }
        
        /*
         * retrieve project data if not add
         */
        if ($this->_action != CRM_Core_Action::ADD) {
            $pumProject = CRM_Threepeas_PumProject::getProjectById($this->_projectId);
        }
        /*
         * set labels for page
         */
        $this->setLabels();
        /*
         * prepare page based on action
         */
        switch($this->_action) {
            case CRM_Core_Action::UPDATE:
                $this->buildPageEdit($pumProject);
                break;
            case CRM_Core_Action::ADD:
                $this->buildPageAdd();
                break;
            case CRM_Core_Action::VIEW:
                $this->buildPageView($pumProject);
                break;
        }
        parent::run();
    }
    /**
     * Function to build page for view action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     * @param type $pumProject
     * @access private
     */
    private function buildPageView($pumProject) {
        $doneUrl = CRM_Utils_System::url('civicrm/projectlist', null, true);
        $this->assign('doneUrl', $doneUrl);
        
        $this->assign('action', 'view');
        
        if (isset($pumProject['title'])) {
            $this->assign('projectTitle', $pumProject['title']);
        }
        if (isset($pumProject['programme_id']) && !empty($pumProject['programme_id'])) {
            $programmeTitle = CRM_Threepeas_PumProgramme::getProgrammeTitleWithId($pumProject['programme_id']);
            $programmeUrl = CRM_Utils_System::url("civicrm/pumprogramme", 'action=view&pid='.$pumProject['programme_id'], true);
            $programmeHtml = '<a href="'.$programmeUrl.'">'.$programmeTitle.'</a>';
            $this->assign('projectProgramme', $programmeHtml);
        }
        if (isset($pumProject['customer_id']) && !empty($pumProject['customer_id'])) {
            $customerParams = array(
                'id'     =>  $pumProject['customer_id'],
                'return' =>  'display_name'
            );
            $customerName = civicrm_api3('Contact', 'Getvalue', $customerParams);
            $customerUrl = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$pumProject['customer_id'], true);
            $customerHtml = '<a href="'.$customerUrl.'">'.$customerName.'</a>';
            $this->assign('projectCustomer', $customerHtml);
        }
        if (isset($pumProject['reason'])) {
            $reasonHtml='<textarea readonly="readonly" name="project_reason" 
                rows="3" cols="80">'.$pumProject['reason'].'</textarea>';
            $this->assign('projectReason', $reasonHtml);
        }
        if (isset($pumProject['work_description'])) {
            $workDescriptionHtml='<textarea readonly="readonly" name="project_work_description" 
                rows="3" cols="80">'.$pumProject['work_description'].'</textarea>';
            $this->assign('projectWorkDescription', $workDescriptionHtml);
        }
        if (isset($pumProject['qualifications'])) {
            $qualificationsHtml='<textarea readonly="readonly" name="project_qualifications" 
                rows="3" cols="80">'.$pumProject['qualifications'].'</textarea>';
            $this->assign('projectQualifications', $qualificationsHtml);
        }
        if (isset($pumProject['expected_results'])) {
            $expectedResultsHtml='<textarea readonly="readonly" name="project_expected_results" 
                rows="3" cols="80">'.$pumProject['expected_results'].'</textarea>';
            $this->assign('projectExpectedResults', $expectedResultsHtml);
        }
        if (isset($pumProject['sector_coordinator_id']) && !empty($pumProject['sector_coordinator_id'])) {
            $sectorParams = array(
                'id'     =>  $pumProject['sector_coordinator_id'],
                'return' =>  'display_name'
            );
            $sectorName = civicrm_api3('Contact', 'Getvalue', $sectorParams);
            $sectorUrl = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$pumProject['sector_coordinator_id'], true);
            $sectorHtml = '<a href="'.$sectorUrl.'">'.$sectorName.'</a>';
            $this->assign('projectSectorCoordinator', $sectorHtml);
            
        }
        if (isset($pumProject['country_coordinator_id']) && !empty($pumProject['country_coordinator_id'])) {
            $countryParams = array(
                'id'     =>  $pumProject['country_coordinator_id'],
                'return' =>  'display_name'
            );
            $countryName = civicrm_api3('Contact', 'Getvalue', $countryParams);
            $countryUrl = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$pumProject['country_coordinator_id'], true);
            $countryHtml = '<a href="'.$countryUrl.'">'.$countryName.'</a>';
            $this->assign('projectCountryCoordinator', $countryHtml);
            
        }
        if (isset($pumProject['project_officer_id']) && !empty($pumProject['project_officer_id'])) {
            $officerParams = array(
                'id'     =>  $pumProject['project_officer_id'],
                'return' =>  'display_name'
            );
            $officerName = civicrm_api3('Contact', 'Getvalue', $officerParams);
            $officerUrl = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$pumProject['project_officer_id'], true);
            $officerHtml = '<a href="'.$officerUrl.'">'.$officerName.'</a>';
            $this->assign('projectOfficer', $officerHtml);
            
        }
        if (isset($pumProject['start_date'])) {
            $this->assign('projectStartDate', date("d-m-Y", 
                strtotime($pumProject['start_date'])));
        }
        if (isset($pumProject['end_date'])) {
            $this->assign('projectEndDate', date("d-m-Y", 
                strtotime($pumProject['end_date'])));
        }
        if (isset($pumProject['is_active'])) {
            if ($pumProject['is_active'] == 1) {
                $activeHtml="<tt>[x]</tt>";
            } else {
                $activeHtml = "<tt>[ ]</tt>";
            }
        } else {
            $activeHtml = "<tt>[ ]</tt>";
        }
        $this->assign('projectIsActive', $activeHtml);
    }
    /**
     * Function to build page for add action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     * @access private
     */
    private function buildPageAdd() {
        $this->assign('action', 'add');
        
        $submitUrl = CRM_Utils_System::url('civicrm/actionprocess', null, true);
        $this->assign('submitProjectUrl', $submitUrl);

        $cancelUrl = CRM_Utils_System::url('civicrm/projectlist', null, true);
        $this->assign('cancelUrl', $cancelUrl);
               
        $titleHtml = '<input id="project-title" type="text" class="form-text" 
            name="projectTitle" size="80" maxlength="80">';
        $this->assign('projectTitle', $titleHtml);
        
        $programmeHtml = '<select id="project-programme" name="projectProgramme" 
            class="form-select"><option value="0">- none</option>';        
        $apiProgrammes = civicrm_api3('PumProgramme', 'Get', array());
        foreach ($apiProgrammes['values'] as $programmeId => $programme) {
            $programmeHtml .= '<option value="'.$programmeId.'">'.
                    $programme['title'].'</option>';
        }
        $programmeHtml .= '</select>';
        $this->assign('projectProgramme', $programmeHtml);

        $customerHtml = '<select id="project-customer" name="projectCustomer" 
            class="form-select"><option value="0">- select</option>';
        $customers = civicrm_api3('Contact', 'Get', array('contact_sub_type' => $this->_customerContactType));
        foreach ($customers['values'] as $customerId => $customer) {
            $customerHtml .= '<option value="'.$customerId.'">'.$customer['display_name'].'</option>';
        }
        $customerHtml .= '</select>';
        $this->assign('projectCustomer', $customerHtml);
        
        $reasonHtml='<textarea name="projectReason" rows="3" cols="80"></textarea>';
        $this->assign('projectReason', $reasonHtml);
        
        $workDescriptionHtml='<textarea name="projectWorkDescription" rows="3" cols="80"></textarea>';
        $this->assign('projectWorkDescription', $workDescriptionHtml);
        
        $qualificationsHtml='<textarea name="projectQualifications" rows="3" cols="80"></textarea>';
        $this->assign('projectQualifications', $qualificationsHtml);
        
        $expectedResultsHtml='<textarea name="projectExpectedResults" rows="3" cols="80"></textarea>';
        $this->assign('projectExpectedResults', $expectedResultsHtml);
        
        $sectorHtml = '<select id="project-sector-coordinator" name="projectSectorCoordinator" 
            class="form-select"><option value="0">- none</option>';        
        $sectorCoordinators = civicrm_api3('Contact', 'Get', array('group' => 
            $this->_sectorCoordinatorGroup));
        foreach ($sectorCoordinators['values'] as $sector_coordinator_id => $sector_coordinator) {
            $sectorHtml .= '<option value="'.$sector_coordinator_id.'">'.
                    $sector_coordinator['display_name'].'</option>';
        }
        $sectorHtml .= '</select>';
        $this->assign('projectSectorCoordinator', $sectorHtml);
        
        $countryHtml = '<select id="project-country-coordinator" name="projectCountryCoordinator" 
            class="form-select"><option value="0">- none</option>';        
        $countryCoordinators = civicrm_api3('Contact', 'Get', array('group' => 
            $this->_countryCoordinatorGroup));
        foreach ($countryCoordinators['values'] as $country_coordinator_id => $country_coordinator) {
            $countryHtml .= '<option value="'.$country_coordinator_id.'">'.
                    $country_coordinator['display_name'].'</option>';
        }
        $countryHtml .= '</select>';
        $this->assign('projectCountryCoordinator', $countryHtml);
        
        $officerHtml = '<select id="project-officer" name="projectOfficer" 
            class="form-select"><option value="0">- none</option>';        
        $projectOfficers = civicrm_api3('Contact', 'Get', array('group' => 
            $this->_projectOfficerGroup));
        foreach ($projectOfficers['values'] as $project_officer_id => $project_officer) {
            $officerHtml .= '<option value="'.$project_officer_id.'">'.
                    $project_officer['display_name'].'</option>';
        }
        $officerHtml .= '</select>';
        $this->assign('projectOfficer', $officerHtml);
        
        $enabledHtml = '<input id="is_active" class="form-checkbox" type="checkbox" 
            checked="checked" value="1" name="is_active">';
        $this->assign('projectIsActive', $enabledHtml);
    }
    /**
     * Function to build page for update action
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 17 Feb 2014
     * @param type $pumProject
     * @access private
     */
    private function buildPageEdit($pumProject) {
        $this->assign('action', 'update');
        $this->assign('projectId', $this->_projectId);
        
        $submitUrl = CRM_Utils_System::url('civicrm/actionprocess', null, true);
        $this->assign('submitProjectUrl', $submitUrl);

        $cancelUrl = CRM_Utils_System::url('civicrm/projectlist', null, true);
        $this->assign('cancelUrl', $cancelUrl);
        
        $titleHtml = '<input id="project-title" type="text" class="form-text" 
            name="projectTitle" value="'.$pumProject['title'].'">';
        $this->assign('projectTitle', $titleHtml);
        
        $programmeHtml = '<select id="project-programme" name="projectProgramme" 
            class="form-select"><option value="0">- none</option>';        
        $apiProgrammes = civicrm_api3('PumProgramme', 'Get', array());
        foreach ($apiProgrammes['values'] as $programmeId => $programme) {
            if ($programmeId == $pumProject['programme_id']) {
                $programmeHtml .= '<option selected="selected" value="'.$programmeId.'">'.
                    $programme['title'].'</option>';
            } else {
                $programmeHtml .= '<option value="'.$programmeId.'">'.
                    $programme['title'].'</option>';
            }
        }
        $programmeHtml .= '</select>';
        $this->assign('projectProgramme', $programmeHtml);
        
        $customerHtml = '<select id="project-customer" name="projectCustomer" 
            class="form-select">';
        $customers = civicrm_api3('Contact', 'Get', array('contact_sub_type' => $this->_customerContactType));
        foreach ($customers['values'] as $customerId => $customer) {
            if ($customerId == $pumProject['customer_id']) {
                $customerHtml .= '<option selected="selected" value="'.
                    $customerId.'">'.$customer['display_name'].'</option>';                
            } else {
                $customerHtml .= '<option value="'.$customerId.'">'.
                    $customer['display_name'].'</option>';
            }
        }
        $customerHtml .= '</select>';
        $this->assign('projectCustomer', $customerHtml);
        
        $reasonHtml='<textarea name="projectReason" rows="3" cols="80">'.$pumProject['reason'].'</textarea>';
        $this->assign('projectReason', $reasonHtml);
        
        $workDescriptionHtml='<textarea name="projectWorkDescription" rows="3" cols="80">'.$pumProject['work_description'].'</textarea>';
        $this->assign('projectWorkDescription', $workDescriptionHtml);
        
        $qualificationsUrl='<textarea name="projectQualifications" rows="3" cols="80">'.$pumProject['qualifications'].'</textarea>';
        $this->assign('projectQualifications', $qualificationsUrl);
        
        $expectedResultsHtml='<textarea name="projectExpectedResults" rows="3" cols="80">'.$pumProject['expected_results'].'</textarea>';
        $this->assign('projectExpectedResults', $expectedResultsHtml);
        
        $sectorHtml = '<select id="project-sector-coordinator" name="projectSectorCoordinator" 
            class="form-select"><option value="0">- none</option>';        
        $sectorCoordinators = civicrm_api3('Contact', 'Get', array('group' => $this->_sectorCoordinatorGroup));
        foreach ($sectorCoordinators['values'] as $sector_coordinator_id => $sectorCoordinator) {
            if ($sector_coordinator_id == $pumProject['sector_coordinator_id']) {
                $sectorHtml .= '<option selected="selected" value="'.
                    $sector_coordinator_id.'">'.$sectorCoordinator['display_name'].'</option>';                
            } else {
                $sectorHtml .= '<option value="'.$sector_coordinator_id.'">'.
                    $sectorCoordinator['display_name'].'</option>';
            }
        }
        $sectorHtml .= '</select>';
        $this->assign('projectSectorCoordinator', $sectorHtml);
        
        $countryHtml = '<select id="project-country-coordinator" name="projectCountryCoordinator" 
            class="form-select"><option value="0">- none</option>';        
        $countryCoordinators = civicrm_api3('Contact', 'Get', array('group' => $this->_countryCoordinatorGroup));
        foreach ($countryCoordinators['values'] as $country_coordinator_id => $countryCoordinator) {
            if ($country_coordinator_id == $pumProject['country_coordinator_id']) {
                $countryHtml .= '<option selected="selected" value="'.
                    $country_coordinator_id.'">'.$countryCoordinator['display_name'].'</option>';                
            } else {
                $countryHtml .= '<option value="'.$country_coordinator_id.'">'.
                    $countryCoordinator['display_name'].'</option>';
            }
        }
        $countryHtml .= '</select>';
        $this->assign('projectCountryCoordinator', $countryHtml);
        
        $officerHtml = '<select id="project-officer" name="projectOfficer" 
            class="form-select"><option value="0">- none</option>';        
        $projectOfficers = civicrm_api3('Contact', 'Get', array('group' => $this->_projectOfficerGroup));
        foreach ($projectOfficers['values'] as $project_officer_id => $projectOfficer) {
            if ($project_officer_id == $pumProject['project_officer_id']) {
                $officerHtml .= '<option selected="selected" value="'.
                    $project_officer_id.'">'.$projectOfficer['display_name'].'</option>';                
            } else {
                $officerHtml .= '<option value="'.$project_officer_id.'">'.
                    $projectOfficer['display_name'].'</option>';
            }
        }
        $officerHtml .= '</select>';
        $this->assign('projectOfficer', $officerHtml);

        if (!isset($pumProject['is_active']) || $pumProject['is_active'] == 0) {
            $enabledHtml = '<input id="project-is_active" class="form-checkbox" type="checkbox" 
                name="projectIsActive">';
        } else {
            $enabledHtml = '<input id="project-is-active" class="form-checkbox" type="checkbox" 
                checked="checked" value="1" name="projectIsActive">';            
        }
        $this->assign('projectIsActive', $enabledHtml);
        
        if (isset($pumProject['start_date']) && !empty($pumProject['start_date'])) {
            $this->assign('displayStartDate', date("d-m-Y", strtotime($pumProject['start_date'])));
        }
        if (isset($pumProject['start_date']) && !empty($pumProject['start_date'])) {
            $this->assign('displayEndDate', date("d-m-Y", strtotime($pumProject['end_date'])));
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
        $labels['projectTitle'] = '<label for="Title">'.ts('Title').'<span class="crm-marker" title="This field is required.">*</span></label>';
        $labels['projectProgramme'] = '<label for="Programme">'.ts('Programme').'</label>';
        $labels['projectCustomer'] = '<label for="Customer">'.ts('Customer').'</label>';
        $labels['projectReason'] = '<label for="Reason">'.ts('Reason For Project').'</label>';
        $labels['projectWorkDescription'] = '<label for="Work Description">'.ts('Work Description').'</label>';
        $labels['projectQualifications'] = '<label for="Qualifications">'.ts('Qualifications').'</label>';
        $labels['projectExpectedResults'] = '<label for="Expected Results">'.ts('Expected Results').'<label>';
        $labels['projectSectorCoordinator'] = '<label for="Sector Coordinator">'.ts('Sector Coordinator').'</label>';
        $labels['projectCountryCoordinator'] = '<label for="Country Coordinator">'.ts('Country Coordinator').'</label>';
        $labels['projectOfficer'] = '<label for="Project Officer">'.ts('Project Officer').'</label>';
        $labels['projectStartDate'] = '<label for="Start Date">'.ts('Start Date').'</label>';
        $labels['projectEndDate'] = '<label for="End Date">'.ts('End Date').'</label>';
        $labels['projectIsActive'] = '<label for="Is Active">'.ts('Enabled').'</label>';
        $this->assign('labels', $labels);        
    }
}
