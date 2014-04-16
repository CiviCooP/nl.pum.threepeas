<?php

/**
 * Collection of upgrade steps
 */
class CRM_Threepeas_Upgrader extends CRM_Threepeas_Upgrader_Base {
  /**
   * implementation of function install()
   * 
   * create MySQL files when they do not exist yet:
   * - civicrm_campaign_parent
   * - civicrm_campaign_type_parent
   * 
   * @author Erik Hommel (erik.hommel@civicoop.org)
   * @date 29 Jan 2014
   * 
   */
  public function install() {       
    if (!CRM_Core_DAO::checkTableExists('civicrm_programme')) {
      $this->executeSqlFile('sql/createProgramme.sql');
    }

    if (!CRM_Core_DAO::checkTableExists('civicrm_programme_division')) {
      $this->executeSqlFile('sql/createProgrammeDivision.sql');
    }
        
    if (!CRM_Core_DAO::checkTableExists('civicrm_project')) {
      $this->executeSqlFile('sql/createProject.sql');
    }
  }
  /**
   * Upgrade 1001 - add customer_id to project table
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 26 Mar 2014
   */
  public function upgrade_1001() {
    $this->ctx->log->info('Applying update 1001 (add customer_id to civicrm_project table)');
    if (CRM_Core_DAO::checkTableExists('civicrm_project')) {
      if (!CRM_Core_DAO::checkFieldExists('civicrm_project', 'customer_id')) {
        CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_project ADD COLUMN customer_id INT(11) DEFAULT NULL");
      }
    }
    return TRUE;
  }
  /**
   * Upgrade 1002 - add country_id to project table
   * this is NOT the core civicrm country_id but the id of a contact of the 
   * sub_type country
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 16 Apr 2014
   */
  public function upgrade_1002() {
    $this->ctx->log->info('Applying update 1002 (add country)id to civicrm_project table)');
    if (CRM_Core_DAO::checkTableExists('civicrm_project')) {
      if (!CRM_Core_DAO::checkFieldExists('civicrm_project', 'country_id')) {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_project ADD COLUMN country_id INT(11) DEFAULT NULL');
      }
    }  
  }
}
