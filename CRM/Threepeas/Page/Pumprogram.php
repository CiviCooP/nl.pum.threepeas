<?php

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_Pumprogram extends CRM_Core_Page {
    function run() {
        CRM_Utils_System::setTitle(ts('Program'));
        $this->assign('action', "add");
        parent::run();
    }
}
