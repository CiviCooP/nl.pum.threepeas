<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:Relationship.CheckProject',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'PUM Update project relationships',
      'description' => 'PUM Update all project relationships (anamon, country coordinator, project officer, sector coordinator)',
      'run_frequency' => 'Daily',
      'api_entity' => 'Relationship',
      'api_action' => 'CheckProject',
      'parameters' => '',
    ),
  ),
);