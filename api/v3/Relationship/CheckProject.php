<?php
set_time_limit(0);
/**
 * Relationship.CheckProject API
 * PUM issue 3287 daily check to process relationships for projects if required based
 * on start date or end date
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 2 June 2016
 */

function civicrm_api3_relationship_checkproject($params) {
  $returnValues = array();

  $queue = CRM_Queue_Service::singleton()->create(array(
    'type' => 'Sql',
    'name' => 'nl.pum.threepeas',
    'reset' => true, //Flush queue upon creation
  ));
  $task = new CRM_Queue_Task(
    array('CheckProject', '_run_check_project_job'), //call back method
    array()
  );

  //now add this task to the queue
  $queue->createItem($task);

  //If the runner has an onEndUrl, then it will not return, so redirect will take place in onEnd function
  $runner = new CRM_Queue_Runner(array(
    'title' => ts('CheckProject Job: queue runner'), //title fo the queue
    'queue' => $queue, //the queue object
    'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE,
    'onEnd' => array('CheckProject','onEnd'),
    //'onEndUrl' => CRM_Utils_System::url('civicrm', 'reset=1'),
  ));
  $queueResult = $runner->runAll();

  $params['version'] = 3;
  $params['sequential'] = 1;
  $returnValues['is_error'] = 0;
  $returnValues['values'] = '';
  $returnValues['queueResult'] = $queueResult;

  return civicrm_api3_create_success($returnValues, $params, 'Relationship', 'CheckProject');
}

class CheckProject {
  public static function _run_check_project_job(CRM_Queue_TaskContext $ctx) {
    $msg = 'CheckProject Job: Started';
    CRM_Core_Session::setStatus($msg, 'Queue', 'success');
    CRM_Core_Error::debug_log_message($msg);

    $validRelations = _get_valid_relations();
    $countryQuery = 'SELECT DISTINCT(country_id) AS contact_id FROM civicrm_project WHERE country_id IS NOT NULL AND is_active = 1 AND (end_date IS NULL OR end_date > CURDATE())';
    $country = CRM_Core_DAO::executeQuery($countryQuery);
    $countries = array();
    while ($country->fetch()) {
      $countries[] = $country->contact_id;
    }

    _process_contact($countries, $validRelations, 'country');

    $customerQuery = "SELECT DISTINCT(p.customer_id) AS contact_id FROM civicrm_project p
                      LEFT JOIN civicrm_case_project cp ON cp.project_id = p.id
                      LEFT JOIN civicrm_relationship rel ON rel.case_id = cp.case_id
                      LEFT JOIN civicrm_case c ON c.id = rel.case_id
                      WHERE customer_id IS NOT NULL
                      AND p.is_active = 1 AND (p.end_date IS NULL OR p.end_date > CURDATE())
                      AND rel.is_active = 1 AND (rel.end_date IS NULL OR ((rel.end_date >= curdate() - INTERVAL DAYOFWEEK(curdate())+6 DAY) AND (rel.end_date < curdate() - INTERVAL DAYOFWEEK(curdate())-1 DAY)))
                      AND c.status_id NOT IN (SELECT ov.value FROM civicrm_option_value ov WHERE ov.option_group_id = (SELECT id FROM civicrm_option_group og WHERE og.name = 'case_status') AND (ov.label = 'Completed' OR ov.label = 'Cancelled' OR ov.label = 'Rejected'))
                      ORDER BY p.customer_id";
    $customer = CRM_Core_DAO::executeQuery($customerQuery);
    $customers = array();
    while ($customer->fetch()) {
      $customers[] = $customer->contact_id;
    }

    _process_contact($customers, $validRelations, 'customer');

    return TRUE;
  }

  /**
   * Handle the final step of the queue
   */
  public static function onEnd(CRM_Queue_TaskContext $ctx) {
    //set a status message for the user
    $msg = 'CheckProject Job: Finished';
    CRM_Core_Session::setStatus($msg, 'Queue', 'success');
    CRM_Core_Error::debug_log_message($msg);

    $result = array();
    $result['is_error'] = 0;
    $result['numberOfItems'] = 0;
    $result['is_continue'] = 0;
    if (!empty($ctx->onEndUrl)) {
      $result['redirect_url'] = $ctx->onEndUrl;
    }

    $ctx->queue->deleteQueue();

    return $result;
  }
}

/**
 * Function to process each contact
 *
 * @param $contact
 * @param $validRelations
 */
function _process_contact($contacts, $validRelations, $project_type) {
  if ($project_type == 'country') {
    $typeColumn = 'country_id';
  } else {
    $typeColumn = 'customer_id';
  }

  $column_values = array();

  foreach ($validRelations as $validRelationshipTypeId => $columnName) {
    foreach($contacts as $cid){
      if (!empty($cid)){
        $contactRelationId = _get_contact_relation($cid, $columnName, $project_type);

        if (!empty($contactRelationId)) {
          $column_values[$cid][$columnName] = $contactRelationId;
        } else {
          if (empty($column_values[$cid][$columnName])){
            $column_values[$cid][$columnName] = NULL;
          }
        }
      }
    }
  }

  foreach($column_values as $client_id => $contacts) {
    $vals = '';
    $value_insert = '';
    foreach($contacts as $column => $value) {
      if(!empty($value)) {
        $value_insert = $value;
      } else {
        $value_insert = '';
      }
      $vals .= $column."='".$value_insert."',";
    }
    $vals = substr($vals,0, -1); //remove last comma

    $query = "UPDATE civicrm_project SET ".$vals. " WHERE ".$typeColumn." = %1";

    $params = array(1 => array($client_id, 'Integer'));
    CRM_Core_DAO::executeQuery($query, $params);

  }
}

/**
 * Function to get the relevant contact id of the relation
 *
 * @param $contact
 * @param $columnName
 * @return integer|bool
 */
function _get_contact_relation($contact_id, $columnName, $project_type) {
  $foundContactId = FALSE;
  switch ($columnName) {
    case 'anamon_id':
      if ($project_type == 'customer') {
        $foundContactId = CRM_Threepeas_BAO_PumCaseRelation::getAnamonId($contact_id);
      }
      break;
    case 'country_coordinator_id':
      $foundContactId = CRM_Threepeas_BAO_PumCaseRelation::getCountryCoordinatorId($contact_id);
      break;
    case 'project_officer_id':
      $foundContactId = CRM_Threepeas_BAO_PumCaseRelation::getProjectOfficerId($contact_id);
      break;
    case 'sector_coordinator_id':
      if ($project_type == 'customer') {
        $foundContactId = CRM_Threepeas_BAO_PumCaseRelation::getSectorCoordinatorId($contact_id);
      }
      break;
  }
  return $foundContactId;
}

/**
 * Function to get the required valid relationship types with their column names
 *
 * @throws Exception when error from API
 * @return array
 */
function _get_valid_relations() {
  $result = array();
  $names = array(
    'Anamon' => 'anamon_id',
    'Country Coordinator is' => 'country_coordinator_id',
    'Project Officer for' => 'project_officer_id',
    'Sector Coordinator' => 'sector_coordinator_id');
  foreach ($names as $name => $column) {
    try {
      $relTypeId = civicrm_api3('RelationshipType', 'Getvalue', array('name_a_b' => $name, 'return' => 'id'));
      $result[$relTypeId] = $column;
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a single relationship type with the name '.$name.' in '.__METHOD__
        .', contact your system administrator. Error from API RelationshipType Getvalue: '.$ex->getMessage());
    }
  }
  return $result;
}
