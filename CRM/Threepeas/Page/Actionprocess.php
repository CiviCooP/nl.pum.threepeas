<?php

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Actionprocess extends CRM_Core_Page {
    protected $_action = "";
    protected $_entity = "";
    protected $_redirectUrl = "";
    function run() {
        CRM_Core_Error::debug("request", $_REQUEST);
        CRM_Core_Error::debug("post", $_POST);
        $this->_redirectUrl = CRM_Utils_System::url('civicrm/programlist', null, true);
        CRM_Utils_System::redirect($this->_redirectUrl);
        //parent::run();
    }
}
