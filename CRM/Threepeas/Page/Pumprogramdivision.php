<?php

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Pumprogramdivision extends CRM_Core_Page {
    protected $_programId = 0;
    protected $_action = 0;
    
    function run() {
        CRM_Utils_System::setTitle(ts('Program Budget Division'));
        
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);
        $this->_programId = CRM_Utils_Request::retrieve('pid', 'Positive', $this);
        
        $this->assign("doneUrl", CRM_Utils_System::url("civicrm/programdivision", null, true));

        $submitUrl = CRM_Utils_System::url('civicrm/actionprocess', null, true)
            ."&pumAction=add&pumEntity=budgetdivision";
        $this->assign('submitUrl', $submitUrl);
        
        $deleteUrl = CRM_Utils_System::url('civicrm/actionprocess', null, true)
            ."&pumAction=delete&pumEntity=budgetdivision";
        $this->assign('delProgramDivisionUrl', $deleteUrl);

        /*
         * retrieve and display program details
         */
        $programData = CRM_Threepeas_PumProgram::getProgramById($this->_programId);
        
        $this->assign("programId", $this->_programId);
        $this->assign("programTitle", "<strong>{$programData['title']}</strong>");
        $contactParams = array(
            'id'        =>  $programData['contact_id_manager'],
            'return'    =>  "display_name"
        );
        $programManagerName = civicrm_api3('Contact', 'Getvalue', $contactParams);
        $this->assign("programManager", "<strong>$programManagerName</strong>");
        $programBudget = CRM_Utils_Money::format($programData['budget']);
        $this->assign("programBudget", "<strong>$programBudget</strong>");
        /*
         * line for new division
         */
        $countryHtml = '<select id="program-division-country" name="programDivisionCountry" 
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
        
        $minProjectsHtml = '<input id="program-division-min-projects" type="number" 
            class="form=text" name="programDivisionMinProjects" size="10">';
        $this->assign("newMinProjects", $minProjectsHtml);
        
        $maxProjectsHtml = '<input id="program-division-max-projects" type="number" 
            class="form=text" name="programDivisionMaxProjects" size="10">';
        $this->assign("newMaxProjects", $maxProjectsHtml);
        
        $minBudgetHtml = '<input id="program-division-min-budget" type="number" 
            class="form=text" name="programDivisionMinBudget" size="15">';
        $this->assign("newMinBudget", $minBudgetHtml);
        
        $maxBudgetHtml = '<input id="program-division-max-budget" type="number" 
            class="form=text" name="programDivisionMaxBudget" size="15">';
        $this->assign("newMaxBudget", $maxBudgetHtml);
        /*
         * retrieve existing programDivisions
         */
        $params = array('program_id' => $this->_programId);
        $displayDivisions = array();
        $programDivisions = CRM_Threepeas_PumProgramDivision::getAllProgamDivisionsForProgram($params);
        foreach ($programDivisions as $programDivisionId => $programDivision) {
            $displayDivision = array();
            $displayDivision['id'] = $programDivisionId;
            $displayDivision['program_id'] = $this->_programId;
            $displayDivision['country'] = $apiCountries['values']
                    [$programDivision['country_id']]['name'];
            $displayDivision['min_projects'] = $programDivision['min_projects'];
            $displayDivision['max_projects'] = $programDivision['max_projects'];
            $displayDivision['min_budget'] = CRM_Utils_Money::format(
                    $programDivision['min_budget']);
            $displayDivision['max_budget'] = CRM_Utils_Money::format(
                    $programDivision['max_budget']);
            $displayDivisions[] = $displayDivision;
        }
        $this->assign("pumProgramDivisions", $displayDivisions);
       
        parent::run();
    }
}
