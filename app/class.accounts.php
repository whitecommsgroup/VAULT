<?php 


class Accounts
{
	
    private $app;
    private $logs;

	function __construct($app, $logs) { 
	
		$this->app = $app;
		$this->logs = $logs;

	}

	function addBrand($name, $accountid){
		   
		$this->app['db']->insert(
			'account_brands',
			array(
				'name' => $name,
				'organisationid' => $accountid
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('organisation_contacts', $return, 'delete');
		
		return $return;

	}

    function getListOfBrandsByCompany($id)
    {
		$results = $this->app['db']->fetchAll('
			SELECT
				*
			FROM
				account_brands
			WHERE
				organisationid = ?
		   ', array($id));
		
		return $results;

    }


    function getListOfProspects()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
              distinct(organisation.id) as orgid, organisation_details.name As orgname, organisation_contactstates.name AS contactstatename, organisation.lastdate, organisation.nextdate, (SELECT organisation_notes.body FROM organisation_notes WHERE organisation_notes.organisationid = orgid ORDER BY date DESC LIMIT 1) As actionbody, (SELECT organisation_notes.date FROM organisation_notes WHERE organisation_notes.organisationid = orgid ORDER BY date DESC LIMIT 1) As actiondate, organisation_contacts.id As contactid, organisation_contacts.firstname As firstname, organisation_contacts.lastname As lastname, organisation_contacts.phone As phone, organisation_contacts.mobile As mobile, organisation_contacts.email As email, organisation_substates.name As substatename, organisation.accountsubstatusid
           FROM
               organisation
			LEFT JOIN
				organisation_details ON organisation.id = organisation_details.organisationid
			LEFT JOIN
				organisation_contactstates ON organisation.contactstatusid = organisation_contactstates.id
			LEFT JOIN
				organisation_substates ON organisation.accountsubstatusid = organisation_substates.id
			STRAIGHT_JOIN
				organisation_contacts ON organisation.id = organisation_contacts.organisationid
           WHERE
               type = ? AND accountstatusid = ? AND organisation_details.name IS NOT NULL
		   GROUP BY organisation.id
		   ORDER BY nextdate DESC
		   LIMIT 100
		   ', array(1,2));
		
		return $results;

    }

    function getListOfCompanies()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               organisation.*, organisation_details.*
           FROM
               organisation
			LEFT JOIN
				organisation_details ON organisation.id = organisation_details.organisationid
           WHERE
               name IS NOT NULL AND (deleted IS NULL OR deleted = 0)
		   ORDER BY name ASC
	   ');
		
		return $results;

    }

    function getListOfCompaniesByType($type)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               organisation.*, organisation_details.*, wcg_employees.firstname, wcg_employees.lastname
           FROM
               organisation
			LEFT JOIN
				organisation_details ON organisation.id = organisation_details.organisationid
			LEFT JOIN	
				wcg_employees ON organisation.accountmanagerid = wcg_employees.id
           WHERE
               name IS NOT NULL AND (deleted IS NULL OR deleted = 0) AND type = ?
		   ORDER BY name ASC
	   ', array($type));
		
		return $results;

    }

    function getListOfCompaniesByTypeAndStatus($type, $status)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               organisation.*, organisation_details.*, wcg_employees.firstname, wcg_employees.lastname
           FROM
               organisation
			LEFT JOIN
				organisation_details ON organisation.id = organisation_details.organisationid
			LEFT JOIN	
				wcg_employees ON organisation.accountmanagerid = wcg_employees.id
           WHERE
               name IS NOT NULL AND (deleted IS NULL OR deleted = 0) AND type = ? AND accountstatusid = ?
		   ORDER BY name ASC
	   ', array($type, $status));
		
		return $results;

    }

    function getListOfClients()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               DISTINCT organisation_details.id As uid, organisation.*, organisation_details.*
           FROM
               organisation
			LEFT JOIN
				organisation_details ON organisation.id = organisation_details.organisationid
           WHERE
               type = 1 AND accountstatusid = 1 AND name IS NOT NULL
		   ORDER BY name ASC
		   ');
		
		return $results;

    }
	
    function getClientsDetails($id)
    {
		$results = $this->app['db']->fetchAssoc('
           SELECT
               organisation.*, organisation_details.*, wcg_employees.firstname, wcg_employees.lastname, (SELECT name FROM account_sectors WHERE id = organisation.sectorid) As sectorname, (SELECT name FROM account_sources WHERE id = organisation.sourceid) As sourcename
           FROM
               organisation
			LEFT JOIN
				organisation_details ON organisation.id = organisation_details.organisationid
			LEFT JOIN
				wcg_employees ON wcg_employees.id = organisation.accountmanagerid
           WHERE
               organisation.id = ?
		   ', array($id));

		return $results;

    }
	
    function getClientIdByContact($id)
    {
		
		return $this->app->fetchColumn('SELECT
               organisation.id
           FROM
               organisation_contacts
			LEFT JOIN
				organisation ON organisation.id = organisation_contacts.organisationid
           WHERE
               organisation_contacts.id = ?
		', array($id), 0);

    }


    function getJobsByClient($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               *
           FROM
               jobs
           WHERE
               customerid = ?
		   ', array($id));
		
		return $results;

    }

    function updateAccountValue($id, $name, $value){
		   
		$return = $this->app['db']->update(
			'organisation',
			array(
				$name => $value
			),
			array(
				'id' => $id
			)
		);

		return $return;
	}

    function updateAccountDetailValue($id, $name, $value){
		   
		$return = $this->app['db']->update(
			'organisation_details',
			array(
				$name => $value
			),
			array(
				'organisationid' => $id
			)
		);

		return $return;
	}

	function addAccount($type, $companyName, $companyPhone, $companyEmail, $companyWebsite, $companyAdd1, $companyAdd2, $companyAdd3, $companyTown, $companyCounty, $companyPostcode, $companyAccountStatus, $companyFirstcontact, $companyAccManager, $companyNextDate, $companySource, $compactSector, $companyServices, $companyAccountSubStatus){
		   
		$this->app['db']->insert(
			'organisation',
			array(
				'type' => $type,
				'accountmanagerid' => $companyAccManager,
				'sourceid' => $companySource,
				'firstcontact' => uk_date_to_mysql_date($companyFirstcontact),
				'services' => $companyServices,
				'sectorid' => $compactSector,
				'accountstatusid' => $companyAccountStatus,
				'nextdate' => uk_date_to_mysql_date($companyNextDate),
				'accountsubstatusid' => $companyAccountSubStatus,
			)
		);

		$newid = $this->app['db']->lastInsertId();

		$this->app['db']->insert(
			'organisation_details',
			array(
				'organisationid' => $newid,
				'name' => $companyName,
				'phone' => $companyPhone,
				'email' => $companyEmail,
				'website' => $companyWebsite,
				'address1' => $companyAdd1,
				'address2' => $companyAdd2,
				'address3' => $companyAdd3,
				'town' => $companyTown,
				'county' => $companyCounty,
				'postcode' => $companyPostcode
			)
		);

		$newdetailsid = $this->app['db']->lastInsertId();

		$this->logs->addLog('organisation', $newid, 'insert');
		$this->logs->addLog('organisation_details', $newdetailsid, 'insert');

		return $newid;

	}
	
	function updateClientLapsed($accountid){

		$return = $this->app['db']->update(
			'organisation',
			array(
				'accountstatusid' => 4
			),
			array(
				'id' => $accountid
			)
		);
		
	}

	function updateClientStatus($accountid, $statusid){

		$return = $this->app['db']->update(
			'organisation',
			array(
				'accountstatusid' => $statusid
			),
			array(
				'id' => $accountid
			)
		);
		
	}

	function updateClientSubStatus($accountid, $substatusid){

		$return = $this->app['db']->update(
			'organisation',
			array(
				'accountsubstatusid' => $substatusid
			),
			array(
				'id' => $accountid
			)
		);
		
	}

	function updateClient($accountid, $companyName, $companyPhone, $companyEmail, $companyWebsite, $companyAdd1, $companyAdd2, $companyAdd3, $companyTown, $companyCounty, $companyPostcode, $companyAccountStatus, $companyFirstcontact, $companyAccManager, $companyNextDate, $companySource, $compactSector, $companyServices){
		   
		$return = $this->app['db']->update(
			'organisation',
			array(
				'accountmanagerid' => $companyAccManager,
				'sourceid' => $companySource,
				'firstcontact' => uk_date_to_mysql_date($companyFirstcontact),
				'services' => $companyServices,
				'sectorid' => $compactSector,
				'accountstatusid' => $companyAccountStatus,
				'nextdate' => uk_date_to_mysql_date($companyNextDate)
			),
			array(
				'id' => $accountid
			)
		);

		$return = $this->app['db']->update(
			'organisation_details',
			array(
				'name' => $companyName,
				'phone' => $companyPhone,
				'email' => $companyEmail,
				'website' => $companyWebsite,
				'address1' => $companyAdd1,
				'address2' => $companyAdd2,
				'address3' => $companyAdd3,
				'town' => $companyTown,
				'county' => $companyCounty,
				'postcode' => $companyPostcode
			),
			array(
				'organisationid' => $accountid
			)
		);

		$this->logs->addLog('organisation', $accountid, 'update');

		return $return;

	}

	function updateSupplier($accountid, $companyName, $companyPhone, $companyEmail, $companyWebsite, $companyAdd1, $companyAdd2, $companyAdd3, $companyTown, $companyCounty, $companyPostcode, $companyAccountStatus){
		   
		$return = $this->app['db']->update(
			'organisation',
			array(
				'accountmanagerid' => $companyAccManager,
				'accountstatusid' => $companyAccountStatus,
			),
			array(
				'id' => $accountid
			)
		);

		$return = $this->app['db']->update(
			'organisation_details',
			array(
				'name' => $companyName,
				'phone' => $companyPhone,
				'email' => $companyEmail,
				'website' => $companyWebsite,
				'address1' => $companyAdd1,
				'address2' => $companyAdd2,
				'address3' => $companyAdd3,
				'town' => $companyTown,
				'county' => $companyCounty,
				'postcode' => $companyPostcode
			),
			array(
				'organisationid' => $accountid
			)
		);

		$this->logs->addLog('organisation', $accountid, 'update');

		return $return;

	}

	function getServicesName($id)
	{
		$results = $this->app['db']->fetchAssoc('SELECT services FROM organisation WHERE id = ?', array($id), 0);
		
		//return $results['services'];
		
		switch($id){
			
			case '1':
			
				return 'Creative';
				break;

			case '2':
			
				return 'Interactive';
				break;

			case '3':
			
				return 'Creative, Interactive';
				break;

			case '7':
			
				return 'Creative, Interactive, Healthcare';
				break;
				
			default:
			
				return 'None';
				break;
			
		}
	}

	function getCompanyName($id)
	{
		return $this->app['db']->fetchColumn('SELECT name FROM organisation_details WHERE organisationid = ?', array($id), 0);
	}

	function getStateName($id)
	{
		return $this->app['db']->fetchColumn('SELECT name FROM organisation_states WHERE id = ?', array($id), 0);
	}

	function getActionMonthName($id)
	{
		return $this->app['db']->fetchColumn('SELECT name FROM account_actionmonths WHERE id = ?', array($id), 0);
	}

	function getSectorName($id)
	{
		return $this->app['db']->fetchColumn('SELECT name FROM account_sectors WHERE id = ?', array($id), 0);
	}

	function getSourceName($id)
	{
		return $this->app['db']->fetchColumn('SELECT name FROM account_sources WHERE id = ?', array($id), 0);
	}

	function getListOfSectors()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM account_sectors');
		return $results;
	}

	function getListOfSources()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM account_sources');
		return $results;
	}

	function getListOfActionMonths()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM account_actionmonths');
		return $results;
	}

	function getListOfStatesByType($type)
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM organisation_states WHERE accounttype = ?', array($type));
		return $results;
	}

	function getListOfContactStates()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM organisation_contactstates');
		return $results;
	}

	function getListOfSubStates($stateid)
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM organisation_substates WHERE parentid = ?', array($stateid));
		return $results;
	}

	function deleteAccount($id){
		   
		  $return = $this->app['db']->update(
				'organisation',
				array(
					'deleted' => 1
				),
				array(
					'id' => $id
				)
			);

		  $return = $this->app['db']->update(
				'organisation_contacts',
				array(
					'deleted' => 1
				),
				array(
					'organisationid' => $id
				)
			);

		$this->logs->addLog('organisation', $id, 'delete');

		return $return;

	}

}

?>