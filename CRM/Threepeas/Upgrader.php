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
    $this->executeSqlFile('sql/createProgramme.sql');
    $this->executeSqlFile('sql/createProject.sql');
    $this->executeSqlFile('sql/createCaseProject.sql');
    $this->executeSqlFile('sql/createDonorLink.sql');
    $this->executeSqlFile('sql/createContributionProject.sql');
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
   *              - rename contact_id_manager to manager_id in programme table
   * this is NOT the core civicrm country_id but the id of a contact of the 
   * sub_type country
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 16 Apr 2014
   */
  public function upgrade_1002() {
    $this->ctx->log->info('Applying update 1002 (add country)id to civicrm_project table and renaming manager_id in civicrm_programme table)');
    if (CRM_Core_DAO::checkTableExists('civicrm_project')) {
      if (!CRM_Core_DAO::checkFieldExists('civicrm_project', 'country_id')) {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_project ADD COLUMN country_id INT(11) DEFAULT NULL');
      }
      if (CRM_Core_DAO::checkFieldExists('civicrm_programme' , 'contact_id_manager')) {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_programme CHANGE contact_id_manager manager_id INT(11)');
      }
    }
    return TRUE;
  }
  /**
   * Upgrade 1003 - add table civicrm_case_project
   */
  public function upgrade_1003() {
    $this->ctx->log->info('Applying update 1003 (create civicrm_case_project table');
    if (!CRM_Core_DAO::checkTableExists('civicrm_case_project')) {
      $this->executeSqlFile('sql/createCaseProject.sql');
    }
    return TRUE;
  }
  /**
   * Upgrade 1004 - add fields is_active to civicrm_case_project
   */
  public function upgrade_1004() {
    $this->ctx->log->info('Applying update 1004 (add column is_active to table civicrm_case_project');
    if (CRM_Core_DAO::checkTableExists('civicrm_case_project')) {
      if (!CRM_Core_DAO::checkFieldExists('civicrm_case_project', 'is_active')) {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_case_project ADD COLUMN is_active TINYINT(4)');
      }
    }
    return TRUE;
  }
  /**
   * Upgrade 2000 - add sponsor link table
   */
  public function upgrade_2000() {
    $this->ctx->log->info('Applying update 2000 (create civicrm_donor_link and civicrm_contribution_number_projects table');
    $this->executeSqlFile('sql/createDonorLink.sql');
    $this->executeSqlFile('sql/createContributionProject.sql');
    return TRUE;
  }
  /**
   * Upgrade 2001 - remove programme budget division (replaced with donor link)
   */
  public function upgrade_2001() {
    $this->ctx->log->info('Applying update 2001 (drop civicrm_programme_division table');
    if (CRM_Core_DAO::checkTableExists('civicrm_programme_division')) {
      CRM_Core_DAO::executeQuery('DROP TABLE civicrm_programme_division');
    }
    return TRUE;    
  }
}
