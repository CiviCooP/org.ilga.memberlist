<?php
/**
 *  @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 *  @date 10/19/18 11:18 AM
 *  @license AGPL-3.0
 */

class CRM_Memberlist_List {

  var $membershipStatusId;
  var $organisationalCustomGroup;
  var $publicationOnWebsiteField;

  /**
   * CRM_Memberlist_List constructor.
   */
  public function __construct() {

    $this->membershipStatusId = civicrm_api3('MembershipStatus', 'getvalue', [
      'return' => "id",
      'name' => "Not Approved",
    ]);
    $this->organisationalCustomGroup = civicrm_api3('CustomGroup', 'getsingle', [
      'name' => "Organisational_data",
    ]);
    $this->publicationOnWebsiteField = civicrm_api3('CustomField', 'getsingle', [
      'custom_group_id' => "Organisational_data",
      'name' => 'Publication_of_Organiation_Data_on_website'
    ]);

  }

  public function result(){

    $sql = "
       select c.id         contact_id,
              display_name organization_name, 
              cn.name country, 
              r.name region,
              cg.{$this->publicationOnWebsiteField['column_name']} publication
       from civicrm_contact c
       left join civicrm_address a on (a.contact_id = c.id and a.is_primary = 1)
       left join civicrm_country cn on (cn.id = a.country_id)
       left join civicrm_worldregion r on cn.region_id = r.id
       left join {$this->organisationalCustomGroup['table_name']} cg on (cg.entity_id=c.id)
       where contact_type = 'Organization'
       and exists(
         select 1 from civicrm_membership m where m.contact_id = c.id
                                             and m.status_id != %1
          )
       order by r.name, cn.name, publication desc, organization_name
    ";
    $result = [];
    $dao = CRM_Core_DAO::executeQuery($sql,[
      1 => [$this->membershipStatusId,'Integer']
    ]);
    while($dao->fetch()){
      $row = [
        'contact_id' => $dao->contact_id,
        'publication'  => $dao->publication,
        'organization_name' => $dao->publication?$dao->organization_name:'Anonymous',
        'country' => $dao->country,
        'region'  => $dao->region
      ];
      $result[] = $row;
    }
    return $result;
  }

}