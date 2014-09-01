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

    if (!CRM_Core_DAO::checkTableExists('civicrm_case_project')) {
      $this->executeSqlFile('sql/createCaseProject.sql');
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
    $this->ctx->log->info('Applying update 1004 (add is_active to civicrm_case_project table');
    if (CRM_Core_DAO::checkTableExists('civicrm_case_project')) {
      if (!CRM_Core_DAO::checkFieldExists('civicrm_case_project', 'is_active')) {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_case_project ADD COLUMN is_active TINYINT(4)');
      }
    }
    return TRUE;
  }
  /**
   * Upgrade 1005 - remove fields country_coordinator_id, sector_coordinator_id and project_officer
   * from table civicrm_project
   */
  public function upgrade_1005() {
    $this->ctx->log->info('Applying update 1005 (remove country_coordinator_id, '
      . 'sector_coordinator_id and project_officer_id from table civicrm_project');
    if (CRM_Core_DAO::checkTableExists('civicrm_project')) {
      if (CRM_Core_DAO::checkFieldExists('civicrm_project', 'country_coordinator_id')) {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_project DROP country_coordinator_id');
      }
      if (CRM_Core_DAO::checkFieldExists('civicrm_project', 'sector_coordinator_id')) {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_project DROP sector_coordinator_id');
      }
      if (CRM_Core_DAO::checkFieldExists('civicrm_project', 'project_officer_id')) {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_project DROP project_officer_id');
      }
    }
    return TRUE;
  }
}
