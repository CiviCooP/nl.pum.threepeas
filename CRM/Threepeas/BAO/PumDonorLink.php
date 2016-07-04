<?php
/**
 * BAO PumDonorLink for dealing with donor_link (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 14 Jul 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the AGPL-3.0
 */
class CRM_Threepeas_BAO_PumDonorLink extends CRM_Threepeas_DAO_PumDonorLink
{

  /**
   * Function to get values
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 14 Jul 2014
   * @param array $params name/value pairs with field names/values
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params)
  {
    $result = array();
    $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $paramKey => $paramValue) {
        if (isset($fields[$paramKey])) {
          $pumDonorLink->$paramKey = $paramValue;
        }
      }
    }
    $pumDonorLink->find();
    while ($pumDonorLink->fetch()) {
      $row = array();
      self::storeValues($pumDonorLink, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Function to add or update donor link
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 14 Jul 2014
   * @param array $params
   * @return array $result
   * @access public
   * @throws Exception when params empty
   * @static
   */
  public static function add($params)
  {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a Donor Link');
    }
    $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
    $fields = self::fields();
    foreach ($params as $paramKey => $paramValue) {
      if (isset($fields[$paramKey])) {
        $pumDonorLink->$paramKey = $paramValue;
      }
    }
    $pumDonorLink->save();
    self::storeValues($pumDonorLink, $result);
    return $result;
  }

  /**
   * Function to disable donor link by donation entity id
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 14 Jul 2014
   * @param string $donationEntity
   * @param int $donationEntityId
   */
  public static function disableByDonationEntityId($donationEntity, $donationEntityId)
  {
    if (!empty($donationEntity) && !empty($donationEntityId)) {
      $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
      $pumDonorLink->donation_entity = $donationEntity;
      $pumDonorLink->donation_entity_id = $donationEntityId;
      $pumDonorLink->find();
      while ($pumDonorLink->fetch()) {
        self::add(array('id' => $pumDonorLink->id, 'is_active' => 0));
      }
    }
  }

  /**
   * Function to disable donor link by entity_id
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 14 Jul 2014
   * @param string $entity
   * @param int $entityId
   */
  public static function disableByEntityId($entity, $entityId)
  {
    if (!empty($entity) && !empty($entityId)) {
      $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
      $pumDonorLink->entity = $entity;
      $pumDonorLink->entity_id = $entityId;
      $pumDonorLink->find();
      while ($pumDonorLink->fetch()) {
        self::add(array('id' => $pumDonorLink->id, 'is_active' => 0));
      }
    }
  }

  /**
   * Function to delete donor link by donation entity id
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 14 Jul 2014
   * @param string $donationEntity
   * @param int $donationEntityId
   */
  public static function deleteByDonationEntityId($donationEntity, $donationEntityId)
  {
    if (!empty($donationEntity) && !empty($donationEntityId)) {
      $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
      $pumDonorLink->donation_entity = $donationEntity;
      $pumDonorLink->donation_entity_id = $donationEntityId;
      $pumDonorLink->find();
      while ($pumDonorLink->fetch()) {
        $pumDonorLink->delete();
      }
    }
  }

  /**
   * Function to delete donor link by entity id
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 14 Jul 2014
   * @param string $entity
   * @param int $entityId
   */
  public static function deleteByEntityId($entity, $entityId)
  {
    if (!empty($entity) && !empty($entityId)) {
      $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
      $pumDonorLink->entity = $entity;
      $pumDonorLink->entity_id = $entityId;
      $pumDonorLink->find();
      while ($pumDonorLink->fetch()) {
        $pumDonorLink->delete();
      }
    }
  }

  /**
   * Function to delete donor link by entity id for only applicable contributions (issue 3266)
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 14 Jul 2014
   * @param string $entity
   * @param int $entityId
   */
  public static function deleteApplicableByEntityId($entity, $entityId) {
    if (!empty($entity) && !empty($entityId)) {
      $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
      $pumDonorLink->entity = $entity;
      $pumDonorLink->entity_id = $entityId;
      $pumDonorLink->find();
      while ($pumDonorLink->fetch()) {
        if (self::contributionIsApplicable($pumDonorLink->donation_entity_id) == TRUE) {
          $pumDonorLink->delete();
        }
      }
    }
  }

  /**
   * /**
   * Function to enable donor link by donation entity id
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 14 Jul 2014
   * @param string $donationEntity
   * @param int $donationEntityId
   */
  public static function enableByDonationEntityId($donationEntity, $donationEntityId)
  {
    if (!empty($donationEntity) && !empty($donationEntityId)) {
      $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
      $pumDonorLink->donation_entity = $donationEntity;
      $pumDonorLink->donation_entity_id = $donationEntityId;
      $pumDonorLink->find();
      while ($pumDonorLink->fetch()) {
        self::add(array('id' => $pumDonorLink->id, 'is_active' => 1));
      }
    }
  }

  /**
   * Function to enable donor link by entity_id
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 14 Jul 2014
   * @param string $entity
   * @param int $entityId
   */
  public static function enableByEntityId($entity, $entityId)
  {
    if (!empty($entity) && !empty($entityId)) {
      $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
      $pumDonorLink->entity = $entity;
      $pumDonorLink->entity_id = $entityId;
      $pumDonorLink->find();
      while ($pumDonorLink->fetch()) {
        self::add(array('id' => $pumDonorLink->id, 'is_active' => 1));
      }
    }
  }

  /**
   * Function to return a count of links
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 20 Aug 2014
   * @params string $donationEntity
   * @params int $donationEntityId
   * @return int $count
   * @access public
   * @static
   */
  public static function getContributionCount($donationEntity, $donationEntityId)
  {
    $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
    $pumDonorLink->donation_entity = $donationEntity;
    $pumDonorLink->donation_entity_id = $donationEntityId;
    $pumDonorLink->is_active = 1;
    return $pumDonorLink->count();
  }

  /**
   * Function to create View Row for Donor Link (to display data of donation link)
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 27 Aug 2014
   * @param array $donorLink
   * @return array $donorLinkRow
   */
  public static function createViewRow($donorLink)
  {
    $donorLinkRow = array();
    if (!empty($donorLink) && isset($donorLink['donation_entity']) && isset($donorLink['donation_entity_id'])) {
      switch ($donorLink['donation_entity']) {
        case 'Contribution':
          $contribution = civicrm_api3('Contribution', 'Getsingle', array('id' => $donorLink['donation_entity_id']));
          $contactUrl = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $contribution['contact_id']);
          $donorLinkRow['contribution_id'] = $contribution['contribution_id'];
          $donorLinkRow['contact'] = '<a class="action-item" title="View contact" href="' . $contactUrl . '">' . $contribution['display_name'] . '</a>';
          $donorLinkRow['amount'] = CRM_Utils_Money::format($contribution['total_amount']);
          $donorLinkConfig = CRM_Threepeas_DonorLinkConfig::singleton();
          $all_contribution_status = $donorLinkConfig->getAllContributionStatus();
          $donorLinkRow['status'] = $all_contribution_status[$contribution['contribution_status_id']];
          $donorLinkRow['date'] = date('d-M-Y', strtotime($contribution['receive_date']));
          $donorLinkRow['financial_type'] = $contribution['financial_type'];
          $donorLinkRow['is_fa_donor'] = self::setDisplayTinyint($donorLink['is_fa_donor']);
          $viewContributionUrl = CRM_Utils_System::url('civicrm/contact/view/contribution', 'reset=1&id='
            . $contribution['contribution_id'] . '&cid=' . $contribution['contact_id'] . '&action=view');
          $donorLinkRow['view_link'] = '<a class="action-item" title="View contribution" href="'
            . $viewContributionUrl . '">View contribution</a>';
          break;
      }
    }
    return $donorLinkRow;
  }

  /**
   * Function to return tinyint value 1/0 as Y/N
   *
   * @param int $tiny_int
   * @return string
   * @access protected
   * @static
   */
  protected static function setDisplayTinyint($tiny_int)
  {
    if ($tiny_int == 1) {
      return 'Y';
    } else {
      return 'N';
    }
  }

  /**
   * Function to get donations
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 18 Nov 2014
   * @param array $params
   * @return array $donations
   * @access public
   * @static
   */
  public static function getDonations($params)
  {
    $donations = self::getValues($params);
    foreach ($donations as $key => $value) {
      if (self::isGrantDonation($value) == TRUE) {
        unset($donations[$key]);
      }
    }
    return $donations;
  }

  /**
   * Function to get grant donations
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 18 Nov 2014
   * @param array $params
   * @return array $donations
   * @access public
   * @static
   */
  public static function getGrantDonations($params)
  {
    $donations = self::getValues($params);
    foreach ($donations as $key => $value) {
      if (self::isGrantDonation($value) == FALSE) {
        unset($donations[$key]);
      }
    }
    return $donations;
  }

  /**
   * Function to check if a donation is a grant donation
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 18 Nov 2014
   * @param array $value
   * @return boolean
   * @access protected
   * @static
   */
  protected static function isGrantDonation($value)
  {
    $donorLinkConfig = CRM_Threepeas_DonorLinkConfig::singleton();
    if ($value['donation_entity'] == 'Contribution') {
      $params = array(
        'id' => $value['donation_entity_id'],
        'return' => 'financial_type');
      $contributionFinancialType = civicrm_api3('Contribution', 'Getvalue', $params);
      if ($contributionFinancialType == $donorLinkConfig->getGrantDonationFinancialType()) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Function to set params for the contributions list
   * - if not case then show all contributions of type 'Donation'
   * - if case and case type = Grant show all contributions of type 'Grant Donation'
   * - if case and case type != Grant and not empty, show all contributions of type 'Donation'
   * - if case and case type empty, show all contribution of type 'Donation' or 'Grant Donation'
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 19 Nov 2014
   * @param string $context
   * @param int $caseType
   * @return type
   * @access protected
   * @static
   */
  protected static function setContributionListParams($context, $caseType)
  {
    $params = array('is_test' => 0, 'options' => array('limit' => 9999));
    $donorLinkConfig = CRM_Threepeas_DonorLinkConfig::singleton();
    if ($context == 'Case') {
      self::setFinancialTypeParam($caseType, $params);
    } else {
      $params['financial_type_id'] = $donorLinkConfig->getDonationFinancialTypeId();
    }
    return $params;
  }

  /**
   * Function to set the financial type param to get contributions for a case
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 19 Nov 2014
   * @param string $caseType
   * @param array $params
   * @access protected
   * @static
   */
  protected static function setFinancialTypeParam($caseType, &$params)
  {
    $donorLinkConfig = CRM_Threepeas_DonorLinkConfig::singleton();
    if (!empty($caseType)) {
      if ($caseType == 'Grant') {
        $params['financial_type_id'] = $donorLinkConfig->getGrantDonationFinancialTypeId();
      } else {
        $params['financial_type_id'] = $donorLinkConfig->getDonationFinancialTypeId();
      }
    }
  }

  /**
   * Function to retrieve active contributions for select list
   *
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 19 Nov 2014
   * @param string $context
   * @param string $caseType
   * @return array $optionContributions
   * @access public
   * @static
   */
  public static function getContributionsList($context, $caseType)
  {
    $optionContributions = array();
    $donorLinkConfig = CRM_Threepeas_DonorLinkConfig::singleton();
    $activeContributionStatus = $donorLinkConfig->getActiveContributionStatus();
    $params = self::setContributionListParams($context, $caseType);
    $contributions = civicrm_api3('Contribution', 'Get', $params);
    /*
     * add required contributions to option list
     */
    foreach ($contributions['values'] as $contribution) {
      if (self::contributionIsApplicable($contribution['id'])) {
        if (isset($activeContributionStatus[$contribution['contribution_status_id']])) {
          $optionText = $contribution['display_name'] . ' (type ' . $contribution['financial_type'] . ')';
          $optionContributions[$contribution['contribution_id']] = $optionText;
        }
      }
    }
    asort($optionContributions);
    return $optionContributions;
  }

  /**
   * Function to check if the selected contribution is applicable
   *
   * @param $contributionId
   * @return bool
   * @access public
   * @static
   */
  public static function contributionIsApplicable($contributionId)
  {
    if (empty($contributionId)) {
      return FALSE;
    }
    $extensionConfig = CRM_Threepeas_DonorLinkConfig::singleton();
    $applicableColumn = $extensionConfig->getApplicableCustomFieldColumn();
    $query = 'SELECT ' . $applicableColumn . ' FROM ' . $extensionConfig->getApplicableCustomGroupTable() . ' WHERE entity_id= %1';
    $params = array(1 => array($contributionId, 'Positive'));
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    if ($dao->fetch()) {
      if ($dao->$applicableColumn == 1) {
        return TRUE;
      } else {
        return FALSE;
      }
    } else {
      return FALSE;
    }
  }

  /**
   * Method to get case donor id
   *
   * @param int $caseId
   * @return int|bool
   * @access public
   * @static
   */
  public static function getCaseDonor($caseId)
  {
    if (empty($caseId)) {
      return FALSE;
    }
    $donationLink = new CRM_Threepeas_BAO_PumDonorLink();
    $donationLink->donation_entity = 'Contribution';
    $donationLink->entity = 'Case';
    $donationLink->entity_id = $caseId;
    $donationLink->find(true);
    if (empty($donationLink->donation_entity_id)) {
      return FALSE;
    }
    $contributionParams = array(
      'id' => $donationLink->donation_entity_id,
      'return' => 'contact_id');
    try {
      $donorId = civicrm_api3('Contribution', 'Getvalue', $contributionParams);
      return $donorId;
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to get case donor id
   *
   * @param int $caseId
   * @return int|bool
   * @access public
   * @static
   */
  public static function getCaseFADonor($caseId)
  {
    if (empty($caseId)) {
      return FALSE;
    }
    $donationLink = new CRM_Threepeas_BAO_PumDonorLink();
    $donationLink->donation_entity = 'Contribution';
    $donationLink->entity = 'Case';
    $donationLink->is_fa_donor = 1;
    $donationLink->entity_id = $caseId;
    $donationLink->find(true);
    if (empty($donationLink->donation_entity_id)) {
      return FALSE;
    }
    $contributionParams = array(
      'id' => $donationLink->donation_entity_id,
      'return' => 'contact_id');
    try {
      $donorId = civicrm_api3('Contribution', 'Getvalue', $contributionParams);
      return $donorId;
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to add non-applicable linked to case to form
   * (see Redmine issue 3266 http://redmine.pum.nl/issues/3266 Erik Hommel (CiviCooP), 12 Apr 2016)
   *
   * @param $caseId
   * @param $form
   * @param $defaults
   */
  public static function showNonApplicableCaseDonations($caseId, &$form, &$defaults)
  {
    $config = CRM_Threepeas_DonorLinkConfig::singleton();
    $nonApplicableDonations = array();
    $caseTypeId = civicrm_api3('Case', 'Getvalue', array('id' => $caseId, 'return' => 'case_type_id'));
    if ($caseTypeId == $config->getGrantCaseTypeId()) {
      $donations = self::getGrantDonations(array('entity' => 'Case', 'entity_id' => $caseId, 'is_active' => 1));
    } else {
      $donations = self::getDonations(array('entity' => 'Case', 'entity_id' => $caseId, 'is_active' => 1));
    }
    foreach ($donations as $donationId => $donation) {
      if ($donation['donation_entity'] == "Contribution" &&
        self::contributionIsApplicable($donation['donation_entity_id']) == FALSE) {
        $contribution = civicrm_api3('Contribution', 'Getsingle', array('id' => $donation['donation_entity_id']));
        if ($donation['is_fa_donor'] == 1) {
          $defaults['not_applicable_fa_donor'] = $contribution['display_name'];
        }
        $nonApplicableDonations[] = $contribution['display_name'];
      }
    }
    if (isset($defaults['not_applicable_fa_donor'])) {
      $editFaElement = $form->getElement('fa_donor');
      $editFaElement->freeze();
    }
    $form->addElement('text', 'not_applicable_fa_donor', ts('Assigned to FA Donor'));
    $form->addElement('text', 'not_applicable_donors', ts('Assigned to Donor(s)'));
    $defaults['not_applicable_donors'] = implode('; ', $nonApplicableDonations);
    $naElement = $form->getElement('not_applicable_donors');
    $naElement->freeze();
    $naFaElement = $form->getElement('not_applicable_fa_donor');
    $naFaElement->freeze();
  }
  /**
   * Method to add non-applicable linked to project or programme to form
   * (see Redmine issue 3266 http://redmine.pum.nl/issues/3266 Erik Hommel (CiviCooP), 12 Apr 2016)
   *
   * @param $entity
   * @param $form
   * @param $defaults
   */
  public static function showNonApplicableDonations($entity, &$form, &$defaults) {
    $nonApplicableDonations = array();
    if (isset($form->_id)) {
      $donations = self::getDonations(array('entity' => $entity, 'entity_id' => $form->_id, 'is_active' => 1));
      foreach ($donations as $donationId => $donation) {
        if ($donation['donation_entity'] == "Contribution" &&
          self::contributionIsApplicable($donation['donation_entity_id']) == FALSE
        ) {
          $contribution = civicrm_api3('Contribution', 'Getsingle', array('id' => $donation['donation_entity_id']));
          if ($donation['is_fa_donor'] == 1) {
            $defaults['not_applicable_fa_donor'] = $contribution['display_name'];
          }
          $nonApplicableDonations[] = $contribution['display_name'];
        }
      }
    }
    if (isset($defaults['not_applicable_fa_donor']) && $form->elementExists('fa_donor')) {
      $editFaElement = $form->getElement('fa_donor');
      $editFaElement->freeze();
    }
    $form->addElement('text', 'not_applicable_fa_donor', ts('Assigned to FA Donor'));
    $form->addElement('text', 'not_applicable_donors', ts('Assigned to Donor(s)'));
    $defaults['not_applicable_donors'] = implode('; ', $nonApplicableDonations);
    $naElement = $form->getElement('not_applicable_donors');
    $naElement->freeze();
    $naFaElement = $form->getElement('not_applicable_fa_donor');
    $naFaElement->freeze();
  }
}