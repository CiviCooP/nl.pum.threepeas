<?php

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_CaseDonorLink extends CRM_Core_Page {
  function run() {
    CRM_Core_Error::debug('request', $_REQUEST);
    exit();
  }
}
