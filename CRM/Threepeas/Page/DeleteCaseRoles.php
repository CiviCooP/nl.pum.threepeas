<?php
/**
 * Class with dummy page to catch the delete case role in the Case View page.
 * Makes sure that remove role is not allowed if relation type is Expert and there is still a business dsa with
 * status payable
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

require_once 'CRM/Core/Page.php';

class CRM_Threepeas_Page_DeleteCaseRoles extends CRM_Core_Page {
  /**
   * Function to delete case role (based on CRM_Case_Page_AJAX::deleteCaseRoles)
   */
  function run() {
    $caseId  = CRM_Utils_Type::escape($_POST['case_id'], 'Integer');
    $relType = CRM_Utils_Type::escape($_POST['rel_type'], 'Integer');
    $relId = CRM_Utils_Type::escape($_POST['rel_id'], 'Integer');
    $result['status'] = null;

    if (method_exists('CRM_Businessdsa_BAO_BusinessDsa', 'canExpertBeRemovedFromCase')) {
      $expertRelationType = CRM_Threepeas_Utils::getRelationshipTypeWithName('Expert');
      $expertRelationTypeId = $expertRelationType['id'];
      if ($relType == $expertRelationTypeId && CRM_Businessdsa_BAO_BusinessDsa::canExpertBeRemovedFromCase($caseId) == FALSE) {
        $result['status'] = 'pum-not-to-be-removed';
        echo json_encode($result);
        CRM_Utils_System::civiExit();
      }
    }
    $sql = "DELETE FROM civicrm_relationship WHERE case_id=%1 AND relationship_type_id=%2 and id = %3";
    $sqlParams = array(
      1 => array($caseId, 'Integer'),
      2 => array($relType, 'Integer'),
      3 => array($relId, 'Integer'),
    );
    CRM_Core_DAO::executeQuery($sql, $sqlParams);
    echo json_encode($result);
    CRM_Utils_System::civiExit();
  }
}
