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
  protected $userSelect = array();
  protected $userCountryCoordinator = FALSE;
  protected $userSectorCoordinator = FALSE;
  protected $userProjectOfficer = FALSE;

  /*
   * properties used to store start and end date values
   */
  protected $startDateFrom = NULL;
  protected $startDateTo = NULL;
  protected $endDateFrom = NULL;
  protected $endDateTo = NULL;

  /**
   * Constructor method
   */
  function __construct() {
    $this->_add2groupSupported = FALSE;
    $this->setCustomerSelect();
    $this->setCountrySelect();
    $this->setUserSelect();
    $session = CRM_Core_Session::singleton();
    $request = CRM_Utils_Request::exportValues();
    $session->pushUserContext(CRM_Utils_System::url($request['q'], 'reset=1', true));
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
     * addRows for selected user
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
    if (!isset($this->_params['user_id_value']) || empty($this->_params['user_id_value'])) {
      $session = CRM_Core_Session::singleton();
      $userId = $session->get('userID');
    } else {
      $userId = $this->_params['user_id_value'];
    }
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
      if ($this->isRowSelectable($foundProject) == TRUE) {
        $projectRow = $this->buildAdditionalRow($foundProject);
        $userRows[$projectRow['project_id']] = $this->buildSingleRow($projectRow);
      }
    }
  }

  /**
   * Method to determine if projectRow added for user roles can be
   * selected according to report filters for title, country, customer and date range
   *
   * @param array $projectRow
   * @return bool
   * @access protected
   */
  protected function isRowSelectable($projectRow) {
    if (isset($this->_params['title_op']) && !empty($this->_params['title_op'])) {
      if ($this->checkTitleValue($projectRow['title']) == FALSE) {
        return FALSE;
      }
    }
    if (isset($this->_params['country_id_op']) && !empty($this->_params['country_id_op'])) {
      if (!isset($projectRow['country_id'])) {
        $projectRow['country_id'] = 0;
      }
      if (!isset($projectRow['customer_id'])) {
        $projectRow['customer_id'] = 0;
      }
      if ($this->checkCountryIdValue($projectRow['country_id'], $projectRow['customer_id']) == FALSE) {
        return FALSE;
      }
    }
    if (isset($this->_params['customer_id_op']) && !empty($this->_params['customer_id_op'])) {
      if (isset($projectRow['customer_id'])) {
        if ($this->checkCustomerIdValue($projectRow['customer_id']) == FALSE) {
          return FALSE;
        }
      }
    }
    if (isset($this->_params['start_date_relative']) && $this->_params['start_date_relative'] != '') {
      if (!isset($projectRow['start_date'])) {
        $projectRow['start_date'] = null;
      }
      if ($this->checkDateValue($projectRow['start_date'], 'start') == FALSE) {
        return FALSE;
      }
    }
    if (isset($this->_params['end_date_relative']) && $this->_params['end_date_relative'] != '') {
      if (!isset($projectRow['end_date'])) {
        $projectRow['end_date'] = null;
      }
      if ($this->checkDateValue($projectRow['end_date'], 'end') == FALSE) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Method to check if date project is in selected date range
   *
   * @param string $projectDate
   * @param string $dateType
   * @return bool
   * @access protected
   */
  protected function checkDateValue($projectDate, $dateType) {
    $validDate = FALSE;
    $relativeName = $dateType.'_date_relative';
    switch ($this->_params[$relativeName]) {
      case 'nll':
        if (empty($projectDate)) {
          $validDate = TRUE;
        }
        break;
      case 'nnll':
        if (!empty($projectDate)) {
          $validDate = TRUE;
        }
        break;
      default:
        $projectDate = date('YmdHis', strtotime($projectDate));
        if ($dateType == 'start') {
          if (!empty($this->startDateFrom) && !empty($this->startDateTo)) {
            if ($projectDate >= $this->startDateFrom && $projectDate <= $this->startDateTo) {
              $validDate = TRUE;
            }
          } else {
            if (!empty($this->startDateFrom)) {
              if ($projectDate >= $this->startDateFrom) {
                $validDate = TRUE;
              }
            }
            if (!empty($this->startDateTo)) {
              if ($projectDate <= $this->startDateTo) {
                $validDate = TRUE;
              }
            }
          }
        }
        if ($dateType == 'end') {
          if (empty($this->endDateFrom) && empty($this->endDateTo)) {
            $validDate = TRUE;
          }
          if (!empty($this->endDateFrom) && !empty($this->endDateTo)) {
            if ($projectDate >= $this->endDateFrom && $projectDate <= $this->endDateTo) {
              $validDate = TRUE;
            }
          } else {
            if (!empty($this->endDateFrom)) {
              if ($projectDate >= $this->endDateFrom) {
                $validDate = TRUE;
              }
            }
            if (!empty($this->endDateTo)) {
              if ($projectDate <= $this->endDateTo) {
                $validDate = TRUE;
              }
            }
          }
        }
        break;
    }
    return $validDate;
  }

  /**
   * Method to check if project can be selected with customer selected
   *
   * @param int $customerId
   * @return bool $customerValid
   * @access protected
   */
  protected function checkCustomerIdValue($customerId) {
    if (empty($this->_params['customer_id_value'])) {
      return TRUE;
    }
    $customerValid = FALSE;
    switch ($this->_params['customer_id_op']) {
      case 'in':
        if (in_array($customerId, $this->_params['customer_id_value'])) {
          $customerValid = TRUE;
        }
      break;
      case 'notin':
        if (!in_array($customerId, $this->_params['customer_id_value'])) {
          $customerValid = TRUE;
        }
      break;
    }
    return $customerValid;
  }

  /**
   * Method to check if project can be selected with country selected
   * (checks project country AND project customer country)
   *
   * @param int $countryId
   * @param int $customerId
   * @return bool $countryValid
   * @access protected
   */
  protected function checkCountryIdValue($countryId, $customerId) {
    if (empty($this->_params['country_id_value'])) {
      return TRUE;
    }
    $countryValid = FALSE;
    if (empty($countryId)) {
      try {
        $contactData = civicrm_api3('Contact', 'Getsingle', array('id' => $customerId));
        $countryCustomGroup = CRM_Threepeas_CountryCustomGroup::singleton();
        $countryContactParams = array(
          'custom_' . $countryCustomGroup->getCountryCustomFieldId() => $contactData['country_id'],
          'return' => 'id');
        try {
          $countryId = civicrm_api3('Contact', 'Getvalue', $countryContactParams);
        } catch (CiviCRM_API3_Exception $ex) {
          $countryValid = FALSE;
        }
      } catch (CiviCRM_API3_Exception $ex) {
        $countryValid = FALSE;
      }
    }
    switch ($this->_params['country_id_op']) {
      case 'in':
        if (in_array($countryId, $this->_params['country_id_value'])) {
          $countryValid = TRUE;
        }
      break;
      case 'notin':
        if (!in_array($countryId, $this->_params['country_id_value'])) {
          $countryValid = TRUE;
        }
      break;
    }
    return $countryValid;
  }

  /**
   * Method to chech if the current project title meets the report selection criteria
   *
   * @param string $projectTitle
   * @return bool $valueValid
   * @access protected
   */
  protected function checkTitleValue($projectTitle) {
    $valueValid = FALSE;
    switch ($this->_params['title_op']) {
      case 'sw':
        $valLength = strlen($this->_params['title_value']);
        if (substr($projectTitle, 0, $valLength) == $this->_params['title_value']) {
          $valueValid = TRUE;
        }
      break;
      case 'ew':
        $valLength = '-'.strlen($this->_params['title_value']);
        if (substr($projectTitle, $valLength) == $this->_params['title_value']) {
          $valueValid = TRUE;
        }
      break;
      case 'nhas':
        if (strpos($projectTitle, $this->_params['title_value']) == FALSE) {
          $valueValid = TRUE;
        }
      break;
      case 'eq':
        if ($projectTitle == $this->_params['title_value']) {
          $valueValid = TRUE;
        }
      break;
      case 'neq':
        if ($projectTitle != $this->_params['title_value']) {
          $valueValid = TRUE;
        }
      break;
      case 'has':
        if (empty($this->_params['title_value'])) {
          return TRUE;
        } else {
          if (strpos($projectTitle, $this->_params['title_value']) != FALSE) {
            $valueValid = TRUE;
          }
        }
      break;
      case 'nll':
        if (empty($projectTitle)) {
          $valueValid = TRUE;
        }
      break;
      case 'nnll':
        if (!empty($projectTitle)) {
          $valueValid = TRUE;
        }
      break;
    }
    return $valueValid;
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
      'user_id' => $this->_params['user_id_value']);
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
          'user_id' => array(
            'title' => ts('Projects for user'),
            'default' => 1,
            'pseudofield' => 1,
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $this->userSelect,
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
      'options' => array('limit' => 0),
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
      'options' => array('limit' => 0),
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
  protected function setUserSelect() {
    if (method_exists('CRM_Groupsforreports_GroupReport', 'getGroupMembersForReport')) {
      $allContacts = CRM_Groupsforreports_GroupReport::getGroupMembersForReport(__CLASS__);
      $sortedContacts = array();
      foreach ($allContacts as $contact) {
        $sortedContacts[$contact] = CRM_Threepeas_Utils::getContactName($contact);
      }
      asort($sortedContacts);
      $this->userSelect = array(0 => 'current user') + $sortedContacts;
    }
  }

  /**
   * Overridden parent method to get where part of query
   *
   * @access public
   */
  public function where() {
    if (!isset($this->_params['user_id_value']) || empty($this->_params['user_id_value'])) {
      $session = CRM_Core_Session::singleton();
      $userId = $session->get('userID');
    } else {
      $userId = $this->_params['user_id_value'];
    }
    $this->_whereClauses[] = '('.$this->_columns['civicrm_project']['alias'].'.is_active = 1)';
    $this->_whereClauses[] = '('.$this->_columns['civicrm_project']['alias'].'.projectmanager_id = '.$userId.')';
    $this->storeWhereHavingClauseArray();
    $this->_where = "WHERE " . implode(' AND ', $this->_whereClauses);
  }

  /**
   * Overridden parent method to catch to and from dates so they can be used in additional project Rows
   *
   * @param $fieldName
   * @param $relative
   * @param $from
   * @param $to
   * @param null $type
   * @param null $fromTime
   * @param null $toTime
   * @return null|string
   */
  function dateClause($fieldName,
                      $relative, $from, $to, $type = NULL, $fromTime = NULL, $toTime = NULL
  ) {
    $clauses = array();
    if (in_array($relative, array_keys($this->getOperationPair(CRM_Report_Form::OP_DATE)))) {
      $sqlOP = $this->getSQLOperator($relative);
      return "( {$fieldName} {$sqlOP} )";
    }

    list($from, $to) = $this->getFromTo($relative, $from, $to, $fromTime, $toTime);
    /*
     * store from and to in class properties so they can be used in comparison of added rows
     */
    $startDateField = $this->_columns['civicrm_project']['alias'].'.start_date';
    $endDateField = $this->_columns['civicrm_project']['alias'].'.end_date';
    if ($fieldName == $startDateField) {
      $this->startDateFrom = $from;
      $this->startDateTo = $to;
    }
    if ($fieldName == $endDateField) {
      $this->endDateFrom = $from;
      $this->endDateTo = $to;
    }

    if ($from) {
      $from = ($type == CRM_Utils_Type::T_DATE) ? substr($from, 0, 8) : $from;
      $clauses[] = "( {$fieldName} >= $from )";
    }

    if ($to) {
      $to = ($type == CRM_Utils_Type::T_DATE) ? substr($to, 0, 8) : $to;
      $clauses[] = "( {$fieldName} <= {$to} )";
    }

    if (!empty($clauses)) {
      return implode(' AND ', $clauses);
    }
    return NULL;
  }

}
