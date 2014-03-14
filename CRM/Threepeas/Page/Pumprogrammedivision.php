<?php
/**
 * Page Pumprogrammedivision to add or delete a programme budget division (PUM)
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 17 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> under the Academic Free License version 3.0.
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Pumprogrammedivision extends CRM_Core_Page {
    protected $_programmeId = 0;
    protected $_action = 0;
    
    function run() {
        CRM_Utils_System::setTitle(ts('Programme Budget Division'));
        
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);
        $this->_programmeId = CRM_Utils_Request::retrieve('pid', 'Positive', $this);
        
        $this->assign("doneUrl", CRM_Utils_System::url("civicrm/programmedivision", null, true));

        $submitUrl = CRM_Utils_System::url('civicrm/actionprocess', 'pumAction=add&pumEntity=budgetdivision', true);
        $this->assign('submitUrl', $submitUrl);
        
        $deleteUrl = CRM_Utils_System::url('civicrm/actionprocess', 'pumAction=delete&pumEntity=budgetdivision', true);
        $this->assign('delProgrammeDivisionUrl', $deleteUrl);

        /*
         * retrieve and display programme details
         */
        $programmeData = CRM_Threepeas_PumProgramme::getProgrammeById($this->_programmeId);
        
        $this->assign("programmeId", $this->_programmeId);
        $this->assign("programmeTitle", "<strong>{$programmeData['title']}</strong>");
        if (isset($programmeData['contact_id_manager']) && !empty($programmeData['contact_id_manager'])) {
            $contactParams = array(
                'id'        =>  $programmeData['contact_id_manager'],
                'return'    =>  "display_name"
            );
            $programmeManagerName = civicrm_api3('Contact', 'Getvalue', $contactParams);
            $this->assign("programmeManager", "<strong>$programmeManagerName</strong>");
        }
        $programmeBudget = CRM_Utils_Money::format($programmeData['budget']);
        $this->assign("programmeBudget", "<strong>$programmeBudget</strong>");
        /*
         * line for new division
         */
        $countryHtml = '<select id="programme-division-country" name="programmeDivisionCountry" 
            class="form-select">';
        $countryParams = array(
            'options'   =>  array(
                'limit'     =>  99999,
                'sort'      =>  'name'
            )
        );
        $apiCountries = civicrm_api3('Country', 'Get', $countryParams);
        asort($apiCountries);
        foreach ($apiCountries['values'] as $countryId => $apiCountry) {
            $countryHtml .= '<option value="'.$countryId.'">'.
                    $apiCountry['name'].'</option>';
        }
        $countryHtml .= '</select>';
        $this->assign('newCountry', $countryHtml);
        
        $minProjectsHtml = '<input id="programme-division-min-projects" type="number" 
            class="form=text" name="programmeDivisionMinProjects" size="10">';
        $this->assign("newMinProjects", $minProjectsHtml);
        
        $maxProjectsHtml = '<input id="programme-division-max-projects" type="number" 
            class="form=text" name="programmeDivisionMaxProjects" size="10">';
        $this->assign("newMaxProjects", $maxProjectsHtml);
        
        $minBudgetHtml = '<input id="programme-division-min-budget" type="number" 
            class="form=text" name="programmeDivisionMinBudget" size="15">';
        $this->assign("newMinBudget", $minBudgetHtml);
        
        $maxBudgetHtml = '<input id="programme-division-max-budget" type="number" 
            class="form=text" name="programmeDivisionMaxBudget" size="15">';
        $this->assign("newMaxBudget", $maxBudgetHtml);
        /*
         * retrieve existing programmeDivisions
         */
        $params = array('programme_id' => $this->_programmeId);
        $displayDivisions = array();
        $programmeDivisions = CRM_Threepeas_PumProgrammeDivision::getAllProgrammeDivisionsForProgramme($params);
        foreach ($programmeDivisions as $programmeDivisionId => $programmeDivision) {
            $displayDivision = array();
            $displayDivision['id'] = $programmeDivisionId;
            $displayDivision['programme_id'] = $this->_programmeId;
            $displayDivision['country'] = $apiCountries['values']
                    [$programmeDivision['country_id']]['name'];
            $displayDivision['min_projects'] = $programmeDivision['min_projects'];
            $displayDivision['max_projects'] = $programmeDivision['max_projects'];
            $displayDivision['min_budget'] = CRM_Utils_Money::format(
                    $programmeDivision['min_budget']);
            $displayDivision['max_budget'] = CRM_Utils_Money::format(
                    $programmeDivision['max_budget']);
            $displayDivisions[] = $displayDivision;
        }
        $this->assign("pumProgrammeDivisions", $displayDivisions);
       
        parent::run();
    }
}
