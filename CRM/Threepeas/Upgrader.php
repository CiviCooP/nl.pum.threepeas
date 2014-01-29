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
        if (!CRM_Core_DAO::checkTableExists('civicrm_campaign_type_parent')) {
            $this->executeSqlFile('sql/createCampaignTypeParent.sql');
        }
        
        if (!CRM_Core_DAO::checkTableExists('civicrm_campaign_parent')) {
            $this->executeSqlFile('sql/createCampaignParent.sql');
        }
    }
}
