<?php 


class User
{

    private $app;

	function __construct($app) { 
	
		$this->app = $app;

	}

	function checkLoggedInStatus($sessionid)
    {
		
		$userid = $this->app['db']->fetchColumn('SELECT id FROM wcg_employees WHERE sessionid = ?', array($sessionid));

		if ($userid){
			return true;
		}else{
			return false;	
		}
		
    }

	function getVisitsByUserId($userid)
    {
		
		$visits = $this->app['db']->fetchColumn('SELECT visits FROM wcg_employees WHERE id = ?', array($userid));

		return $visits;
		
    }

	function getUserId($sessionid)
    {
		
		$userid = $this->app['db']->fetchColumn('SELECT id FROM wcg_employees WHERE sessionid = ?', array($sessionid));

		return $userid;
		
    }

	function loginUser($username, $password, $sessionid)
    {
		
		$userid = $this->app['db']->fetchColumn('SELECT id FROM wcg_employees WHERE username = ? AND password = ?', array($username, md5($password)));

		if ($userid){
			$return = $this->app['db']->update(
				'wcg_employees',
				array(
					'sessionid' => $sessionid,
					'lastlogin' => date("Y-m-d H:i:s", time()),
					'visits' => $this->getVisitsByUserId($userid) + 1,
				),
				array(
					'id' => $userid
				)
			);
		}else{
			return false;	
		}
	
		return $userid;
		
    }

	function logoutUser($sessionid)
    {
		
		$userid = $this->app['db']->fetchColumn('SELECT id FROM wcg_employees WHERE sessionid = ?', array($sessionid));

		if ($userid){
			$return = $this->app['db']->update(
				'wcg_employees',
				array(
					'sessionid' => '',
				),
				array(
					'id' => $userid
				)
			);
		}else{
			return false;	
		}
	
		return true;	
		
    }

    function login($username, $password)
    {
		$results = $this->app['db']->fetchAssoc('
           SELECT
               id, firstname, lastname, username
           FROM
               employees
           WHERE
               username = ? AND password = ?
		   ', array($username, MD5($password)));

		if ($results){
			$id = $results['id'];
			$this->cookies->set('id',$id);
			if (! $id == ''){
				return $id;
			}else{
				return false;
			}	
		}else{
			return false;
		}
		
		$app['session']->set('user', array('id' => $results['id'], 'supportid' => $results['supportid']));

    }

	function getUserDetails($id)
	{
		$results = $this->app['db']->fetchAssoc('SELECT * FROM wcg_employees WHERE id = ?', array($id));
		return $results;
	}
	
	function getAccMgrName($id)
	{
		$results = $this->app['db']->fetchAssoc('SELECT firstname, lastname FROM wcg_employees WHERE id = ?', array($id));
		return $results['firstname'] . ' ' . $results['lastname'];
	}

	function getAccMgrFirstName($id)
	{
		$results = $this->app['db']->fetchAssoc('SELECT firstname, lastname FROM wcg_employees WHERE id = ?', array($id));
		return $results['firstname'];
	}

	function getListOfEmployees()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM wcg_employees');
		return $results;
	}

	function getListOfEmployeesByDepartment($id)
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM wcg_employees WHERE departmentid = ? AND leaver = 0', array($id));
		return $results;
	}

	function getListOfDepartments()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM wcg_departments');
		return $results;
	}

}

?>