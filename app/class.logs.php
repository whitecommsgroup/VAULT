<?php 


class Logs
{
	
    private $app;
    private $user;

	function __construct($app) { 
	
		$this->app = $app;
		$this->user = $app['session']->get('user');

	}

    function getLogsBetweenDate($dateFrom, $dateTo)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               logs.*, wcg_employees.firstname, wcg_employees.lastname
           FROM
               logs
			LEFT JOIN
				wcg_employees ON employeeid = wcg_employees.id
			WHERE 
				DATE(logs.date) >= ? AND DATE(logs.date) <= ?
		   ORDER BY id DESC
		   ', array(uk_date_to_mysql_date($dateFrom), uk_date_to_mysql_date($dateTo)));

		return $results;

    }

	function addLog($table, $recordid, $action){
		
		//$userid = $this->user['id'];
		
		$userinfo = $this->app['session']->get('user');
		$userid = $userinfo['id'];
		
		$this->app['db']->insert(
			'logs',
			array(
				'tablename' => $table,
				'recordid' => $recordid,
				'action' => $action,
				'employeeid' => $userid,
				'date' => date("Y-m-d H:i:s", time())
			)
		);

		return $this->app['db']->lastInsertId();
		
	}

}

?>