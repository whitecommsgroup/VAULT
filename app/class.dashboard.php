<?php 

class Dashboard
{
	
    private $app;
    private $logs;

	function __construct($app, $logs) { 
	
		$this->app = $app;
		$this->logs = $logs;

	}
	
	function getJobValueByMonth()
    {
		
		$value = $this->app['db']->fetchColumn('
		SELECT 
			SUM(value)
		FROM 
			jobs
		WHERE
			MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW()) AND jobnumber IS NOT NULL
		');

		return $value;
		
    }

	function getJobValueByQuarter()
    {
		
		$results = $this->app['db']->fetchAssoc('
		SELECT
			(SELECT SUM(value) FROM jobs WHERE MONTH(date) = MONTH(DATE_SUB(NOW(), INTERVAL 3 MONTH))  AND YEAR(date) = YEAR(DATE_SUB(NOW(), INTERVAL 3 MONTH)) AND jobnumber IS NOT NULL) As month1,
			(SELECT SUM(value) FROM jobs WHERE MONTH(date) = MONTH(DATE_SUB(NOW(), INTERVAL 2 MONTH))  AND YEAR(date) = YEAR(DATE_SUB(NOW(), INTERVAL 3 MONTH)) AND jobnumber IS NOT NULL) As month2,
			(SELECT SUM(value) FROM jobs WHERE MONTH(date) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))  AND YEAR(date) = YEAR(DATE_SUB(NOW(), INTERVAL 3 MONTH)) AND jobnumber IS NOT NULL) As month3
		');

		return $results;
		
    }


    function getLast5Clients()
    {
		$results = $this->app['db']->fetchAll('
			SELECT
				organisation.*, organisation_details.*
			FROM
				organisation
			LEFT JOIN
				organisation_details ON organisation.id = organisation_details.organisationid
			WHERE
				deleted = 0 AND name IS NOT NULL AND type = 1 AND accountstatusid = 1
			ORDER BY 
				organisation.firstcontact DESC
			LIMIT 5
		   ');
		
		return $results;

    }

    function getLast5Prospects()
    {
		$results = $this->app['db']->fetchAll('
			SELECT
				organisation.*, organisation_details.*, (SELECT body FROM organisation_notes WHERE organisationid = organisation.id AND deleted = 0 AND jobid = 0 ORDER BY date DESC LIMIT 1) As note
			FROM
				organisation
			LEFT JOIN
				organisation_details ON organisation.id = organisation_details.organisationid
			WHERE
				organisation.deleted = 0 AND name IS NOT NULL AND organisation.type = 1 AND accountstatusid = 2
			ORDER BY 
				organisation.firstcontact DESC
			LIMIT 5
		   ');
		
		return $results;

    }
	
    function getLast5Jobs()
    {
		$results = $this->app['db']->fetchAll('
			SELECT
				*
			FROM
				jobs
			WHERE
				statusid = 1 AND categoryid = 3
			ORDER BY 
				jobnumber DESC
			LIMIT 5
		   ');
		
		return $results;

    }
	
    function getBirthdaysThisMonth()
    {
		$results = $this->app['db']->fetchAll('
			SELECT 
				id, firstname, dob
			FROM 
				wcg_employees
			WHERE
				(MONTH(dob) = MONTH(NOW()) AND DAY(dob) >= DAY(NOW())) OR (MONTH(dob) = MONTH(DATE_ADD(NOW(),INTERVAL 1 MONTH)) AND DAY(dob) <= DAY(NOW()))
		   ');
		   
		   //AND DAYOFMONTH(dob) = 24
			// DATE_FORMAT(dob, \'%m-%d\') AS bday 
		return $results;

    }

    function getHolidaysThisMonth()
    {
		$results = $this->app['db']->fetchAll('
			SELECT 
				firstname, startdate, enddate
			FROM 
				wcg_holidays
			LEFT JOIN
				wcg_employees ON wcg_employees.id = wcg_holidays.employeeid
			WHERE startdate < DATE_ADD(NOW(),INTERVAL 30 DAY) AND enddate >= NOW()
			ORDER BY startdate ASC
		   ');
		   
		   //AND DAYOFMONTH(dob) = 24
			// DATE_FORMAT(dob, \'%m-%d\') AS bday 
		return $results;

    }

    function getNoticeboard()
    {
		$results = $this->app['db']->fetchAll('
			SELECT 
				wcg_noticeboard.*, wcg_employees.firstname
			FROM 
				wcg_noticeboard
			LEFT JOIN
				wcg_employees ON wcg_employees.id = wcg_noticeboard.employeeid
			ORDER BY
				date DESC
		   ');
		   
		return $results;

    }

	function addNotice($notice, $userid){
		   
		$this->app['db']->insert(
			'wcg_noticeboard',
			array(
				'notice' => $notice,
				'employeeid' => $userid,
				'date' => date("Y-m-d H:i:s", time())
			)
		);

		$return = $this->app['db']->lastInsertId();
		
		return $return;

	}

    function deleteNotice($id){
		   
		  $return = $this->app['db']->delete(
			'wcg_noticeboard',
			array(
				'id' => $id
			)
		);

		return $return;
	}

}

?>