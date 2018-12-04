<?php
/**
 *  @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 *  @date 10/19/18 11:17 AM
 *  @license AGPL-3.0
 */

use CRM_Memberlist_ExtensionUtil as E;

/**
 * Member.List API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_member_List_spec(&$spec) {
}

/**
 * Member.List API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_member_List($params) {
  try{
    $list = new CRM_Memberlist_List($params);
    $list->calculate();
    $result = $list->result();
    $toReturn = civicrm_api3_create_success($result, $params, 'Member', 'List');
    $toReturn['count_countries'] = $list->countCountries();
    return $toReturn;
  }
  catch (Exception $ex) {
    throw new API_Exception($ex->getMessage());
  }
}
