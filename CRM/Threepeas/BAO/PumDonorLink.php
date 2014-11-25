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
class CRM_Threepeas_BAO_PumDonorLink extends CRM_Threepeas_DAO_PumDonorLink {

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
  public static function getValues($params) {
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
   * @static
   */
  public static function add($params) {
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
  public static function disableByDonationEntityId($donationEntity, $donationEntityId) {
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
  public static function disableByEntityId($entity, $entityId) {
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
  public static function deleteByDonationEntityId($donationEntity, $donationEntityId) {
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
  public static function deleteByEntityId($entity, $entityId) {
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
  /**
   * Function to enable donor link by donation entity id
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 14 Jul 2014
   * @param string $donationEntity
   * @param int $donationEntityId
   */
  public static function enableByDonationEntityId($donationEntity, $donationEntityId) {
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
  public static function enableByEntityId($entity, $entityId) {
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
  public static function getContributionCount($donationEntity, $donationEntityId) {
    $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
    $pumDonorLink->entity = $donationEntity;
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
  public static function createViewRow($donorLink) {
    $donorLinkRow = array();
    if (!empty($donorLink) && isset($donorLink['donation_entity']) && isset($donorLink['donation_entity_id'])) {
      switch ($donorLink['donation_entity']) {
        case 'Contribution':
          $contribution = civicrm_api3('Contribution', 'Getsingle', array('id' => $donorLink['donation_entity_id']));
          $contactUrl = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$contribution['contact_id']);
          $donorLinkRow['contribution_id'] = $contribution['contribution_id'];
          $donorLinkRow['contact'] = '<a class="action-item" title="View contact" href="'.$contactUrl.'">'.$contribution['display_name'].'</a>';
          $donorLinkRow['amount'] = CRM_Utils_Money::format($contribution['total_amount']);
          $donor_link_config = CRM_Threepeas_DonorLinkConfig::singleton();
          $all_contribution_status = $donor_link_config->get_all_contribution_status();
          $donorLinkRow['status'] = $all_contribution_status[$contribution['contribution_status_id']];
          $donorLinkRow['date'] = date('d-M-Y', strtotime($contribution['receive_date']));
          $donorLinkRow['financial_type'] = $contribution['financial_type'];
          $donorLinkRow['is_fa_donor'] = self::set_display_tinyint($donorLink['is_fa_donor']);
          $viewContributionUrl = CRM_Utils_System::url('civicrm/contact/view/contribution', 'reset=1&id='
            .$contribution['contribution_id'].'&cid='.$contribution['contact_id'].'&action=view');
          $donorLinkRow['view_link'] = '<a class="action-item" title="View contribution" href="'.$viewContributionUrl.'">View contribution</a>';
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
  protected static function set_display_tinyint($tiny_int) {
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
  public static function get_donations($params) {
    $donations = self::getValues($params);
    foreach ($donations as $key => $value) {
      if (self::is_grant_donation($value) == TRUE) {
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
  public static function get_grant_donations($params) {
    $donations = self::getValues($params);
    foreach ($donations as $key => $value) {
      if (self::is_grant_donation($value) == FALSE) {
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
  protected static function is_grant_donation($value) {
    $donor_link_config = CRM_Threepeas_DonorLinkConfig::singleton();
    if ($value['donation_entity'] == 'Contribution') {
      $params = array(
        'id' => $value['donation_entity_id'],
        'return' => 'financial_type');
      $contribution_financial_type = civicrm_api3('Contribution', 'Getvalue', $params);
      if ($contribution_financial_type == $donor_link_config->get_grant_donation_financial_type()) {
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
   * @param int $case_type
   * @return type
   * @access protected
   * @static
   */
  protected static function set_contribution_list_params($context, $case_type) {
    $params = array('is_test' => 0, 'options' => array('limit' => 9999));
    $donor_link_config = CRM_Threepeas_DonorLinkConfig::singleton();
    if ($context == 'Case') {
      self::set_financial_type_param($case_type, $params);
    } else {
      $params['financial_type_id'] = $donor_link_config->get_donation_financial_type_id();
    } 
    return $params;
  }
  /**
   * Function to set the financial type param to get contributions for a case
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 19 Nov 2014
   * @param string $case_type
   * @param array $params
   * @access protected
   * @static
   */
  protected static function set_financial_type_param($case_type, &$params) {
    $donor_link_config = CRM_Threepeas_DonorLinkConfig::singleton();
    if (!empty($case_type)) {
      if ($case_type == 'Grant') {
        $params['financial_type_id'] = $donor_link_config->get_grant_donation_financial_type_id();        
      } else {
        $params['financial_type_id'] = $donor_link_config->get_donation_financial_type_id();                
      }
    }
  }
  /**
   * Function to retrieve active contributions for select list
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 19 Nov 2014
   * @param string $context
   * @param string $case_type
   * @access public
   * @static
   */
  public static function get_contributions_list($context, $case_type) {
    $option_contributions = array();
    $donor_link_config = CRM_Threepeas_DonorLinkConfig::singleton();
    $active_contribution_status = $donor_link_config->get_active_contribution_status();
    $params = self::set_contribution_list_params($context, $case_type);
    $contributions = civicrm_api3('Contribution', 'Get', $params);
    /*
     * add required contributions to option list
     */
    foreach ($contributions['values'] as $contribution) {
      if (isset($active_contribution_status[$contribution['contribution_status_id']])) {
        $option_text = $contribution['display_name'].' (type '.$contribution['financial_type'].')';
        $option_contributions[$contribution['contribution_id']] = $option_text;
      }
    }
    asort($option_contributions);
    return $option_contributions;
  }
}