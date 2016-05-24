<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Threepeas_Page_GetCaseRoles {

  public static function getCaseRoles() {
    $caseID    = CRM_Utils_Type::escape($_GET['caseID'], 'Integer');
    $contactID = CRM_Utils_Type::escape($_GET['cid'], 'Integer');

    $sortMapper = array(
      0 => 'relation', 1 => 'name', 2 => 'phone', 3 => 'email', 4 => 'actions'
    );

    $sEcho     = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
    $offset    = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
    $rowCount  = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 25;
    $sort      = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : 'relation';
    $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String') : 'asc';

    $params = $_POST;
    if ($sort && $sortOrder) {
      $sortSQL = $sort .' '.$sortOrder;
    }

    $caseRelationships = CRM_Case_BAO_Case::getCaseRoles($contactID, $caseID);
    $caseTypeName = CRM_Case_BAO_Case::getCaseType($caseID, 'name');
    $xmlProcessor = new CRM_Case_XMLProcessor_Process();
    $caseRoles    = $xmlProcessor->get($caseTypeName, 'CaseRoles');

    $hasAccessToAllCases = CRM_Core_Permission::check('access all cases and activities');

    $managerRoleId = $xmlProcessor->getCaseManagerRoleId($caseTypeName);
    if (!empty($managerRoleId)) {
      $caseRoles[$managerRoleId] = $caseRoles[$managerRoleId] . '<br />' . '(' . ts('Case Manager') . ')';
    }

    foreach ($caseRelationships as $key => $value) {
      //calculate roles that don't have relationships
      if (CRM_Utils_Array::value($value['relation_type'], $caseRoles)) {
        //keep naming from careRoles array
        $caseRelationships[$key]['relation'] = $caseRoles[$value['relation_type']];
        unset($caseRoles[$value['relation_type']]);
      }
      // mark orginal case relationships record to use on setting edit links below
      $caseRelationships[$key]['source'] = 'caseRel';
    }

    $caseRoles['client'] = CRM_Case_BAO_Case::getContactNames($caseID);

    // move/transform caseRoles array data to caseRelationships
    // for sorting and display
    foreach($caseRoles as $id => $value) {
      if ($id != "client") {
        $rel = array();
        $rel['relation'] = $value;
        $rel['relation_type'] = $id;
        $rel['name'] = '(not assigned)';
        $rel['phone'] = '';
        $rel['email'] = '';
        $rel['source'] = 'caseRoles';
        $caseRelationships[] = $rel;
      } else {
        foreach($value as $clientRole) {
          $relClient = array();
          $relClient['relation'] = 'Client';
          $relClient['name'] = $clientRole['sort_name'];
          $relClient['phone'] = $clientRole['phone'];
          $relClient['email'] = $clientRole['email'];
          $relClient['cid'] = $clientRole['contact_id'];
          $relClient['source'] = 'contact';
          $caseRelationships[] = $relClient;
        }
      }
    }

    // sort clientRelationships array using jquery call params
    foreach ($caseRelationships as $key => $row) {
      $sortArray[$key]  = $row[$sort];
    }

    $sort_type = "SORT_" . strtoupper($sortOrder);
    array_multisort($sortArray, constant($sort_type), $caseRelationships);

    //limit rows display
    $allCaseRelationships = $caseRelationships;
    $caseRelationships = array_slice($allCaseRelationships, $offset, $rowCount, TRUE);

    // set user name, email and edit columns links
    // idx will count number of current row / needed by edit links
    $idx = 1;
    foreach ($caseRelationships as $key => $row) {
      // view user links
      if ($caseRelationships[$key]['cid']) {
        $caseRelationships[$key]['name'] = '<a href='.CRM_Utils_System::url('civicrm/contact/view',
            'action=view&reset=1&cid='.$caseRelationships[$key]['cid']).'>'.$caseRelationships[$key]['name'].'</a>';
      }
      // email column links/icon
      if ($caseRelationships[$key]['email']) {
        $caseRelationships[$key]['email'] = '<a href="'.CRM_Utils_System::url('civicrm/contact/view/activity', 'action=reset=1&action=add&atype=3&cid='.$caseRelationships[$key]['cid']).'&caseid='.$caseID.'" title="compose and send an email"><div class="icon email-icon" title="compose and send an email"></div>
             </a>';
      }
      // edit links
      if ($hasAccessToAllCases) {
        switch($caseRelationships[$key]['source']){
          case 'caseRel':
            $caseRelationships[$key]['actions'] =
              '<a href="#" title="edit case role" onclick="createRelationship( '.$caseRelationships[$key]['relation_type'].', '.$caseRelationships[$key]['cid'].', '.$caseRelationships[$key]['rel_id'].', '.$idx.', \''.$caseRelationships[$key]['relation'].'\' );return false;"><div class="icon edit-icon" ></div></a>&nbsp;&nbsp;<a href="#" class="case-role-delete" case_id="'.$caseID.'" rel_id="'.$caseRelationships[$key]['rel_id'].'"  rel_type="'.$caseRelationships[$key]['relation_type'].'"><div class="icon delete-icon" title="remove contact from case role"></div></a>';
            break;

          case 'caseRoles':
            $caseRelationships[$key]['actions'] =
              '<a href="#" title="edit case role" onclick="createRelationship('.$caseRelationships[$key]['relation_type'].', null, null, '.$idx.',  \''.$caseRelationships[$key]['relation'].'\');return false;"><div class="icon edit-icon"></div></a>';
            break;
        }
      } else {
        $caseRelationships[$key]['actions'] = '';
      }
      $idx++;
    }
    $iFilteredTotal = $iTotal = $params['total'] = count($allCaseRelationships);
    $selectorElements = array('relation', 'name', 'phone', 'email', 'actions');

    echo CRM_Utils_JSON::encodeDataTableSelector($caseRelationships, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
    CRM_Utils_System::civiExit();
  }

}