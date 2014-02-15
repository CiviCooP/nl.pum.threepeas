<?php
/**
 * Class ThreepeasUtils for util function for complete extension
 * 
 * @client PUM (http://www.pum.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 15 Feb 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to PUM <http://www.pum.nl> and CiviCRM under the Academic Free License version 3.0.
 */
class CRM_Threepeas_ThreepeasUtils {
    /**
     * Function to check if mandatory fields are in params
     * 
     * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
     * @date 11 Feb 2014
     * @param array $mandatoryFields
     * @param array $params
     * @return boolean (TRUE if OK, FALSE if error
     * @access private
     * @static
     */
    private static function checkMandatoryFields($mandatoryFields, $params) {
        foreach ($mandatoryFields as $mandatoryField) {
            if (!isset($params[$mandatoryField])) {
                return FALSE;
            } else {
                if (empty($params[$mandatoryField])) {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }
}
