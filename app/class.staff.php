<?php 

class Staff
{
	
    private $app;
    private $logs;

	function __construct($app, $logs) { 
	
		$this->app = $app;
		$this->logs = $logs;

	}
	
    function updateEmployeeDetails($id, $firstname, $lastname, $departmentid, $email, $username, $groupid, $dob){
		   
		$return = $this->app['db']->update(
			'wcg_employees',
			array(
				'firstname' => $firstname,
				'lastname' => $lastname,
				'departmentid' => $departmentid,
				'email' => $email,
				'username' => $username,
				'groupid' => $groupid,
				'dob' => uk_date_to_mysql_date($dob)
			),
			array(
				'id' => $id
			)
		);

		return $return;
	}

	function updateEmployeePassword($id, $password)
    {
		
		$password = md5($password);

		$return = $this->app['db']->update(
			'wcg_employees',
			array(
				'password' => $password
			),
			array(
				'id' => $id
			)
		);

		return $return;
		
    }
	
    function getEmployeeDetails($id)
    {
		$results = $this->app['db']->fetchAssoc('
			SELECT
			   wcg_employees.*
			FROM
			   wcg_employees
			WHERE
				id = ?
		   ', array($id));

		return $results;

    }

    function getDerpartments()
    {
		$results = $this->app['db']->fetchAll('
			SELECT
			   *
			FROM
			   wcg_departments
			   ');

		return $results;

    }

    function getUsergroupsByEmployee($id)
    {
		$results = $this->app['db']->fetchAll('
			SELECT
			   wcg_usergroups.*, ((wcg_employees.groupid&wcg_usergroups.id) = wcg_usergroups.id) AS checked
			FROM
			   wcg_usergroups
			LEFT JOIN 
				wcg_employees ON wcg_employees.id = ?
	   ', array($id));

		return $results;

    }
	
    function getUsergroups()
    {
		$results = $this->app['db']->fetchAll('
			SELECT
			   *
			FROM
			   wcg_usergroups
			   ');

		return $results;

    }


    function getStaff()
    {
		$results = $this->app['db']->fetchAll('
			SELECT
			   *
			FROM
			   wcg_employees
			WHERE
				leaver = 0
			   ');

		return $results;

    }

    function getHolidays()
    {
		$results = $this->app['db']->fetchAll('
			SELECT
			   wcg_holidays.*, wcg_employees.firstname, wcg_employees.lastname
			FROM
			   wcg_holidays
			LEFT JOIN
				wcg_employees ON wcg_employees.id = wcg_holidays.employeeid
			ORDER BY
				startdate ASC
			   ');

		return $results;

    }
	
	function addHoliday($employeeid, $startdate, $enddate, $days){
		   
		$this->app['db']->insert(
			'wcg_holidays',
			array(
				'employeeid' => $employeeid,
				'startdate' => uk_date_to_mysql_date($startdate),
				'enddate' => uk_date_to_mysql_date($enddate),
				'days' => $days
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('wcg_holidays', $return, 'insert');
		
		return $return;

	}

    function deleteHoliday($id){
		   
		  $return = $this->app['db']->delete(
			'wcg_holidays',
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('wcg_holidays', $return, 'delete');

		return $return;
	}

}

?>