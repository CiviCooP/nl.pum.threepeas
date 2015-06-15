<?php
class CRM_Threepeas_Form_Report_IncompleteProjectintake extends CRM_Report_Form {

  protected $_addressField = FALSE;
  protected $_emailField = FALSE;
  protected $_summary = NULL;
  protected $_customGroupExtends = array('Project');
  protected $_customGroupGroupBy = FALSE;
  protected $_from = NULL;
  protected $_where = NULL;

  /**
   * Constructor method
   */
  function __construct() {
    $this->_add2groupSupported = FALSE;
    $session = CRM_Core_Session::singleton();
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
    CRM_Utils_System::setTitle(ts('Incomplete PUM Projectintake'));
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
    $this->_select = 'SELECT id, case_type_id, subject, status_id, start_date, end_date';
  }

  /**
   * Method to set the from part of the report query
   *
   * @access public
   */
  public function from() {
    $fromClauses[] = 'civicrm_case '.$this->_columns['civicrm_case']['alias'];
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
    $threepeasConfig = CRM_Threepeas_Config::singleton();
    $rows = array();
    $dao = CRM_Core_DAO::executeQuery($sql);
    $this->modifyColumnHeaders();
    while ($dao->fetch()) {
      $row = array();
      foreach ($this->_columns['civicrm_case']['fields'] as $columnName => $columnValues) {
        if (isset($dao->$columnName)) {
          $row[$columnName] = $dao->$columnName;
        } else {
          $row[$columnName] = '';
        }
      }
      $row['authorised_id'] = CRM_Threepeas_BAO_PumCaseRelation::getRelationContactIdByCaseId($row['id'], 'authorised_contact');
      $row['authorised_name'] = CRM_Threepeas_Utils::getContactName($row['authorised_id']);
      $row['customer_id'] = CRM_Threepeas_Utils::getCaseClientId($row['id']);
      $row['customer_name'] = CRM_Threepeas_Utils::getContactName($row['customer_id']);
      $row['status'] = $threepeasConfig->caseStatus[$row['status_id']];
      if (!empty($row['start_date'])) {
        $row['start_date'] = date('d-m-Y', strtotime($row['start_date']));
      }
      if (!empty($row['end_date'])) {
        $row['end_date'] = date('d-m-Y', strtotime($row['end_date']));
      }
      $rows[] = $row;
    }
  }

  /**
   * Method to modify specific columnheaders
   *
   * @access public
   */
  public function modifyColumnHeaders() {
    $this->_columnHeaders['id'] = array('type' => 2, 'title' => ts('Main Act. ID'));
    $this->_columnHeaders['customer_name'] = array('type' => 2, 'title' => ts('Customer'));
    $this->_columnHeaders['subject'] = array('type' => 2, 'title'=> ts('Subject'));
    $this->_columnHeaders['status'] = array('type' => 2, 'title' => ts('Status'));
    $this->_columnHeaders['authorised_name'] = array('type' => 2, 'title' => ts('Authorised Contact'));
    $this->_columnHeaders['start_date'] = array('type' => 2, 'title' => ts('Start Date'));
    $this->_columnHeaders['end_date'] = array('type' => 2, 'title' => ts('End Date'));
  }
  /**
   * Method to set the columns for display and filter
   *
   * @access protected
   */
  protected function setCriteriaColumns() {
    $this->_columns = array(
      'civicrm_case' => array(
        'dao' => 'CRM_Case_DAO_Case',
        'fields' => array(
          'id' => array(
            'required' => TRUE,
            'title' => ts('Main Act. ID'),
            'default' => TRUE
          ),
          'case_type_id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
            'default' => TRUE
          ),
          'status_id' => array(
            'required' => TRUE,
            'title' => ts('Status'),
            'default' => TRUE
          ),
          'start_date' => array(
            'title' => ts('Start Date'),
            'default' => TRUE,
            'required' => TRUE
          ),
          'end_date' => array(
            'title' => ts('End Date'),
            'required' => TRUE,
            'default' => TRUE
          ),
          'subject' => array(
            'title' => ts('Subject'),
            'required' => TRUE,
            'default' => TRUE
          )
        )
      ),
    );
  }

  /**
   * Overridden parent method to get where part of query
   * only select cases of type Projectintake and status != Error
   *
   * @access public
   */
  public function where() {
    $config = CRM_Threepeas_Config::singleton();
    $caseType = CRM_Threepeas_Utils::getCaseTypeWithName('Projectintake');
    $this->_whereClauses[] = $this->_columns['civicrm_case']['alias'].'.case_type_id = "'.
      CRM_Core_DAO::VALUE_SEPARATOR.$caseType['value'].'"';
    $this->_whereClauses[] = $this->_columns['civicrm_case']['alias'].'.status_id != '.$config->getCaseErrorStatusId();
    $this->_where = "WHERE " . implode(' AND ', $this->_whereClauses);
  }

  /**
   * Overridden parent method to alter display
   * @param array $rows
   * @access public
   */
  public function alterDisplay(&$rows) {
    foreach ($rows as $rowNum => $row) {
      $caseUrlParams = "reset=1&action=view&id=".$row['id']."&cid=".$row['customer_id'];
      $caseUrl = CRM_Utils_System::url('civicrm/contact/view/case', $caseUrlParams, true);

      if (array_key_exists('id', $row)) {
        $rows[$rowNum]['id_link'] = $caseUrl;
        $rows[$rowNum]['id_hover'] = 'Click to view the main activity';
      }

      if (array_key_exists('subject', $row)) {
        $rows[$rowNum]['subject_link'] = $caseUrl;
        $rows[$rowNum]['subject_hover'] = 'Click to view the main activity';
      }

      if (array_key_exists('customer_name', $row)) {
        $customerUrl = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$row['customer_id'], true);
        $rows[$rowNum]['customer_name_link'] = $customerUrl;
        $rows[$rowNum]['customer_name_hover'] = 'Click to view the customer';
      }

      if (array_key_exists('authorised_name', $row)) {
        $authorisedUrl = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$row['authorised_id'], true);
        $rows[$rowNum]['authorised_name_link'] = $authorisedUrl;
        $rows[$rowNum]['authorised_name_hover'] = 'Click to view the authorised contact';
      }
    }
  }
}
