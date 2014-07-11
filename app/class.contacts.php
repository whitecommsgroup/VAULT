<?php 


class Contacts
{
	
    private $app;
    private $logs;

	function __construct($app, $logs) { 
	
		$this->app = $app;
		$this->logs = $logs;

	}

    function updateContactDetailValue($id, $name, $value){
		   
		$return = $this->app['db']->update(
			'organisation_contacts',
			array(
				$name => $value
			),
			array(
				'id' => $id
			)
		);

		return $return;
	}

    function getContacts()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               organisation_contacts.*, organisation_details.name AS organisationname
           FROM
               organisation_contacts
			LEFT JOIN 
				organisation_details ON organisation_contacts.organisationid = organisation_details.organisationid
			WHERE deleted = 0
			ORDER BY lastname, firstname ASC
		   ');
		
		return $results;

    }

    function getContactsByClient($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               *
           FROM
               organisation_contacts
           WHERE
               organisationid = ? AND deleted = ?
			ORDER BY
				firstname
		   ', array($id,0));

		return $results;

    }
	
    function getContactDetails($id)
    {
		$results = $this->app['db']->fetchAssoc('
           SELECT
               *
           FROM
               organisation_contacts
           WHERE
               organisation_contacts.id = ?
		   ', array($id));
		
		return $results;

    }

	
	function addContact($orgid, $contactFirstName, $contactLastName, $contactPosition, $contactPhone, $contactMobile, $contactEmail){
		   
		$this->app['db']->insert(
			'organisation_contacts',
			array(
				'organisationid' => $orgid,
				'firstname' => $contactFirstName,
				'lastname' => $contactLastName,
				'position' => $contactPosition,
				'phone' => $contactPhone,
				'mobile' => $contactMobile,
				'email' => $contactEmail
			)
		);

		$return = $this->app['db']->lastInsertId();
		
		$this->logs->addLog('organisation_contacts', $return, 'insert');
		
		return $return;


	}

	function updateContact($contactid, $orgid, $contactFirstName, $contactLastName, $contactPosition, $contactPhone, $contactMobile, $contactEmail){
		   
		  $return = $this->app['db']->update(
			'organisation_contacts',
			array(
				'organisationid' => $orgid,
				'firstname' => $contactFirstName,
				'lastname' => $contactLastName,
				'position' => $contactPosition,
				'phone' => $contactPhone,
				'mobile' => $contactMobile,
				'email' => $contactEmail
			),
			array(
				'id' => $contactid
			)
		);

		$this->logs->addLog('organisation_contacts', $contactid, 'update');

		return $return;

	}

	
	function getContactName($id)
	{

		$results = $this->app['db']->fetchAssoc('
           SELECT
               firstname, lastname
           FROM
               organisation_contacts
           WHERE
               id = ?
		   ', array($id));

		return $results['firstname'] . ' ' . $results['lastname'];

	}

	function deleteContact($id){
		   
		  $return = $this->app['db']->update(
			'organisation_contacts',
			array(
				'deleted' => 1
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('organisation_contacts', $id, 'delete');

		return $return;

	}


}

?>