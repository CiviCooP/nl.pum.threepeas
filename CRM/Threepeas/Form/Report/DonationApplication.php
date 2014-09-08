<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM specific report for PUM <www.pum.nl>                       |
 | part of extension nl.pum.threepeas                                 |
 |                                                                    |
 | @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>          |
 | @date 8 Sep 2014                                                   |
 | Shows selected donations with their linked programmes/projects/    |
 | cases                                                              |
 +--------------------------------------------------------------------+
 |                                                                    |
 | Copyright (C) 2014 Coöperatieve CiviCooP U.A.                      |
 | <http://www.civicoop.org>                                          |
 | Licensed to PUM <http://www.pum.nl> and CiviCRM under the          |
 | Academic Free License version 3.0.                                 |
 | <http://opensource.org/licenses/AFL-3.0>                           |
 +--------------------------------------------------------------------+
 */
class CRM_Threepeas_Form_Report_DonationApplication extends CRM_Report_Form {

  protected $_addressField = FALSE;
  protected $_emailField = FALSE;
  protected $_summary = NULL;
  protected $_customGroupExtends = array('Contribution');
  protected $_customGroupGroupBy = FALSE; 
  protected $_campaignEnabled = false;
  protected $_financialTypes = array();
  protected $_contributionStatusIds = array();
  
  /*
   * Constructor function
   */
  function __construct() {
    $this->_add2groupSupported = false;
    $this->getCampaigns();
    $this->setReportColumns();
    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('PUM Donation Application Report'));
    parent::preProcess();
  }

  function from() {
    $this->_from = "
      FROM  civicrm_contribution {$this->_aliases['civicrm_contribution']} {$this->_aclFrom}";
    $this->_from .= 
      " LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']} "
    . "ON {$this->_aliases['civicrm_contribution']}.contact_id = {$this->_aliases['civicrm_contact']}.id";
    $this->_from .= 
      " LEFT JOIN civicrm_donor_link donor_link_civireport ON {$this->_aliases['civicrm_contribution']}.
        id = donor_link_civireport.donation_entity_id AND donor_link_civireport.donation_entity = 
        'Contribution' AND donor_link_civireport.is_active = 1";
  }

  function orderBy() {
    $this->_orderBy = " ORDER BY {$this->_aliases['civicrm_contact']}.sort_name, "
    . "{$this->_aliases['civicrm_contribution']}.receive_date, donor_link_civireport.entity, "
    . "donor_link_civireport.entity_id";
  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact']);
    $this->_whereClauses[] = $this->_aliases['civicrm_contribution'].".is_test = 0";
    $sql = $this->buildQuery(TRUE);
    $rows = array();
    $this->buildRows($sql, $rows);
    //CRM_Core_Error::debug('rows', $rows);
    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  /*
   * Function to check if Campaigns are enabled and retrieve active ones
   */
  private function getCampaigns() {
    $config = CRM_Core_Config::singleton();
    $this->_campaignEnabled = in_array("CiviCampaign", $config->enableComponents);
    if ($this->_campaignEnabled) {
      $getCampaigns = CRM_Campaign_BAO_Campaign::getPermissionedCampaigns(NULL, NULL, TRUE, FALSE, TRUE);
      $this->activeCampaigns = $getCampaigns['campaigns'];
      asort($this->activeCampaigns);
      CRM_Core_Error::debug("actives", $this->activeCampaigns);
    }
  }
  /*
   * Function to add columns to report
   */
  private function setReportColumns() {
    $this->_financialTypes = CRM_Contribute_PseudoConstant::financialType();
    $this->_contributionStatusIds = CRM_Contribute_PseudoConstant::contributionStatus();
    $this->_columns = array(
      'civicrm_contact' => array(
        'dao'     =>  'CRM_Contact_DAO_Contact',
        'fields'  =>  array(
          'sort_name' => array('title' => ts('Donor Name'), 'required' => true),
          'contact_type' => array('title' => ts('Contact Type')),
          'contact_sub_type' => array('title' => ts('Contact Subtype'))
          ),
        'filters' =>  array(
          'sort_name' => array('title' => ts('Donor Name'), 'operator' => 'like'),
          'id' => array('title' => ts('Contact ID'), 'no_display' => true, 'type' => CRM_Utils_Type::T_INT),
        ),
      ),
      'civicrm_contribution' => array(
        'dao' => 'CRM_Contribute_DAO_Contribution',
        'fields' => array(
          'financial_type_id' => array('title' => ts('Financial Type'), 'required' => true),
          'total_amount' => array('title' => ts('Amount'), 'required' => true),
          'receive_date' => array('title' => ts('Receive Date')),
          'contribution_status_id' => array('title' => ts('Contribution Status'))
        ),
        'filters' => array(
          'financial_type_id' => array(
            'title' => ts('Financial Type'),
            'type' => CRM_Utils_Type::T_INT, 
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->_financialTypes,
          ),
          'contribution_status_id' => array(
            'title' => ts('Contribution Status'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->_contributionStatusIds,
          ),
          'total_amount' => array('title' => ts('Contribution Amount')),
          'receive_date' => array('operatorType' => CRM_Report_Form::OP_DATE),
        )
      ),
    );
    if ($this->_campaignEnabled) {
      $this->_columns['civicrm_contribution']['fields']['campaign_id'] = array('title' => ts('Campaign'));
      $this->_columns['civicrm_contribution']['filters']['campaign_id'] = array(
        'title' => ts('Campaign'),
        'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'options' => $this->activeCampaigns);
    }
  }
  /*
   * include function whereClause to correct error with 0 values in list
   */
  function whereClause(&$field, $op,
    $value, $min, $max
  ) {

    $type = CRM_Utils_Type::typeToString(CRM_Utils_Array::value('type', $field));
    $clause = NULL;

    switch ($op) {
      case 'bw':
      case 'nbw':
        if (($min !== NULL && strlen($min) > 0) ||
          ($max !== NULL && strlen($max) > 0)
        ) {
          $min     = CRM_Utils_Type::escape($min, $type);
          $max     = CRM_Utils_Type::escape($max, $type);
          $clauses = array();
          if ($min) {
            if ($op == 'bw') {
              $clauses[] = "( {$field['dbAlias']} >= $min )";
            }
            else {
              $clauses[] = "( {$field['dbAlias']} < $min )";
            }
          }
          if ($max) {
            if ($op == 'bw') {
              $clauses[] = "( {$field['dbAlias']} <= $max )";
            }
            else {
              $clauses[] = "( {$field['dbAlias']} > $max )";
            }
          }

          if (!empty($clauses)) {
            if ($op == 'bw') {
              $clause = implode(' AND ', $clauses);
            }
            else {
              $clause = implode(' OR ', $clauses);
            }
          }
        }
        break;

      case 'has':
      case 'nhas':
        if ($value !== NULL && strlen($value) > 0) {
          $value = CRM_Utils_Type::escape($value, $type);
          if (strpos($value, '%') === FALSE) {
            $value = "'%{$value}%'";
          }
          else {
            $value = "'{$value}'";
          }
          $sqlOP = $this->getSQLOperator($op);
          $clause = "( {$field['dbAlias']} $sqlOP $value )";
        }
        break;

      case 'in':
      case 'notin':
        if ($value !== NULL && is_array($value) && count($value) > 0) {
          $sqlOP = $this->getSQLOperator($op);
          if (CRM_Utils_Array::value('type', $field) == CRM_Utils_Type::T_STRING) {
            //cycle through selections and esacape values
            foreach ($value as $key => $selection) {
              $value[$key] = CRM_Utils_Type::escape($selection, $type);
            }
            $clause = "( {$field['dbAlias']} $sqlOP ( '" . implode("' , '", $value) . "') )";
          }
          else {
            // for numerical values
            $clause = "{$field['dbAlias']} $sqlOP (" . implode(', ', $value) . ")";
          }
          if ($op == 'notin') {
            $clause = "( " . $clause . " OR {$field['dbAlias']} IS NULL )";
          }
          else {
            $clause = "( " . $clause . " )";
          }
        }
        break;

      case 'mhas':
        // mhas == multiple has
        if ($value !== NULL && count($value) > 0) {
          $sqlOP = $this->getSQLOperator($op);
          $clause = "{$field['dbAlias']} REGEXP '[[:<:]]" . implode('|', $value) . "[[:>:]]'";
        }
        break;

      case 'sw':
      case 'ew':
        if ($value !== NULL && strlen($value) > 0) {
          $value = CRM_Utils_Type::escape($value, $type);
          if (strpos($value, '%') === FALSE) {
            if ($op == 'sw') {
              $value = "'{$value}%'";
            }
            else {
              $value = "'%{$value}'";
            }
          }
          else {
            $value = "'{$value}'";
          }
          $sqlOP = $this->getSQLOperator($op);
          $clause = "( {$field['dbAlias']} $sqlOP $value )";
        }
        break;

      case 'nll':
      case 'nnll':
        $sqlOP = $this->getSQLOperator($op);
        $clause = "( {$field['dbAlias']} $sqlOP )";
        break;

      default:
        if ($value !== NULL && strlen($value) > 0) {
          if (isset($field['clause'])) {
            // FIXME: we not doing escape here. Better solution is to use two
            // different types - data-type and filter-type
            $clause = $field['clause'];
          }
          else {
            /*
             * hack : if field in array fixValues and value is 0, no clause
             */
            $fixValues = array();
            if (in_array($field['title'], $fixValues) && $value != 0) {
              $value = CRM_Utils_Type::escape($value, $type);
              $sqlOP = $this->getSQLOperator($op);
              if ($field['type'] == CRM_Utils_Type::T_STRING) {
                $value = "'{$value}'";
              }
              $clause = "( {$field['dbAlias']} $sqlOP $value )";
            }
          }
        }
        break;
    }

    if (CRM_Utils_Array::value('group', $field) && $clause) {
      $clause = $this->whereGroupClause($field, $value, $op);
    }
    elseif (CRM_Utils_Array::value('tag', $field) && $clause) {
      // not using left join in query because if any contact
      // belongs to more than one tag, results duplicate
      // entries.
      $clause = $this->whereTagClause($field, $value, $op);
    }

    return $clause;
  }
  /*
   * specific buildQuery to add selects for donor links
   */
  function buildQuery($applyLimit = TRUE) {
    $this->select();
    $this->from();
    $this->customDataFrom();
    $this->where();
    $this->groupBy();
    $this->orderBy();
    $this->addDonorLinkClauses();

    // order_by columns not selected for display need to be included in SELECT
    $unselectedSectionColumns = $this->unselectedSectionColumns();
    foreach ($unselectedSectionColumns as $alias => $section) {
      $this->_select .= ", {$section['dbAlias']} as {$alias}";
    }

    if ($applyLimit && !CRM_Utils_Array::value('charts', $this->_params)) {
      $this->limit();
    }
    CRM_Utils_Hook::alterReportVar('sql', $this, $this);

    $sql = "{$this->_select} {$this->_from} {$this->_where} {$this->_groupBy} {$this->_having} {$this->_orderBy} {$this->_limit}";
    return $sql;
  }
  /*
   * Function to add select statements for donor links
   */
  private function addDonorLinkClauses() {
    $donorLinkSelect = 'contact_civireport.id as civicrm_contact_id, '
      . 'contribution_civireport.id as civicrm_contribution_id, donor_link_civireport.'
      . 'entity as civicrm_donor_link_entity, donor_link_civireport.entity_id as '
      . 'civicrm_donor_link_entity_id';
    if (empty($this->_select)) {
      $this->_select = $donorLinkSelect;
    } else {
      $this->_select .= ', '.$donorLinkSelect;
    }
  }
  /*
   * Function to add column headers 
   */
  function modifyColumnHeaders() {
    $this->_columnHeaders['civicrm_donor_link_entity'] = array('title' => ts('Linked'), 'type' => 2);
    $this->_columnHeaders['civicrm_donor_link_entity_id'] = array('title' => ts('Linked Title'), 'type' => 2);
    $this->_columnHeaders['civicrm_contact_id'] = array('title' => ts('Contact ID'), 'type' => 1, 'no_display' => true);
    $this->_columnHeaders['civicrm_contribution_id'] = array('title' => ts('Contribution ID'), 'type' => 1, 'no_display' => true);
  }
  /*
   * Function to modify the display of rows
   */
  function alterDisplay(&$rows) {
    $displayRows = array();
    $firstRow = true;
    // custom code to alter rows
    $entryFound = FALSE;
    $previousContribution = NULL;
    foreach ($rows as $rowNum => $row) {
      if ($row['civicrm_contribution_id'] == $previousContribution) {
        $row['civicrm_contact_sort_name'] = '';
        $row['civicrm_contribution_financial_type_id'] = '';
        $row['civicrm_contribution_total_amount'] = '';
        $row['civicrm_contribution_receive_date'] = '';
        $row['civicrm_contribution_contribution_status_id'] = '';
        $row['civicrm_contribution_campaign_id'] = '';
        $row['first_row'] = 0;
      } else {
        if (isset($row['civicrm_contribution_financial_type_id'])) {
          $row['civicrm_contribution_financial_type_id'] = $this->_financialTypes[$row['civicrm_contribution_financial_type_id']];
        }
        if ($row['civicrm_contribution_contribution_status_id']) {
          $row['civicrm_contribution_contribution_status_id'] = $this->_contributionStatusIds[$row['civicrm_contribution_contribution_status_id']];
        }
        if (isset($row['civicrm_contribution_campaign_id'])) {
          $row['civicrm_contribution_campaign_id'] = $this->activeCampaigns[$row['civicrm_contribution_campaign_id']];
        }
        if (!$firstRow) {
          $displayRows[] = array('first_row' => 1);
        } else {
          $firstRow = false;
        }
        $previousContribution = $row['civicrm_contribution_id'];
      }
      $displayRows[] = $row;
    }
    $rows = $displayRows;
  }
}
