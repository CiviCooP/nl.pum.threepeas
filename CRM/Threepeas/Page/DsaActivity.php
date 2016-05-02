<?php
/**
 * Class with dummy page to catch the activity list in the Case View page.
 * Makes sure that no actions are available if DSA activity
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_DsaActivity extends CRM_Core_Page {
  function run() {
    $caseID = CRM_Utils_Type::escape($_GET['caseID'], 'Integer');
    $contactID = CRM_Utils_Type::escape($_GET['cid'], 'Integer');
    $userID = CRM_Utils_Type::escape($_GET['userID'], 'Integer');
    $context = CRM_Utils_Type::escape(CRM_Utils_Array::value('context', $_GET), 'String');

    $sortMapper = array(
      0 => 'display_date', 1 => 'ca.subject', 2 => 'ca.activity_type_id',
      3 => 'acc.sort_name', 4 => 'cc.sort_name', 5 => 'ca.status_id',
    );

    $sEcho = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
    $offset = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
    $rowCount = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 25;
    $sort = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : NULL;
    $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String') : 'asc';

    $params = $_POST;
    if ($sort && $sortOrder) {
      $params['sortname'] = $sort;
      $params['sortorder'] = $sortOrder;
    }
    $params['page'] = ($offset / $rowCount) + 1;
    $params['rp'] = $rowCount;

    /*
     * if method to retrieve activities in business dsa extension exists, use it
     * otherwise use core one
     */

    if (method_exists('CRM_Businessdsa_BAO_BusinessDsa', 'getCaseActivity')) {
      $activities = CRM_Businessdsa_BAO_BusinessDsa::getCaseActivity($caseID, $params, $contactID, $context, $userID);
    } else {
      $activities = CRM_Case_BAO_Case::getCaseActivity($caseID, $params, $contactID, $context, $userID);
    }

    $iFilteredTotal = $iTotal = $params['total'];
    $selectorElements = array('display_date', 'subject', 'type', 'with_contacts', 'reporter', 'status', 'links', 'class');

    echo CRM_Utils_JSON::encodeDataTableSelector($activities, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
    CRM_Utils_System::civiExit();
  }
}
