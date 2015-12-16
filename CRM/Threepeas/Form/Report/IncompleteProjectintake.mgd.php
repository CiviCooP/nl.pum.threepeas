<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Threepeas_Form_Report_IncompleteProjectintake',
    'entity' => 'ReportTemplate',
    'params' => 
    array (
      'version' => 3,
      'label' => 'Incomplete PUM Projectintakes',
      'description' => 'Signal list of incomplete projectintakes',
      'class_name' => 'CRM_Threepeas_Form_Report_IncompleteProjectintake',
      'report_url' => 'pum/incompleteprojectintake',
      'component' => '',
    ),
  ),
);