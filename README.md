# org.ilga.memberlist


Publish the ILGA members with an API for the use on a external website.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM 5.3.1

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl org.ilga.memberlist@https://github.com/CiviCooP/org.ilga.memberlist/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/CiviCooP/org.ilga.memberlist.git
cv en memberlist
```

## Usage

The api can be called by using the following url with a POST action

`https://<site>/sites/all/modules/civicrm/extern/rest.php?entity=Member&action=list&api_key=<userKey>&key=<siteKey>`

The result has the following structure

```json
{

    "is_error": 0,
    "version": 3,
    "count": 153,
    "values": [
        {
                    
            "contact_id": "3636",
            "publication": null,
            "organization_name": "Anonymous",
            "country": "American Samoa",
            "is_code": "AS",
            "region": "America South, Central, North and Caribbean",
            "member_type": "Full member"
                            
        },
        ...
        ],
      "count_countries": 13    
}
```

This fields have the following meaning:

- is_error: when the call is succesfull is_error has the value `0`.
- count: number of organizations in the member list.
- values: list with the organizations to be publiced.
- count_countries: the number of countries that is in the member list (actual the number of unique iso_codes)

Each record in the values list has the following structure:

- contact_id : CiviCRM unique id of the member.
- publication: Does the member allow to publish its name on the website.
- organization_name: Name of the organization of anonymous if publication is not allowed.
- country: Name for the country of the primary address of the organization.
- iso_code: Standard Iso code of the country (2 characters capitals) 
- region: World region where the country is part of.
- member_type: Associate member for associates, Full member for other meberships.

The results are sorted on

1. Region
2. Country
3. Publication
4. Organization Name.




