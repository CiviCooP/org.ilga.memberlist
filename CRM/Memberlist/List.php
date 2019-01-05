<?php
/**
 *  @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 *  @date 10/19/18 11:18 AM
 *  @license AGPL-3.0
 */

class CRM_Memberlist_List {

  var $membershipStatusId;
  var $associateMembershipTypeId;
  var $organisationalCustomGroup;
  var $publicationOnWebsiteField;
  var $params;
  var $countries = array();
  var $result = array();

  /**
   * CRM_Memberlist_List constructor.
   */
  public function __construct($params) {

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
    $this->associateMembershipTypeId = $result = civicrm_api3('MembershipType', 'getvalue', [
      'return' => 'id',
      'name' => "Associate membership",
    ]);

    $this->params = $params;
  }

  public function calculate(){

    if(isset($this->params['anonymous']) && $this->params['anonymous']){
      $anonymous = true;
    } else {
      $anonymous = false;
    }

    $sql = "
       select c.id         contact_id,
              display_name organization_name, 
              cn.name country, 
              cn.iso_code iso_code,
              r.name region,
              cg.{$this->publicationOnWebsiteField['column_name']} publication,
              'Full member' member_type
       from civicrm_contact c
       left join civicrm_address a on (a.contact_id = c.id and a.is_primary = 1)
       left join civicrm_country cn on (cn.id = a.country_id)
       left join civicrm_worldregion r on cn.region_id = r.id
       left join {$this->organisationalCustomGroup['table_name']} cg on (cg.entity_id=c.id)
       where contact_type = 'Organization' and is_deleted=0
       and exists(
         select 1 from civicrm_membership m where m.contact_id = c.id
                                            and m.status_id != %1
                                            and m.membership_type_id != %2
          )
       union
       select c.id         contact_id,
              display_name organization_name, 
              cn.name country, 
              cn.iso_code iso_code,
              r.name region,
              cg.{$this->publicationOnWebsiteField['column_name']} publication,
              'Associate member' member_type
       from civicrm_contact c
       left join civicrm_address a on (a.contact_id = c.id and a.is_primary = 1)
       left join civicrm_country cn on (cn.id = a.country_id)
       left join civicrm_worldregion r on cn.region_id = r.id
       left join {$this->organisationalCustomGroup['table_name']} cg on (cg.entity_id=c.id)
       where contact_type = 'Organization' and is_deleted=0
       and exists(
         select 1 from civicrm_membership m where m.contact_id = c.id
                                            and m.status_id != %1
                                            and m.membership_type_id = %2
          )
       order by region, country, publication desc, organization_name   
    ";
    $this->result = [];
    $this->countries = [];
    $dao = CRM_Core_DAO::executeQuery($sql,[
      1 => [$this->membershipStatusId,'Integer'],
      2 => [$this->associateMembershipTypeId,'Integer']
    ]);
    while($dao->fetch()){

      if(isset($dao->publication)){
        // member said something about publication : follow his orders.
        if($dao->publication){
          $publication = 1;
        } else {
          $publication = 0;
        }
      // member said nothing about publication, publish by default.
      } else {
        $publication = 1;
      }

      $row = [
        'contact_id' => $dao->contact_id,
        'publication'  => $publication,
        'organization_name' => $publication?$dao->organization_name:'Anonymous',
        'country' => $dao->country,
        'is_code' => $dao->iso_code,
        'region'  => $dao->region,
        'member_type' => $dao->member_type,
      ];
      if($anonymous) {
        if($publication){
           // do nothing if anonymous to to publicate
        } else {
          $this->result[] = $row;
          $this->countries[$dao->iso_code]=true;
        }
      } else {
        $this->result[] = $row;
        $this->countries[$dao->iso_code]=true;
      }
    }
  }

  public function result(){
    return $this->result;
  }

  public function countCountries(){
    return count($this->countries);
  }

}