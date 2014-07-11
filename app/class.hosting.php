<?php 


class Hosting
{
	
    private $app;
    private $logs;

	function __construct($app, $logs) { 
	
		$this->app = $app;
		$this->logs = $logs;

	}

    function makeHostingOutstanding()
    {

		$results = $this->app['db']->executeUpdate('
			UPDATE
			   organisation_hosting
			SET
				outstanding = 1
			WHERE
				DATE(organisation_hosting.expirydate) >= DATE(DATE_ADD(NOW(),INTERVAL 7 WEEK)) AND DATE(organisation_hosting.expirydate) <= DATE(DATE_ADD(NOW(),INTERVAL 8 WEEK)) AND deleted = 0
	   ');

		return $results;

    }

    function getHostingOutstandingList()
	{
		
		$results = $this->app['db']->fetchAll('
			SELECT
			   organisation_hosting.id, organisation_details.name, organisation_hosting.description, organisation_hosting.period, IFNULL(organisation_hosting.value,0) AS value, DATE_FORMAT(organisation_hosting.expirydate,"%d-%m-%Y") As expirydate, (SELECT name FROM organisation_hostingtypes WHERE id = organisation_hosting.type) As type, organisation_hosting.type As typeid
			FROM
			   organisation_hosting
			LEFT JOIN
				organisation_details ON organisation_details.organisationid = organisation_hosting.organisationid
			WHERE
				outstanding = 1 AND deleted = 0
			ORDER BY
				organisation_hosting.expirydate ASC
			   ');

		return $results;
		
	}

    function getHostingExpiringList()
    {
		
		$results = $this->app['db']->fetchAll('
			SELECT
			   organisation_hosting.id, organisation_details.name, organisation_hosting.description, organisation_hosting.period, IFNULL(organisation_hosting.value,0) AS value, DATE_FORMAT(organisation_hosting.expirydate,"%d-%m-%Y") As expirydate, (SELECT name FROM organisation_hostingtypes WHERE id = organisation_hosting.type) As type
			FROM
			   organisation_hosting
			LEFT JOIN
				organisation_details ON organisation_details.organisationid = organisation_hosting.organisationid
			WHERE
				((DATE(organisation_hosting.expirydate) >= DATE(DATE_ADD(NOW(),INTERVAL 7 WEEK)) AND DATE(organisation_hosting.expirydate) <= DATE(DATE_ADD(NOW(),INTERVAL 8 WEEK))) OR outstanding = 1) AND deleted = 0
			ORDER BY
				organisation_hosting.expirydate ASC
			   ');

		return $results;

    }

    function getHostingList($type)
    {
		$results = $this->app['db']->fetchAll('
			SELECT
			   organisation_hosting.*, organisation_details.name
			FROM
			   organisation_hosting
			LEFT JOIN
				organisation_details ON organisation_details.organisationid = organisation_hosting.organisationid
			WHERE
				type = ? AND deleted = 0
			ORDER BY
				organisation_details.name ASC
			   ', array($type));

		return $results;

    }

    function getHostingListDeleted($type)
    {
		$results = $this->app['db']->fetchAll('
			SELECT
			   organisation_hosting.*, organisation_details.name
			FROM
			   organisation_hosting
			LEFT JOIN
				organisation_details ON organisation_details.organisationid = organisation_hosting.organisationid
			WHERE
			   type = ? AND deleted = 1
			ORDER BY
				organisation_details.name ASC
		   ', array($type));

		return $results;

    }

    function getHostingByClient($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               *
           FROM
               organisation_hosting
           WHERE
               organisationid = ? AND deleted = 0
		   ', array($id));
		
		return $results;

    }

    function getHostingDetails($id)
    {
		$results = $this->app['db']->fetchAssoc('
           SELECT
               *
           FROM
               organisation_hosting
           WHERE
               id = ?
		   ', array($id));
		
		return $results;

    }
	
	function addHosting($hostingCompany, $hostingJob, $hostingDescription, $hostingValue, $hostingExpiryDate, $hostingPeriod, $hostingType){
		   
		$this->app['db']->insert(
			'organisation_hosting',
			array(
				'organisationid' => $hostingCompany,
				'jobid' => $hostingJob,
				'description' => $hostingDescription,
				'period' => $hostingPeriod,
				'expirydate' => uk_date_to_mysql_date($hostingExpiryDate),
				'type' => $hostingType,
				'value' => $hostingValue
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('organisation_hosting', $return, 'insert');
		
		return $return;

	}

	function updateHosting($id, $hostingCompany, $hostingJob, $hostingDescription, $hostingValue, $hostingExpiryDate, $hostingPeriod, $hostingType){
		   
		  $return = $this->app['db']->update(
			'organisation_hosting',
			array(
				'organisationid' => $hostingCompany,
				'jobid' => $hostingJob,
				'description' => $hostingDescription,
				'period' => $hostingPeriod,
				'expirydate' => uk_date_to_mysql_date($hostingExpiryDate),
				'type' => $hostingType,
				'value' => $hostingValue,
				'outstanding' => 0,
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('organisation_hosting', $id, 'update');

		return $return;

	}

	function deleteHosting($id){
		   
		  $return = $this->app['db']->update(
			'organisation_hosting',
			array(
				'deleted' => 1,
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('organisation_hosting', $id, 'delete');

		return $return;

	}

	function updateHostingExpiryDate($id){

		$newExpiry = $this->app['db']->fetchColumn('SELECT DATE_ADD(expirydate, INTERVAL 1 YEAR) FROM organisation_hosting WHERE id = ?', array($id), 0);
		   
		  if($newExpiry)
		  {
			  $return = $this->app['db']->update(
				'organisation_hosting',
				array(
					'expirydate' => $newExpiry,
					'outstanding' => 0
				),
				array(
					'id' => $id
				)
			);

			$this->logs->addLog('organisation_hosting', $id, 'update');
	
			return $return;
		  }else{
			return false;  
		  }
	}

    function getHostingTypes()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               *
           FROM
               organisation_hostingtypes
		   ');
		
		return $results;

    }

	function getTypeName($id)
	{
		return $this->app['db']->fetchColumn('SELECT name FROM organisation_hostingtypes WHERE id = ?', array($id), 0);
	}


}

?>