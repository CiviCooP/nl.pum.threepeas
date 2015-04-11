<?php

class CRM_Threepeas_Form_Report_PumProjects extends CRM_Report_Form {

  protected $_addressField = FALSE;
  protected $_emailField = FALSE;
  protected $_summary = NULL;
  protected $_customGroupExtends = array('Project');
  protected $_customGroupGroupBy = FALSE;
  protected $_from = NULL;
  protected $_where = NULL;

  protected $customerSelect = array();
  protected $countrySelect = array();
  protected $tableAlias = NULL;
  protected $userId = NULL;
  protected $userCountryCoordinator = FALSE;
  protected $userSectorCoordinator = FALSE;
  protected $userProjectOfficer = FALSE;

  /**
   * Constructor method
   */
  function __construct() {
    $this->_add2groupSupported = FALSE;
    $this->setCustomerSelect();
    $this->setCountrySelect();
    $session = CRM_Core_Session::singleton();
    $this->userId = $session->get('userID');
    $userContextUrl = CRM_Utils_Request::retrieve('q', 'String');
    $session->pushUserContext(CRM_Utils_System::url($userContextUrl, 'reset=1', true));
    $this->setCriteriaColumns();
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    parent::__construct();
  }

  /**
   * Method to perform pre-processing of report
   * (override of parent method)
   *
   * @access public
   */
  public function preProcess() {
    CRM_Utils_System::setTitle(ts('My PUM Projects'));
    parent::preProcess();
  }

  /**
   * Method to perform processing of report
   * (override of parent function)
   *
   * @access public
   */
  public function postProcess() {


    $this->beginPostProcess();

    $sql = $this->buildQuery(TRUE);
    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  /**
   * Method to set the select part of the report query
   *
   * @access public
   */
  public function select() {
    $selectClauses = array(
      'programme.title AS programmeName',
      'customer.display_name AS customerName',
      'country.display_name AS countryName',
      'projectmanager.display_name AS projectManagerName');
    foreach ($this->_columns as $tableName => $tableValues) {
      foreach ($tableValues['fields'] as $fieldName => $fieldValues) {
        $selectClauses[] = $fieldValues['dbAlias'];
      }
    }
    $this->_select = 'SELECT '.implode(', ', $selectClauses);
  }

  /**
   * Method to set the from part of the report query
   *
   * @access public
   */
  public function from() {
    $this->tableAlias = $this->_columns['civicrm_project']['alias'];
    $fromClauses[] = 'civicrm_project '.$this->tableAlias;
    $fromClauses[] = 'LEFT JOIN civicrm_programme programme ON '.$this->tableAlias.'.programme_id = programme.id';
    $fromClauses[] = 'LEFT JOIN civicrm_contact customer ON '.$this->tableAlias.'.customer_id = customer.id';
    $fromClauses[] = 'LEFT JOIN civicrm_contact country ON '.$this->tableAlias.'.country_id = country.id';
    $fromClauses[] = 'LEFT JOIN civicrm_contact projectmanager ON '.$this->tableAlias.'.projectmanager_id = projectmanager.id';
    $this->_from = 'FROM '.implode(' ', $fromClauses);
  }

  /**
   * Overridden parent method to build the report rows
   * First select all rows from dao (if current_user only the ones
   * where the current user is project manager)
   *
   * @param string $sql
   * @param array $rows
   * @access public
   */
  function buildRows($sql, &$rows) {
    $rows = array();
    $dao = CRM_Core_DAO::executeQuery($sql);
    $daoRows = array();
    $this->modifyColumnHeaders();
    while ($dao->fetch()) {
      $daoRows[$dao->id] = $this->buildSingleRow($this->buildDaoRow($dao));
    }
    /*
     * addRows for current user if only current user
     * (country coordinator, sector coordinator, prof or any active case role)
     */
    $userRows = array();
    $this->addUserRows($userRows);
    $rows = $daoRows + $userRows;
    krsort($rows);
  }
  /**
   * Method to add rows if current_user = yes
   * - if user is country coordinator, if so retrieve all project for that country
   * - if user is sector coordinator, if so check for project for all customers in that sector
   * - if user if prof for a country or customer, if so apply those as explained above
   * - check if user has active role in cases for projects and add those
   * For all: only add if not in row already
   *
   * @param array $userRows
   * @access protected
   */
  protected function addUserRows(&$userRows) {
    if (isset($this->_submitValues['current_user_value']) && $this->_submitValues['current_user_value'] == 1) {
      $session = CRM_Core_Session::singleton();
      $userId = $session->get('userID');
      $countryCoordinatorIds = CRM_Threepeas_BAO_PumCaseRelation::isContactRelationFor($userId, 'country_coordinator');
      $projectOfficerIds = CRM_Threepeas_BAO_PumCaseRelation::isContactRelationFor($userId, 'project_officer');
      $sectorCoordinatorIds = CRM_Threepeas_BAO_PumCaseRelation::isContactSectorCoordinatorFor($userId);
      $activeCaseProjectIds = CRM_Threepeas_BAO_PumCaseRelation::isContactActiveInCases($userId);
      $contactIds = array_merge($countryCoordinatorIds, $projectOfficerIds, $sectorCoordinatorIds);
      $allContactProjects = array();
      foreach ($contactIds as $contactId) {
        $contactProjects = CRM_Threepeas_BAO_PumProject::getContactProjects($contactId);
        foreach ($contactProjects as $contactProject) {
          $allContactProjects[$contactProject['id']] = $contactProject;
        }
      }
      $foundProjects = array_merge($allContactProjects + $activeCaseProjectIds);
      foreach ($foundProjects as $foundProject) {
        $projectRow = self::buildAdditionalRow($foundProject);
        $userRows[$projectRow['project_id']] = self::buildSingleRow($projectRow);
      }
    }
  }
  /**
   * Method to build data array from additional projects
   *
   * @param array $contactProject
   * @return array $singleRow
   * @access protected
   */
  protected function buildAdditionalRow($contactProject) {
    $singleRow['project_id'] = $contactProject['id'];
    $rowFields = array(
      'project_id' => 'id',
      'title' => 'title',
      'customer_id' => 'customer_id',
      'country_id' => 'country_id',
      'programme_id' => 'programme_id',
      'projectmanager_id' => 'projectmanager_id',
      'start_date' => 'start_date',
      'end_date' => 'end_date');
    foreach ($rowFields as $singleName => $projectName) {
      if (isset($contactProject[$projectName])) {
        $singleRow[$singleName] = $contactProject[$projectName];
      } else {
        $singleRow[$singleName] = null;
      }
    }

    if (!empty($singleRow['programme_id'])) {
      $singleRow['programme'] = CRM_Threepeas_BAO_PumProgramme::getProgrammeTitleWithId($singleRow['programme_id']);
    } else {
      $singleRow['programme'] = null;
    }

    if (!empty($singleRow['customer_id'])) {
      $singleRow['customer_name'] = CRM_Threepeas_Utils::getContactName($singleRow['customer_id']);
    } else {
      $singleRow['customer_name'] = CRM_Threepeas_Utils::getContactName($singleRow['country_id']);
    }

    if (!empty($singleRow['projectmanager_id'])) {
      $singleRow['project_manager'] = CRM_Threepeas_Utils::getContactName($singleRow['projectmanager_id']);
    } else {
      $singleRow['project_manager'] = null;
    }
    return $singleRow;
  }

  /**
   * Method to build data array from dao
   *
   * @param object $dao
   * @return array $singleRow
   * @access protected
   */
  protected function buildDaoRow($dao) {
    $singleRow = array();
    $singleRow['project_id'] = $dao->id;
    $singleRow['customer_id'] = $dao->customer_id;
    $singleRow['country_id'] = $dao->country_id;
    $singleRow['programme_id'] = $dao->programme_id;
    $singleRow['projectmanager_id'] = $dao->projectmanager_id;
    $singleRow['title'] = $dao->title;
    $singleRow['programme'] = $dao->programmeName;
    if (!empty($dao->customerName)) {
      $singleRow['customer_name'] = $dao->customerName;
    } else {
      $singleRow['customer_name'] = $dao->countryName;
    }
    $singleRow['project_manager'] = $dao->projectManagerName;
    $singleRow['start_date'] = $dao->start_date;
    $singleRow['end_date'] = $dao->end_date;
    return $singleRow;
  }

  /**
   * Method to build single row
   *
   * @param array $singleRow
   * @return array $singleRow
   * @access protected
   */
  protected function buildSingleRow($singleRow) {
    $config = CRM_Core_Config::singleton();
    $singleRow['start_date'] = CRM_Utils_Date::customFormat($singleRow['start_date'], $config->dateformatFull);
    $singleRow['end_date'] = CRM_Utils_Date::customFormat($singleRow['end_date'], $config->dateformatFull);
    $roleParams = array(
      'project_id' => $singleRow['project_id'],
      'user_id' => $this->userId);
    if (!empty($singleRow['customer_id'])) {
      $roleParams['customer_id'] = $singleRow['customer_id'];
    } else {
      $roleParams['customer_id'] = $singleRow['country_id'];
    }
    $singleRow['my_role'] = CRM_Threepeas_BAO_PumProject::getUserRoles($roleParams);
    $drillUrl = CRM_Utils_System::url('civicrm/pumdrill', 'reset=1&pumEntity=project&pid='.$singleRow['project_id'], true);
    $singleRow['drilldown'] = '<a href="'.$drillUrl.'">Drill Down</a>';
    return $singleRow;
  }


  /**
   * Method to modify specific columnheaders
   *
   * @access public
   */
  public function modifyColumnHeaders() {
    $this->_columnHeaders['title'] = array('type' => 2, 'title' => ts('Project Name'));
    $this->_columnHeaders['programme'] = array('type' => 2, 'title' => ts('Programme'));
    $this->_columnHeaders['customer_name'] = array('type' => 2, 'title' => ts('Customer or Country'));
    $this->_columnHeaders['project_manager'] = array('type' => 2, 'title' => ts('Project Manager'));
    $this->_columnHeaders['start_date'] = array('type' => 2, 'title' => ts('Start Date'));
    $this->_columnHeaders['end_date'] = array('type' => 2, 'title' => ts('End Date'));
    $this->_columnHeaders['my_role'] = array('type' => 2, 'title' => ts('My Role'));
    $this->_columnHeaders['drilldown'] = array('type' => 2, 'title' => '');
  }

  /**
   * Method to set the where part of the report query
   *
   * @access public
   */
  public function where() {
    $whereClauses[] = $this->tableAlias.'.is_active = 1';
    if (isset($this->_submitValues['current_user_value']) && $this->_submitValues['current_user_value'] == 1) {
      $session = CRM_Core_Session::singleton();
      $userId = $session->get('userID');
      $whereClauses[] = $this->tableAlias.'.projectmanager_id = '.$userId;
    }
    $this->_where = ' WHERE '.implode(' AND ', $whereClauses);
  }

  /**
   * Overridden parent method to alter display rows (make clickable)
   *
   * @param array $rows
   */
  function alterDisplay(&$rows) {
    foreach ($rows as $rowNum => $row) {
      if (isset($row['title']) && !empty($row['title'])) {
        $url = CRM_Utils_System::url('civicrm/pumproject', 'action=view&pid='.
          $row['project_id'], $this->_absoluteUrl);
        $rows[$rowNum]['title_link'] = $url;
        $rows[$rowNum]['title_hover'] = 'Click to view project details';
      }
      if (isset($row['programme']) && !empty($row['programme'])) {
        $url = CRM_Utils_System::url('civicrm/pumprogramme', 'action=view&pid='.
          $row['programme_id'], $this->_absoluteUrl);
        $rows[$rowNum]['programme_link'] = $url;
        $rows[$rowNum]['programme_hover'] = 'Click to view programme details';
      }
      if (isset($row['customer_name']) && !empty($row['customer_name'])) {
        if (!empty($row['customer_id'])) {
          $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.
            $row['customer_id'], $this->_absoluteUrl);
        } else {
          $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.
            $row['country_id'], $this->_absoluteUrl);
        }
        $rows[$rowNum]['customer_name_link'] = $url;
        $rows[$rowNum]['customer_name_hover'] = 'Click to view customer or country details';
      }
      if (isset($row['project_manager']) && !empty($row['project_manager'])) {
        $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.
          $row['projectmanager_id'], $this->_absoluteUrl);
        $rows[$rowNum]['project_manager_link'] = $url;
        $rows[$rowNum]['project_manager_hover'] = 'Click to view project manager details';
      }
    }
  }

  /**
   * Method to set the columns for display and filter
   *
   * @access protected
   */
  protected function setCriteriaColumns() {
    $this->_columns = array(
      'civicrm_project' => array(
        'dao' => 'CRM_Threepeas_DAO_PumProject',
        'fields' => array(
          'title' => array(
            'title' => ts('Name Project'),
            'required' => TRUE,
            'default' => TRUE,
            'no_repeat' => TRUE,
          ),
          'id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'programme_id' => array(
            'title' => ts('Programme'),
            'default' => TRUE,
            'no_repeat' => TRUE,
          ),
          'customer_id' => array(
            'title' => ts('Customer'),
            'default' => TRUE,
            'no_repeat' => TRUE,
          ),
          'country_id' => array(
            'title' => ts('Country'),
            'default' => TRUE,
            'no_repeat' => TRUE,
          ),
          'projectmanager_id' => array(
            'title' => ts('Projectmanager'),
            'default' => TRUE,
            'no_repeat' => TRUE,
          ),
          'start_date' => array(
            'title' => ts('Start Date'),
          ),
          'end_date' => array(
            'title' => ts('End Date'),
          ),
        ),
        'filters' => array(
          'current_user' => array(
            'title' => ts('Limit To Current User'),
            'default' => 1,
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array('0' => ts('No'), '1' => ts('Yes')),
          ),
          'title' => array(
            'title' => ts('Project'),
            'type' => CRM_Utils_Type::T_STRING,
            'operator' => 'like',
          ),
          'country_id' => array(
            'title' => ts('Country'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->countrySelect,
          ),
          'start_date' => array(
            'title' => ts('Start Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'end_date' => array(
            'title' => ts('End Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'customer_id' => array(
            'title' => ts('Customer'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->customerSelect,
          ),
        ),
      ),
    );
  }

  /**
   * Method to set the customers for the select list in the filter
   *
   * @access protected
   */
  protected function setCustomerSelect() {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $customerParams = array(
      'contact_sub_type' => $threepeasConfig->customerContactType,
      'contact_is_deleted' => 0,
      'return' => 'display_name'
    );
    try {
      $customerContacts = civicrm_api3('Contact', 'Get', $customerParams);
      foreach ($customerContacts['values'] as $contactId => $contactValues) {
        $this->customerSelect[$contactId] = $contactValues['display_name'];
      }
    } catch (CiviCRM_API3_Exception $ex) {
    }
  }
  /**
   * Method to set the countries for the select list in the filter
   *
   * @access protected
   */
  protected function setCountrySelect() {
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $countryParams = array(
      'contact_sub_type' => $threepeasConfig->countryContactType,
      'contact_is_deleted' => 0,
      'return' => 'display_name'
    );
    try {
      $countryContacts = civicrm_api3('Contact', 'Get', $countryParams);
      foreach ($countryContacts['values'] as $contactId => $contactValues) {
        $this->countrySelect[$contactId] = $contactValues['display_name'];
      }
    } catch (CiviCRM_API3_Exception $ex) {
    }
  }
}
