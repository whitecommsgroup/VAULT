<?php 



require_once("class.user.php");
require_once("class.accounts.php");
require_once("class.contacts.php");
require_once("class.tickets.php");
require_once("class.jobs.php");
require_once("class.notes.php");
require_once("class.hosting.php");
require_once("class.timesheets.php");
require_once("class.invoicing.php");
require_once("class.reports.php");
require_once("class.dashboard.php");
require_once("class.staff.php");
require_once("class.logs.php");


class Vault
{
    private $db;
    private $_username;
    private $_password;

    private $app;

	var $accounts;

	function __construct($app) { 
	
		$this->app = $app;

		//set up DB connection
		$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
			'db.options' => array(
					'driver'    => 'pdo_mysql',
					'host'      => '109.228.11.51',
					'dbname'    => 'wcg_crm',
					'user'      => 'wcg_crm_admin',
					'password'  => '!j03th3l10n',
					'charset'   => 'utf8',
			),
		));
		$app['debug'] = true;

		//setup logs sub-classes
		$this->logs = New Logs($app);
		$logs = New Logs($app);
	
		//instantiate all sub-classes
		$this->accounts = New Accounts($app, $logs);	
		$this->contacts = New Contacts($app, $logs);
		$this->tickets = New Tickets($app, $logs);
		$this->jobs = New Jobs($app, $logs);
		$this->notes = New Notes($app, $logs);
		$this->hosting = New Hosting($app, $logs);
		$this->user = New User($app);
		$this->timesheets = New Timesheets($app, $logs);
		$this->invoicing = New Invoicing($app, $logs);
		$this->reports = New Reports($app);
		$this->dashboard = New Dashboard($app, $logs);
		$this->staff = New Staff($app, $logs);

	}

	function reArrayFiles(&$file_post) {
	
		$file_ary = array();
		$file_count = count($file_post['name']);
		$file_keys = array_keys($file_post);
	
		for ($i=0; $i<$file_count; $i++) {
			foreach ($file_keys as $key) {
				$file_ary[$i][$key] = $file_post[$key][$i];
			}
		}
	
		return $file_ary;
	}

	function getErrorName($error)
	{
		return $this->app['db']->fetchColumn('SELECT name FROM system_errors WHERE error = ?', array($error));
	}

	function getErrorDescription($error)
	{
		return $this->app['db']->fetchColumn('SELECT description FROM system_errors WHERE error = ?', array($error));
	}

    function listAccountmanagers()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               *
           FROM
               wcg_employees
           WHERE
               departmentid = 1
		   ');
		return $results;

    }
	
	function getAccMgrName($id)
	{

		$results = $this->app->fetchColumn('SELECT firstname, lastname FROM employees WHERE id = ?', array(1), 0);
		
		return $results['firstname'] . '-' . $results['lastname'];

	}



}

	function uk_date_to_mysql_date($date){
	 
		$date_year=SUBSTR($date,6,4);
		$date_month=SUBSTR($date,3,2);
		$date_day=SUBSTR($date,0,2);
		$date=DATE("Y-m-d", MKTIME(0,0,0,$date_month,$date_day,$date_year));
		return $date; 
 
	} 	


?>