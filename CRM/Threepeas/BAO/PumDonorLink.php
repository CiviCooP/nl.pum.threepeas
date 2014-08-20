<?php
/**
 * BAO PumDonorLink for dealing with donor_link (PUM)
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 14 Jul 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
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
  static function getContributionCount($donationEntity, $donationEntityId) {
    $pumDonorLink = new CRM_Threepeas_BAO_PumDonorLink();
    $pumDonorLink->entity = $donationEntity;
    $pumDonorLink->donation_entity_id = $donationEntityId;
    $pumDonorLink->is_active = 1;
    $count = $pumDonorLink->count();
    return $pumDonorLink->count();
  }
}