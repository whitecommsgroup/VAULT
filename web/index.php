<?php

date_default_timezone_set('Europe/London');

//load classes
require_once("../vendor/autoload.php");
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once("../app/class.vault.php");

require_once 'PHPWord.php';

// setup Silex
$app = new Silex\Application();
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/templates',
));

//Setup email
$app->register(new Silex\Provider\SwiftmailerServiceProvider(), array(
     'swiftmailer.options' => array(
            'host' => 'localhost',
            'port' => 25,
            'username' => null,
            'password' => null,
            'encryption' => null,
            'auth_mode' => null),
      'swiftmailer.class_path' => __DIR__.'/../vendor/swiftmailer/lib/classes'
));


// setup Vault
$vault = New Vault($app);

// set customer global variables and functions for Twig
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) use ($vault) {

	/*** HELPER ARRAYS ***/

		//add user session array as global

		//timehseets
		$twig->addGlobal('global_worktypes', $vault->timesheets->getWorkTypes());

		//jobs
		$twig->addGlobal('global_ousourcedtypes', $vault->jobs->getOutsourcedTypes());
		$twig->addGlobal('global_jobtypes', $vault->jobs->getJobTypes());
		$twig->addGlobal('global_jobcategories', $vault->jobs->getJobCategories());
		$twig->addGlobal('global_jobstates', $vault->jobs->getJobStates());
		$twig->addGlobal('global_invoicestates', $vault->jobs->getInvoiceStates());
	
		//users
		$twig->addGlobal('user', $app['session']->get('user'));
		$twig->addGlobal('global_employees', $vault->user->getListOfEmployees());
		$twig->addGlobal('global_accountmanagers', $vault->user->getListOfEmployeesByDepartment(1));
		$twig->addGlobal('global_creativestaff', $vault->user->getListOfEmployeesByDepartment(2));
		$twig->addGlobal('global_digitalstaff', $vault->user->getListOfEmployeesByDepartment(3));
	
		//accounts
		$twig->addGlobal('global_accounts', $vault->accounts->getListOfCompaniesByType(1));
		$twig->addGlobal('global_clients', $vault->accounts->getListOfClients());
		$twig->addGlobal('global_sources', $vault->accounts->getListOfSources());
		$twig->addGlobal('global_sectors', $vault->accounts->getListOfSectors());
		$twig->addGlobal('global_months', $vault->accounts->getListOfActionMonths());
		$twig->addGlobal('global_states_clients', $vault->accounts->getListOfStatesByType(1));
		$twig->addGlobal('global_states_suppliers', $vault->accounts->getListOfStatesByType(2));
		$twig->addGlobal('global_contactstates', $vault->accounts->getListOfContactStates());
		

		//tickets
		$twig->addGlobal('global_ticket_states', $vault->tickets->getListOfStates());
		$twig->addGlobal('global_ticket_priorities', $vault->tickets->getListOfPriorities());
		$twig->addGlobal('global_ticket_departments', $vault->tickets->getListOfDepartments());
		$twig->addGlobal('global_ticket_support_agents', $vault->tickets->getListOfSupportAgents());

		//hosting
		$twig->addGlobal('global_hosting_types', $vault->hosting->getHostingTypes());
	
	/*** HELPER FUNCTIONS TO GET NAMES OF THINGS ***/

		//get employee hours split by category between date range
		$function = new Twig_SimpleFunction('getEmployeeHoursByCategoryBetweenDate', function ($employeeid, $datefrom, $dateto) use ($vault) {
			return $vault->reports->getEmployeeHoursByCategoryBetweenDate($employeeid, $datefrom, $dateto);
		});
		$twig->addFunction($function);

		//function to get clients default rate (if exists)
		$function = new Twig_SimpleFunction('getAccountRate', function ($id) use ($vault) {
			return $vault->jobs->getAccountRate($id);
		});
		$twig->addFunction($function);
	
		//function to get acc mgr name
		$function = new Twig_SimpleFunction('getAccMgrName', function ($id) use ($vault) {
			return $vault->user->getAccMgrName($id);
		});
		$twig->addFunction($function);
		
		//function to get company brands
		$function = new Twig_SimpleFunction('getListOfBrandsByCompany', function ($id) use ($vault) {
			return $vault->accounts->getListOfBrandsByCompany($id);
		});
		$twig->addFunction($function);

		//function to get acc mgr name
		$function = new Twig_SimpleFunction('getAccMgrFirstName', function ($id) use ($vault) {
			return $vault->user->getAccMgrFirstName($id);
		});
		$twig->addFunction($function);

		//function to get acc mgr name
		$function = new Twig_SimpleFunction('getCompanyName', function ($id) use ($vault) {
			return $vault->accounts->getCompanyName($id);
		});
		$twig->addFunction($function);

		//function to get services name
		$function = new Twig_SimpleFunction('getServicesName', function ($id) use ($vault) {
			return $vault->accounts->getServicesName($id);
		});
		$twig->addFunction($function);

		//function to get actionmonth name
		$function = new Twig_SimpleFunction('getActionMonthName', function ($id) use ($vault) {
			return $vault->accounts->getActionMonthName($id);
		});
		$twig->addFunction($function);

		//function to get state name
		$function = new Twig_SimpleFunction('getStateName', function ($id) use ($vault) {
			return $vault->accounts->getStateName($id);
		});
		$twig->addFunction($function);

		//function to get sector name
		$function = new Twig_SimpleFunction('getSectorName', function ($id) use ($vault) {
			return $vault->accounts->getSectorName($id);
		});
		$twig->addFunction($function);

		//function to get source name
		$function = new Twig_SimpleFunction('getSourceName', function ($id) use ($vault) {
			return $vault->accounts->getSourceName($id);
		});
		$twig->addFunction($function);
		
		//function to get contact name
		$function = new Twig_SimpleFunction('getContactName', function ($id) use ($vault) {
			return $vault->contacts->getContactName($id);
		});
		$twig->addFunction($function);

		//function to get hosting type name
		$function = new Twig_SimpleFunction('getHostingTypeName', function ($id) use ($vault) {
			return $vault->hosting->getTypeName($id);
		});
		$twig->addFunction($function);

		//function to get job type name
		$function = new Twig_SimpleFunction('getJobTypeName', function ($id) use ($vault) {
			return $vault->jobs->getJobTypeName($id);
		});
		$twig->addFunction($function);

		//function to get job status name
		$function = new Twig_SimpleFunction('getJobStatusName', function ($id) use ($vault) {
			return $vault->jobs->getJobStatusName($id);
		});
		$twig->addFunction($function);

		//function to get job number by id
		$function = new Twig_SimpleFunction('getJobNumber', function ($id) use ($vault) {
			return $vault->jobs->getJobNumber($id);
		});
		$twig->addFunction($function);

		//function to get job number by id
		$function = new Twig_SimpleFunction('getJobIdByNumber', function ($jobnumber) use ($vault) {
			return $vault->jobs->getJobIdByNumber($jobnumber);
		});
		$twig->addFunction($function);

		//function to get job title by id
		$function = new Twig_SimpleFunction('getJobTitle', function ($id) use ($vault) {
			return $vault->jobs->getJobTitle($id);
		});
		$twig->addFunction($function);

		//function to get work type name
		$function = new Twig_SimpleFunction('getWorkTypeName', function ($id) use ($vault) {
			return $vault->jobs->getWorkTypeName($id);
		});
		$twig->addFunction($function);

		//function to get work sub-type name
		$function = new Twig_SimpleFunction('getWorkSubTypeName', function ($id) use ($vault) {
			return $vault->jobs->getWorkSubTypeName($id);
		});
		$twig->addFunction($function);

		//function to get ticket state name
		$function = new Twig_SimpleFunction('getTicketStateName', function ($id) use ($vault) {
			return $vault->tickets->getStateName($id);
		});
		$twig->addFunction($function);

		//function to get ticket priority name
		$function = new Twig_SimpleFunction('getTicketPriorityName', function ($id) use ($vault) {
			return $vault->tickets->getPriorityName($id);
		});
		$twig->addFunction($function);

		//function to get ticket department name
		$function = new Twig_SimpleFunction('getTicketDepartmentName', function ($id) use ($vault) {
			return $vault->tickets->getDepartmentName($id);
		});
		$twig->addFunction($function);

		//function to return array of contacts
		$function = new Twig_SimpleFunction('getContactsByClient', function ($id) use ($vault) {
			return $vault->contacts->getContactsByClient($id);
		});
		$twig->addFunction($function);

		//function to return array of jobs
		$function = new Twig_SimpleFunction('getJobsByClient', function ($id) use ($vault) {
			return $vault->jobs->getJobsByClient($id);
		});
		$twig->addFunction($function);

		//function to return array of jobs
		$function = new Twig_SimpleFunction('getJobMargin', function ($id) use ($vault) {
			$results = $vault->reports->getSingleJobReport($id);
			return $results;
		});
		$twig->addFunction($function);

    return $twig;
}));

/* USER LOGIN SESSION */

$app['session']->set('user', false);

$loggedIn = $vault->user->getUserId(session_id());
	
if($loggedIn){
	$userid = $vault->user->getUserId(session_id());
	$userdetails = $vault->user->getUserDetails($userid);
	$app['session']->set('user', array('sessionid' => session_id(), 'id' => $userdetails['id'], 'group' => $userdetails['groupid'], 'department' => $userdetails['departmentid']));
	$user = $app['session']->get('user');
}else{
	$user = false;

	if (substr($_SERVER["REQUEST_URI"],0,6) != '/login' && substr($_SERVER["REQUEST_URI"],0,4) != '/run'){
		header('Location: /login?route=' . $_SERVER["REQUEST_URI"]);
		exit;
	}
	
}

//******************************************************************************************************************************************//
//                  CRON JOBS			      																							    //
//******************************************************************************************************************************************//

$app->get('/run-daily', function () use ($app, $vault){
	
	$data = $vault->hosting->makeHostingOutstanding();
	$data = $vault->hosting->getHostingExpiringList();
	
	if($data){

		 $body = '';
	
		foreach ($data as $obj_key =>$hosting)
		{
			//echo "$obj_key Book:<br>";
			$body .= '<table border="1" width="500" cellspacing="0" cellpadding="3">';
			//foreach ($book as $key=>$value){
			//	$body .= '<tr><td width="200"><strong>' . $key . '</strong></tdwidth="400"><td>' . $value . '</td></tr>';
			//}
				$body .= '<tr><td width="200"><strong>Client</strong></tdwidth="300"><td>' . $hosting['name'] . '</td></tr>';
				$body .= '<tr><td width="200"><strong>Type</strong></tdwidth="300"><td>' . $hosting['type'] . '</td></tr>';
				$body .= '<tr><td width="200"><strong>Description</strong></tdwidth="300"><td>' . $hosting['description'] . '</td></tr>';
				$body .= '<tr><td width="200"><strong>Period</strong></tdwidth="300"><td>' . $hosting['period'] . ' months</td></tr>';
				$body .= '<tr><td width="200"><strong>Value</strong></tdwidth="300"><td>£' . $hosting['value'] . '</td></tr>';
				$body .= '<tr><td width="200"><strong>Expiry</strong></tdwidth="300"><td>' . $hosting['expirydate'] . '</td></tr>';
				$body .= '<tr><td colspan="2"><a href="http://vault.whitecommsgroup.co.uk/hosting/view/' . $hosting['id'] . '">View in VAULT</td></tr>';
			$body .= '</table>';
			$body .= '<br>';
		}
	
		$subject = 'Hosting Expiry Alerts';
		$from = array('root@server109-228-11-51.live-servers.net' => 'VAULT');
		$to = array('accounts@whitecommsgroup.co.uk', 'simon@whitecommsgroup.co.uk');
		
		$message = \Swift_Message::newInstance()
		->setContentType('text=html')
		->setSubject($subject)
		->setFrom($from)
		->setTo($to)
		->addPart($body, 'text/html');
	
		$return = $app['mailer']->send($message);
		
	}

	return true;

	
});

$app->get('/run-monthly', function () use ($app, $vault){
	
	$jobs = $vault->jobs->getJobsForRenewal();

	$body = '';

	foreach ($jobs as $obj_key =>$values)
	{
		
		//update month and year in title/description
		$prev_month = date("F", mktime(0, 0, 0, date("m", strtotime("-1 months")), 10));
		$curr_month = date("F", mktime(0, 0, 0, date("m", time()), 10));

		$prev_year = date("y", strtotime("-1 years"));
		$curr_year = date("y");

		$new_title = str_replace($prev_year, $curr_year, str_replace($prev_month, $curr_month, $values['title']));
		$new_description = str_replace($prev_year, $curr_year, str_replace($prev_month, $curr_month, $values['description']));
		
		//get new job number and add to vault
		$jobnumber = $vault->jobs->getNextJobNumber();
		$result = $vault->jobs->addJob($jobnumber, $values['organisationid'], $values['brandid'], $values['contactid'], $values['accountmanagerid'], $values['value'], $values['type'], date("d-m-Y", time()), $new_title, $new_description, $values['po'], $values['categoryid'], $values['recurring']);

		//close old job
		$result = $vault->jobs->changeJobStatus($values['id'], 3);		
		
		//write to screen
		$body .= '<table border="1" width="500" cellspacing="0" cellpadding="3">';
			$body .= '<tr><td width="200"><strong>Job Number</strong></tdwidth="300"><td>' . $jobnumber . '</td></tr>';
			$body .= '<tr><td width="200"><strong>Title</strong></tdwidth="300"><td>' . $new_title . '</td></tr>';
			$body .= '<tr><td width="200"><strong>Description</strong></tdwidth="300"><td>' . $new_description . '</td></tr>';
		$body .= '</table>';
		$body .= '<br>';
		
	}

	
	//return $body;
	
	$subject = 'Monthly Jobs Created';
	$from = array('root@server109-228-11-51.live-servers.net' => 'VAULT');
	$to = array('all@whitecommsgroup.co.uk', 'simonridley@hotmail.com');
	
	$message = \Swift_Message::newInstance()
	->setContentType('text=html')
	->setSubject($subject)
	->setFrom($from)
	->setTo($to)
	->addPart($body, 'text/html');

	$return = $app['mailer']->send($message);

	return $return;

	
});


//******************************************************************************************************************************************//
//                  DASHBOARD          																							    		//
//******************************************************************************************************************************************//


$app->get('/', function() use($app, $vault, $loggedIn) {
	
	if(!$loggedIn){
		return $app->redirect('/login?route=/');
	}

	//get user id
	$user = $app['session']->get('user');
	
	return $app['twig']->render('crm.html', array(
		'last5Clients' => $vault->dashboard->getLast5Clients(),
		'last5Prospects' => $vault->dashboard->getLast5Prospects(),
		'last5Jobs' => $vault->dashboard->getLast5Jobs(),
		'birthdays' => $vault->dashboard->getBirthdaysThisMonth(),
		'holidays' => $vault->dashboard->getHolidaysThisMonth(),
		'noticeboard' => $vault->dashboard->getNoticeboard(),
		'jobsvalue' => $vault->dashboard->getJobValueByQuarter(),
		'currentjobsvalue' => $vault->dashboard->getJobValueByMonth(),
		'outstandinghosting' => $vault->hosting->getHostingOutstandingList(),
		'jobswithoutestimate' => $vault->jobs->getJobsWithoutEstimate($user['id']),
		'alljobswithoutestimate' => $vault->jobs->getAllJobsWithoutEstimate(),
		'livejobsoverbudget' => $vault->jobs->getLiveJobsByAmountOverBudgetByEstimated(),
		'wipjobsnearlyoverbudget' => $vault->jobs->getRecentWorkedJobsByAmountNearlyOverBudgetByEstimated(),
		'oldjobsoverbudget' => $vault->jobs->getOldJobsByAmountOverBudgetByEstimated(),
		'jobsoverbyworktype' => $vault->jobs->getLiveJobsByAmountOverBudgetByEstimatedWorkType(),
		'oldjobsoverbyworktype' => $vault->jobs->getOldJobsByAmountOverBudgetByEstimatedWorkType(),
		'employeeHoursLastDay' => $vault->reports->getEmployeeHoursLastDay()
	));
	
});

// *** ADD NOTICE ***/
$app->post('/dashboard/add-notice', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		//get user id
		$user = $app['session']->get('user');

		// run insert
		$result = $vault->dashboard->addNotice($values['notice'], $user['id']);

		return $app->redirect('/');

});

// *** DELETE NOTICE ***/
$app->get('/dashboard/delete-notice/{noticeid}', function ($noticeid) use ($app, $vault) {

		// run delete
		$result = $vault->dashboard->deleteNotice($noticeid);
		return $app->redirect('/');

});

//******************************************************************************************************************************************//
//                  STAFF          																							    			//
//******************************************************************************************************************************************//

// *** ACCOUNTS LIST PAGE ***/
$app->get('/staff/accounts/list', function () use ($app, $vault){
	
	// render template
	return $app['twig']->render('staff-accounts-list.html', array(
		'staff' => $vault->staff->getStaff(),
		'usergroups' => $vault->staff->getUsergroups(),
		'departments' => $vault->staff->getDerpartments()
		
	));
	
});

// *** ACCOUNTS VIEW PAGE ***/
$app->get('/staff/accounts/view/{id}', function ($id) use ($app, $vault){
	
	// render template
	return $app['twig']->render('staff-accounts-view.html', array(
		'employee' => $vault->staff->getEmployeeDetails($id),
		'usergroups' => $vault->staff->getUsergroupsByEmployee($id),
		'departments' => $vault->staff->getDerpartments()
	));
	
});

// *** UPDATE EMPLOYEE DETAILS ***/
$app->post('/staff/accounts/update/{id}', function ($id) use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();
	
	$userGroups = 0;
	foreach ($values['employeeUserGroups'] as &$value) {
		$userGroups = $userGroups + $value;
	}

	// run insert
	$result = $vault->staff->updateEmployeeDetails($id, $values['employeeFirstName'], $values['employeeLastName'], $values['employeeDepartment'], $values['employeeEmail'], $values['employeeUsername'], $userGroups, $values['employeeDOB'] );
	
	if($values['employeePassword']){
		$result = $vault->staff->updateEmployeePassword($id, $values['employeePassword']);
	}

	return $app->redirect('/staff/accounts/view/' . $id);

});


// *** HOLIDAYS LIST PAGE ***/
$app->get('/staff/holidays', function () use ($app, $vault){
	
	// render template
	return $app['twig']->render('holidays-list.html', array(
		'holidays' => $vault->staff->getHolidays(),
		'holidays_staff' => $vault->staff->getStaff()
	));
	
});

// *** ADD HOLIDAY LINE ***/
$app->post('/staff/holidays/add-holiday-line/{id}', function ($id) use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();

	// run insert
	$result = $vault->staff->addHoliday($id, $values['startDate'], $values['endDate'], $values['days']);

	return $app->redirect('/staff/holidays');

});

// *** HOLIDAY DELETE LINE ***/
$app->get('/ajax/delete-holiday-line/{id}', function ($id) use ($app, $vault) {
	$return = $vault->staff->deleteHoliday($id);
	if ($return == 1){
		return $id;
	}else{
		return 0;
	}
});



//******************************************************************************************************************************************//
//                  USER LOGIN 	               																							    //
//******************************************************************************************************************************************//


$app->get('/login/{error}', function ($error) use ($app, $vault){
	
	$errordescription = '';
	if($error){
		$errordescription = $vault->getErrorDescription($error);
	}

	// get querystring
	$values = $app['request']->query->all();

	return $app['twig']->render('/login.html', array(
		'title' => 'Login',
		'error' => $errordescription,
		'route' => $values['route']
	));
	
})->value('error', '');

$app->post('/login', function () use ($app, $vault){
	
	// get form values
	$values = $app['request']->request->all();

	$userid = $vault->user->loginUser($values['username'], $values['password'], session_id());
	
	if($userid){
		$userdetails = $vault->user->getUserDetails($userid);
		$app['session']->set('user', array('sessionid' => session_id(), 'id' => $userdetails['id'], 'group' => $userdetails['groupid'], 'department' => $userdetails['departmentid']));
		return $app->redirect($values['route']);
	}else{
		return $app->redirect('/login/not-found?route=' . $values['route']);
	}
	
});

$app->get('/logout', function () use ($app, $vault, $user){
	
	$return = $vault->user->logoutUser(session_id());
	$app['session']->set('user', false);
	return $app->redirect('/');
	
});


//******************************************************************************************************************************************//
//                  ACCOUNTS  	               																							    //
//******************************************************************************************************************************************//

// *** ACCOUNTS LIST PAGE ***/
$app->get('/accounts/list', function () use ($app, $vault){
	
	// render template
	return $app['twig']->render('accounts-list.html', array(
		'current_clients' => $vault->accounts->getListOfCompaniesByTypeAndStatus(1,1),
		'lapsed_clients' => $vault->accounts->getListOfCompaniesByTypeAndStatus(1,4),
		'prospects' => $vault->accounts->getListOfCompaniesByTypeAndStatus(1,2),
		'suppliers' => $vault->accounts->getListOfCompaniesByType(2),
		'managers' => $vault->listAccountmanagers()
	));
	
});

// *** ACCOUNTS DETAILS PAGE ***/
$app->get('/accounts/view/{id}', function ($id) use ($app, $vault) {
	
	// render template
	return $app['twig']->render('accounts-details.html', array(
		'clientDetails' => $vault->accounts->getClientsDetails($id),
		'contacts' => $vault->contacts->getContactsByClient($id),
		'jobs' => $vault->jobs->getJobsByClient($id),
		'tickets' => $vault->tickets->getTicketsByClient($id),
		'notes' => $vault->notes->getNotesByClient($id),
		'hosting' => $vault->hosting->getHostingByClient($id),
		'brands' => $vault->accounts->getListOfBrandsByCompany($id)
	));
	
});

// *** ACCOUNTS EDIT PAGE - CLIENT ***/
$app->get('/accounts/edit-client/{id}', function ($id) use ($app, $vault) {
	
	// render template
	return $app['twig']->render('accounts-edit-client.html', array(
		'clientDetails' => $vault->accounts->getClientsDetails($id),
		'managers' => $vault->listAccountmanagers()
	));
	
});

// *** ACCOUNTS EDIT PAGE - SUPPLIER ***/
$app->get('/accounts/edit-supplier/{id}', function ($id) use ($app, $vault) {
	
	// render template
	return $app['twig']->render('accounts-edit-supplier.html', array(
		'clientDetails' => $vault->accounts->getClientsDetails($id),
		'managers' => $vault->listAccountmanagers()
	));
	
});

// *** ADD ACCOUNT ***/
$app->post('/accounts/add', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		$companyServices = 0;
		foreach ($values['companyServices'] as &$value) {
			$companyServices = $companyServices + $value;
		}

		// run insert
		$result = $vault->accounts->addAccount($values['companyType'], $values['companyName'], $values['companyPhone'], $values['companyEmail'], $values['companyWebsite'], $values['companyAdd1'], $values['companyAdd2'], $values['companyAdd3'], $values['companyTown'], $values['companyCounty'], $values['companyPostcode'], $values['companyAccountStatus'], $values['companyFirstcontact'], $values['companyAccManager'], $values['companyNextDate'], $values['companySource'], $values['companySector'], $companyServices);

		return $app->redirect('/accounts/view/' . $result);

});

// *** UPDATE ACCOUNT - TO LAPSED - TEMP FOR NICK ***/
$app->get('/accounts/make-lapsed/{id}', function ($id) use ($app, $vault) {

		$result = $vault->accounts->updateClientLapsed($id);

		return $app->redirect('/accounts/list');

});

// *** UPDATE ACCOUNT - CLIENT ***/
$app->post('/accounts/update-client/{id}', function ($id) use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();
		
		$companyServices = 0;
		foreach ($values['companyServices'] as &$value) {
			$companyServices = $companyServices + $value;
		}

		// run insert
		$result = $vault->accounts->updateClient($id, $values['companyName'], $values['companyPhone'], $values['companyEmail'], $values['companyWebsite'], $values['companyAdd1'], $values['companyAdd2'], $values['companyAdd3'], $values['companyTown'], $values['companyCounty'], $values['companyPostcode'], $values['companyAccountStatus'], $values['companyFirstcontact'], $values['companyAccManager'], $values['companyNextDate'], $values['companySource'], $values['companySector'], $companyServices);

		return $app->redirect('/accounts/view/' . $id);

});

// *** UPDATE ACCOUNT - SUPPLIER ***/
$app->post('/accounts/update-supplier/{id}', function ($id) use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->accounts->updateSupplier($id, $values['companyName'], $values['companyPhone'], $values['companyEmail'], $values['companyWebsite'], $values['companyAdd1'], $values['companyAdd2'], $values['companyAdd3'], $values['companyTown'], $values['companyCounty'], $values['companyPostcode'], $values['companyAccountStatus']);

		return $app->redirect('/accounts/view/' . $id);

});

// *** DELETE ACCOUNT ***/
$app->post('/accounts/delete/{accountid}', function ($accountid) use ($app, $vault) {

		// run delete
		$result = $vault->accounts->deleteAccount($accountid);
		return $app->redirect('/accounts/list');

});

// *** ADD BRAND ***/
$app->post('/accounts/add-brand', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->accounts->addBrand($values['brandName'], $values['brandCompany']);

		return $app->redirect('/accounts/view/' . $values['brandCompany']);

});

// *** AJAX ADD BRAND ***/
$app->get('/ajax/accounts/add-brand/{id}/{brandname}', function ($id, $brandname) use ($app, $vault) {
	$return = $vault->accounts->addBrand($brandname, $id);
	if ($return != 0){
		return $return;
	}else{
		return 0;
	}
});


// *** GET BRANDS LIST AS XML ***/
$app->get('/xml/brands/{accountid}', function ($accountid) use ($app, $vault) {

	// render template
	return $app['twig']->render('accounts-brands.xml', array(
		'brands' => $vault->accounts->getListOfBrandsByCompany($accountid)
	));
	
});



//******************************************************************************************************************************************//
//                  PROSPECTS  	               																							    //
//******************************************************************************************************************************************//

// *** PROSPECTS PAGE ***/
$app->get('/prospects', function () use ($app, $vault){

	// get data for page
	$content = $vault->accounts->getListOfProspects();
	
	// render template
	return $app['twig']->render('prospects.html', array(
		'content' => $content
	));
	
});

// *** PROSPECTS - UPDATE ACCOUNT VALUE ***/
$app->post('/prospects/update-account', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->accounts->updateAccountValue($values['pk'], $values['name'], $values['value']);

		return $result;

});


// *** PROSPECTS - UPDATE ACCOUNT DETAILS VALUE ***/
$app->post('/prospects/update-account-details', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->accounts->updateAccountDetailValue($values['pk'], $values['name'], $values['value']);

		return $result;

});

// *** PROSPECTS - UPDATE CONTACT VALUE ***/
$app->post('/prospects/update-contact-details', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->contacts->updateContactDetailValue($values['pk'], $values['name'], $values['value']);

		return $result;

});

// *** PROSPECTS - UPDATE NOTE ***/
$app->post('/prospects/add-note', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();
		
		//get user id
		$user = $app['session']->get('user');

		// run insert
		$result = $vault->notes->addProspectNote($values['pk'], $values['value'], $user['id']);

		return $result;

});




//******************************************************************************************************************************************//
//                  CONTACTS  	               																							    //
//******************************************************************************************************************************************//

// *** CONTACT LIST PAGE ***/
$app->get('/contacts/list', function () use ($app, $vault){
	
	// render template
	return $app['twig']->render('contact-list.html', array(
		'content' => $vault->contacts->getContacts(),
		'clients' => $vault->accounts->getListOfClients()
	));
	
});

// *** CONTACT DETAILS PAGE ***/
$app->get('/contacts/view/{id}', function ($id) use ($app, $vault){

	// render template
	return $app['twig']->render('contact-details.html', array(
		'contactDetails' => $vault->contacts->getContactDetails($id),
		'jobs' => $vault->jobs->getJobsByContact($id),
		'tickets' => $vault->tickets->getTicketsByContact($id),
		'notes' => $vault->notes->getNotesByContact($id)
	));
	
});

// *** EDIT CONTACT ***/
$app->get('/contacts/edit/{id}', function ($id) use ($app, $vault){

	// render template
	return $app['twig']->render('contact-edit.html', array(
		'contactDetails' => $vault->contacts->getContactDetails($id),
		'clients' => $vault->accounts->getListOfClients()
	));
	
});


// *** ADD CONTACT ***/
$app->post('/contacts/add', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->contacts->addContact($values['contactCompany'], $values['contactFirstName'], $values['contactLastName'], $values['contactPosition'], $values['contactPhone'], $values['contactMobile'], $values['contactEmail']);
		

		if(isset($values['save'])){
			return $app->redirect('/contacts/view/' . $result);
		}else{
			return $app->redirect('/accounts/view/' . $values['contactCompany'] . '#_add_contact');
		}



});

// *** AJAX ADD CONTACT ***/
$app->get('/ajax/contacts/add/{id}/{firstname}/{lastname}', function ($id, $firstname, $lastname) use ($app, $vault) {
	$return = $vault->contacts->addContact($id, $firstname, $lastname, '', '', '' ,'');
	if ($return != 1){
		return $return;
	}else{
		return 0;
	}
});

// *** DELETE CONTACT ***/
$app->post('/contacts/delete/{accountid}/{contactid}', function ($accountid, $contactid) use ($app, $vault) {

		// run delete
		$result = $vault->contacts->deleteContact($contactid);
		return $app->redirect('/accounts/view/' . $accountid . '');

});

// *** UPDATE CONTACT ***/
$app->post('/contacts/update/{id}', function ($id) use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->contacts->updateContact($id, $values['contactCompany'], $values['contactFirstName'], $values['contactLastName'], $values['contactPosition'], $values['contactPhone'], $values['contactMobile'], $values['contactEmail']);

		return $app->redirect('/contacts/view/' . $id);

});

// *** GET CONTACTS XML FOR FORMS ***/
$app->get('/xml/contacts/{accountid}', function ($accountid) use ($app, $vault) {

	// render template
	return $app['twig']->render('contacts-account.xml', array(
		'contacts' => $vault->contacts->getContactsByClient($accountid)
	));
	
});



//******************************************************************************************************************************************//
//                  JOBS	               																							    //
//******************************************************************************************************************************************//

// *** JOBS LIST PAGE ***/
$app->get('/jobs/list', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('job-list.html', array(
		'content' => $vault->jobs->getJobs(),
		'clients' => $vault->accounts->getListOfClients()
	));
	
});

// *** JOBS DETAILS PAGE ***/
$app->get('/jobs/view/{id}', function ($id) use ($app, $vault) {
	
	// render template
	return $app['twig']->render('job-details.html', array(
		'content' => $vault->jobs->getJobDetails($id),
		'notes' => $vault->notes->getNotesByJob($id),
		'estimated' => $vault->jobs->getJobTimeEstimated($id),
		'outsourced' => $vault->jobs->getJobOutsourced($id),
		'actual' => $vault->jobs->getJobTimeActual($id),
		'estimated_vs_actual' => $vault->jobs->getJobEstVsActTime($id),
		'estimated_vs_actual_cost' => $vault->jobs->getJobEstVsActCost($id, $vault->jobs->getJobNumber($id)),
		'costanalysis' => $vault->jobs->getJobCostAnalysis($id),
		'invoice_lines' => $vault->jobs->getInvoiceLines($id),
		'invoice_closedmonthlength' => $vault->invoicing->getClosedMonthLength()
	));
	
});

// *** JOBS EDIT PAGE ***/
$app->get('/jobs/edit/{id}', function ($id) use ($app, $vault) {
	
	// render template
	return $app['twig']->render('job-edit.html', array(
		'jobDetails' => $vault->jobs->getJobDetails($id),
		'estimated' => $vault->jobs->getJobTimeEstimated($id)
	));
	
});

// *** JOBS EDIT COSTS PAGE ***/
$app->get('/jobs/edit-costs/{id}', function ($id) use ($app, $vault) {
	
	
	// render template
	return $app['twig']->render('job-edit-costs.html', array(
		'jobDetails' => $vault->jobs->getJobDetails($id),
		'lines' => $vault->jobs->getJobTimeEstimated($id)
	));
	
});

// *** JOBS EDIT OUTSOURCED PAGE ***/
$app->get('/jobs/edit-outsourced/{id}', function ($id) use ($app, $vault) {
	
	
	// render template
	return $app['twig']->render('job-edit-outsourced.html', array(
		'jobDetails' => $vault->jobs->getJobDetails($id),
		'lines' => $vault->jobs->getJobOutsourced($id)
	));
	
});

// *** JOBS STUDIO TIME BREAKDOWN ***/
$app->get('/jobs/studio-breakdown/{jobnumber}/{employeeid}', function ($jobnumber, $employeeid) use ($app, $vault) {
	
	// render template
	return $app['twig']->render('job-studio-breakdown.html', array(
		'content' => $vault->timesheets->getTimeByJobAndEmployee($jobnumber, $employeeid),
		'employeeid' => $employeeid,
	));
	
});


// *** ADD INVOICE LINE ***/
$app->post('/jobs/add-invoice-line/{id}', function ($id) use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();

	// run insert
	$result = $vault->jobs->addInvoiceLine($id, $values['studioValue'], $values['chargeOut'], $values['chargeIn'], $values['note'], $values['month']);

	return $app->redirect('/jobs/view/' . $id . '#_invoices');

});

// *** INVOICE DELETE LINE ***/
$app->get('/ajax/delete-invoice-line/{id}', function ($id) use ($app, $vault) {
	$return = $vault->jobs->deleteInvoiceLine($id);
	if ($return == 1){
		return $id;
	}else{
		return 0;
	}
});

// *** INVOICE UPDATE LINE STATUS ***/
$app->get('/ajax/update-invoice-status/{id}/{statusid}/{final}', function ($id, $statusid, $final) use ($app, $vault) {

	$return = $vault->jobs->changeInvoiceFinal($id, $final);
	$return = $vault->jobs->changeInvoiceStatus($id, $statusid);

	if ($return == 1){
		return $id;
	}else{
		return 0;
	}
	
});

// *** ADD JOB - SPECIAL ***/
$app->post('/jobs/add-special', function () use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();

	// run insert
	$result = $vault->jobs->addJob($values['jobNumber'], $values['jobCompany'], $values['jobBrand'], $values['jobContact'], $values['jobAccManager'], $values['jobValue'], $values['jobType'], $values['jobDate'], $values['jobTitle'], $values['jobDescription'], $values['jobPo'], $values['jobCategory']);

	return $app->redirect('/jobs/list#_add_job_special');

});


// *** ADD JOB ***/
$app->post('/jobs/add', function () use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();

	// get next job number
	$jobnumber = $vault->jobs->getNextJobNumber();

	// run insert
	$result = $vault->jobs->addJob($jobnumber, $values['jobCompany'], $values['jobBrand'], $values['jobContact'], $values['jobAccManager'], $values['jobValue'], $values['jobType'], $values['jobDate'], $values['jobTitle'], $values['jobDescription'], $values['jobPo'], $values['jobCategory']);

	return $app->redirect('/jobs/view/' . $result);

});

// *** UPDATE JOB ***/
$app->post('/jobs/update/{id}', function ($id) use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();

	// run insert
	$result = $vault->jobs->updateJob($id, $values['jobCompany'], $values['jobContact'], $values['jobAccManager'], $values['jobValue'], $values['jobType'], $values['jobDate'], $values['jobTitle'], $values['jobDescription'], $values['jobPo'], $values['jobCategory']);

	return $app->redirect('/jobs/view/' . $id);

});

// *** ADD ESTIMATE ***/
$app->post('/jobs/add-estimate', function () use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();

	// run insert
	$result = $vault->jobs->addEstimate($values['estimateCompany'], $values['jobBrand'], $values['estimateContact'], $values['estimateAccManager'], $values['estimateValue'], $values['estimateType'], $values['estimateDate'], $values['estimateTitle'], $values['estimateDescription'], $values['jobCategory']);

	return $app->redirect('/jobs/view/' . $result);

});

// *** UPDATE ESTIMATE ***/
$app->post('/jobs/update-estimate', function () use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();

	// run insert
	$result = $vault->jobs->updateEstimate($values['jobId'], $values['jobCompany'], $values['jobContact'], $values['jobAccManager'], $values['jobValue'], $values['jobType'], $values['jobDate'], $values['jobTitle'], $values['jobDescription'], $values['jobCategory']);

	return $app->redirect('/jobs/view/' .$values['jobId']);

});

// *** ESTIMATE COSTS UPDATE/ADD ***/
$app->post('/jobs/estimate/update', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();
		
		// run inserts
		  $jobid = $values['jobId'];
		
		for ($i = 0; $i < count($values['timeSheetType']); ++$i) {
			if ($values['timeSheetLineId'][$i] != ''){
				
				$result = $vault->jobs->updateEstimateLine($values['timeSheetLineId'][$i], $jobid, $values['timeSheetType'][$i], $values['timeSheetSubType'][$i], $values['timeSheetHours'][$i], $values['timeSheetRate'][$i], $values['timeSheetValue'][$i]);

			}else{
				
				$result = $vault->jobs->addEstimateLine($jobid, $values['timeSheetType'][$i], $values['timeSheetSubType'][$i], $values['timeSheetHours'][$i], $values['timeSheetRate'][$i], $values['timeSheetValue'][$i]);
				
				
			}
		}
		
		return $app->redirect('/jobs/view/' . $jobid);
	
});

// *** OUTSOURCED COSTS UPDATE/ADD ***/
$app->post('/jobs/outsourced/update', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();
		
		// run inserts
		$jobid = $values['jobId'];
		
		for ($i = 0; $i < count($values['timeSheetType']); ++$i) {
			if ($values['timeSheetLineId'][$i] != ''){
				
				$result = $vault->jobs->updateOutsourcedLine($values['timeSheetLineId'][$i], $jobid, $values['timeSheetType'][$i], $values['timeSheetValueIn'][$i], $values['timeSheetValueOut'][$i], $values['timeSheetPo'][$i]);

			}else{
				
				$result = $vault->jobs->addOutsourcedLine($jobid, $values['timeSheetType'][$i], $values['timeSheetValueIn'][$i], $values['timeSheetValueOut'][$i], $values['timeSheetPo'][$i]);
				
			}
		}
		
		return $app->redirect('/jobs/view/' . $jobid);
	
});


// *** CONVERT ESTIMATE TO JOB ***/
$app->post('/jobs/convert-estimate/{id}', function ($id) use ($app, $vault) {

	// get next job number
	$jobnumber = $vault->jobs->getNextJobNumber();

	// run update
	$result = $vault->jobs->convertEstimateToJob($jobnumber, $id);

	return $app->redirect('/jobs/view/' . $id);

});

// *** CLOSE JOB ***/
$app->post('/jobs/close/{id}', function ($id) use ($app, $vault) {

	// run update
	$result = $vault->jobs->changeJobStatus($id, 3);

	return $app->redirect('/jobs/view/' . $id);

});

// *** CLOSE JOB - TEMP FOR NICK ***/
$app->get('/jobs/close/{id}', function ($id) use ($app, $vault) {

	// run update
	$result = $vault->jobs->changeJobStatus($id, 3);

	return $app->redirect('/jobs/list');

});


// *** ARCHIVE ESTIMATE ***/
$app->post('/jobs/archive/{id}', function ($id) use ($app, $vault) {

	// run update
	$result = $vault->jobs->changeJobStatus($id, 5);

	return $app->redirect('/jobs/view/' . $id);

});

// *** RE-OPEN JOB ***/
$app->post('/jobs/re-open/{id}', function ($id) use ($app, $vault) {

	// run update
	$result = $vault->jobs->changeJobStatus($id, 1);

	return $app->redirect('/jobs/view/' . $id);

});

// *** GET CONTACTS XML FOR FORMS ***/
$app->get('/xml/jobs/{accountid}', function ($accountid) use ($app, $vault) {

	// render template
	return $app['twig']->render('jobs-list.xml', array(
		'jobs' => $vault->jobs->getJobsByClient($accountid)
	));
	
});


// *** CREATE ESTIAMTE DOC ***/
$app->get('/jobs/create-estimate/{id}', function ($id) use ($app, $vault) {

});

//******************************************************************************************************************************************//
//                  INVOICING	               																							    //
//******************************************************************************************************************************************//

// *** INVOICING LIST PAGE ***/
$app->get('/invoicing/list', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('invoice-list.html', array(
		'jobs' => $vault->jobs->getJobs()
	));
	
});


//******************************************************************************************************************************************//
//                  TICKETS  	               																							    //
//******************************************************************************************************************************************//

// *** TICKET LIST PAGE ***/
$app->get('/tickets/list', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('ticket-list.html', array(
		'content' => $vault->tickets->getTickets()
	));
	
});

// *** TICKET DETAILS PAGE ***/
$app->get('/tickets/view/{id}', function ($id) use ($app, $vault) {

	// render template
	return $app['twig']->render('ticket-details.html', array(
		'ticketDetails' => $vault->tickets->getTicketDetails($id),
		'ticketReplies' => $vault->tickets->getTicketReplies($id)
	));
	

});

// *** ADD TICKET ***/
$app->post('/tickets/add', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// create tracking id
		$trackingId = $vault->tickets->createTrackingID();

		// run insert
		$result = $vault->tickets->addTicket($trackingId, $values['ticketSubject'], $values['ticketCompany'], $values['ticketContact'], $values['ticketJob'], $values['ticketDepartment'], $values['ticketAssigned'], $values['ticketStatus'], $values['ticketPriority'], $values['ticketDate']);

		return $app->redirect('/tickets/view/' . $result);

});

// *** EDIT TICKET ***/
$app->get('/tickets/edit/{id}', function ($id) use ($app, $vault) {

	// render template
	return $app['twig']->render('ticket-edit.html', array(
		'ticketDetails' => $vault->tickets->getTicketDetails($id),
		'ticketReplies' => $vault->tickets->getTicketReplies($id)
	));

});

// *** UPDATE TICKET ***/
$app->post('/tickets/update/{id}', function ($id) use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();

	// run insert
		$result = $vault->tickets->updateTicket($id, $values['ticketSubject'], $values['ticketCompany'], $values['ticketContact'], $values['ticketJob'], $values['ticketDepartment'], $values['ticketAssigned'], $values['ticketStatus'], $values['ticketPriority']);

	return $app->redirect('/tickets/view/' . $id);

});



// *** ADD REPLY ***/
$app->post('/tickets/reply/{id}', function ($id) use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();
		
		// get user details
		$user = $app['session']->get('user');

		/* check form */
		var_dump($_FILES);
		move_uploaded_file($_FILES["ticketAttachment"]["tmp_name"],"attachments/" . $_FILES["ticketAttachment"]["name"]);
		$filenames = $_FILES["ticketAttachment"]["name"];

		// run insert
		$result = $vault->tickets->addReply($id, $values['ticketReply'], $user['id']);
		$result = $vault->tickets->addAttachment($result, $filenames);
		
		return $app->redirect('/tickets/view/' . $id);
	
});

// *** CLOSE TICKET ***/
$app->post('/tickets/close/{id}', function ($id) use ($app, $vault) {

	// run update
	$result = $vault->tickets->changeTicketClosed($id, 1);

	return $app->redirect('/tickets/view/' . $id);

});

// *** RE-OPEN TICKET ***/
$app->post('/tickets/re-open/{id}', function ($id) use ($app, $vault) {

	// run update
	$result = $vault->tickets->changeTicketClosed($id, 0);

	return $app->redirect('/tickets/view/' . $id);

});


//******************************************************************************************************************************************//
//                  TIMESHEETS 	               																							    //
//******************************************************************************************************************************************//

// *** TIMESHEETS HOME PAGE ***/
$app->get('/timesheets', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('timesheets.html', array(
	));
	
});

// *** TIMESHEETS HOME PAGE ***/
$app->get('/timesheets/list', function () use ($app, $vault) {

	// get user id
    $user = $app['session']->get('user');
	
	// render template
	return $app['twig']->render('timesheets-list.html', array(
		'timesheets' => $vault->timesheets->getTimesheetsByEmployee($user['id']),
		'timesheetsforapproval' => $vault->timesheets->getTimesheetsForApproval(),
		'archive' => $vault->timesheets->getTimesheets(),
		'staff' => $vault->staff->getStaff()
	));
	
});

// *** TIMESHEETS VIEW ***/
$app->get('/timesheets/view/{id}', function ($id) use ($app, $vault) {

	// render template
	return $app['twig']->render('timesheets-view.html', array(
		'timesheet' => $vault->timesheets->getTimesheetById($id),
		'lines' => $vault->timesheets->getTimesheetLinesByTimesheetId($id)
	));
	
});

// *** TIMESHEETS VIEW ***/
$app->get('/timesheets/approve/{id}', function ($id) use ($app, $vault) {

	// render template
	return $app['twig']->render('timesheets-approve.html', array(
		'timesheet' => $vault->timesheets->getTimesheetById($id),
		'lines' => $vault->timesheets->getTimesheetLinesByTimesheetId($id)
	));
	
});


// *** TIMESHEETS ADD ***/
$app->post('/timesheets/add', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();
		
		
		// run inserts
		$timesheetid = $vault->timesheets->addTimesheet($values['timesheetEmployeeId'], $values['timesheetDate']);
		
		for ($i = 0; $i < count($values['timeSheetJobNumber']); ++$i) {
			$result = $vault->timesheets->addTimeSheetLine($timesheetid, $values['timeSheetHours'][$i], $values['timeSheetJobNumber'][$i], $values['timeSheetType'][$i], $values['timeSheetDescription'][$i], $values['timeSheetImages'][$i], $values['timesheetEmployeeId']);
		}
		
		return $app->redirect('/timesheets/view/' . $timesheetid);
	
});

// *** TIMESHEETS UPDATE ***/
$app->post('/timesheets/update', function () use ($app, $vault) {
	

		// get form values
		$values = $app['request']->request->all();
		
		// run inserts
		$timesheetid = $values['timesheetId'];
		
		$result = $vault->timesheets->updateTimesheet($timesheetid, $values['timesheetDate']);
		
		for ($i = 0; $i < count($values['timeSheetJobNumber']); ++$i) {
			if ($values['timeSheetLineId'][$i] != ''){
				if ($values['timesheetApproved'][$i] == true){
					$approved = 1;
				}else{
					$approved = 0;	
				}
				$result = $vault->timesheets->updateTimeSheetLine($values['timeSheetLineId'][$i], $timesheetid, $values['timeSheetHours'][$i], $values['timeSheetJobNumber'][$i], $values['timeSheetType'][$i], $values['timeSheetDescription'][$i], $values['timeSheetImages'][$i], $values['timesheetEmployeeId'], $values['timesheetApproved'][$i]);
			}else{
				$result = $vault->timesheets->addTimeSheetLine($timesheetid, $values['timeSheetHours'][$i], $values['timeSheetJobNumber'][$i], $values['timeSheetType'][$i], $values['timeSheetDescription'][$i], $values['timeSheetImages'][$i], $values['timesheetEmployeeId']);
				
			}
		}
		
		return $app->redirect($app['request']->headers->get('Referer'));
	
});


// *** TIMESHEET APPROVE LINE ***/
$app->get('/ajax/approveLine/{id}', function ($id) use ($app, $vault) {
		return $vault->timesheets->approveLine($id);
});

// *** TIMESHEET UN-APPROVE LINE ***/
$app->get('/ajax/unApproveLine/{id}', function ($id) use ($app, $vault) {
		return $vault->timesheets->unApproveLine($id);
});

// *** TIMESHEET DELETE LINE ***/
$app->get('/ajax/deleteLine/{id}', function ($id) use ($app, $vault) {
		return $vault->timesheets->deleteLine($id);
});

// *** OUTSOURCED DELETE LINE ***/
$app->get('/ajax/deleteOutsourcedLine/{id}', function ($id) use ($app, $vault) {
		return $vault->jobs->deleteOutsourcedLine($id);
});

// *** ESTIMATE DELETE LINE ***/
$app->get('/ajax/deleteEstimateLine/{id}', function ($id) use ($app, $vault) {
		return $vault->jobs->deleteEstimateLine($id);
});


// *** TIMESHEETS JOBS JSON ***/
$app->get('/json/jobs', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('jobs.json', array(
		'jobs' => $vault->jobs->getJobsByStatus(1)
	));
	
});

// *** TIMESHEETS JOBS XML ***/
$app->get('/xml/jobs', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('jobs.xml', array(
		'jobs' => $vault->jobs->getJobsByStatus(1)
	));
	
});

//******************************************************************************************************************************************//
//                  INVOICING 	               																							    //
//******************************************************************************************************************************************//

// *** INVOICING HOME PAGE ***/
$app->get('/invoicing', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('invoicing.html', array(
	));
	
});

// *** INVOICING REPORT PAGE ***/
$app->post('/invoicing/report/{action}', function ($action) use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();
	
	//open or close if necesarry
	if ($action == 'close'){ 
		$result = $vault->invoicing->setMonthClosed($values['invoicingMonth']);
	}

	if ($action == 'open'){ 
		$result = $vault->invoicing->setMonthOpen($values['invoicingMonth']);
	}
	
	// render template
	return $app['twig']->render('invoicing-report.html', array(
		'date' => $values['invoicingMonth'],
		'accountmanagers' => $vault->invoicing->getJobsForInvoicingCount($values['invoicingMonth']),
		'invoicing' => $vault->invoicing->getJobsForInvoicing($values['invoicingMonth']),
		'month_closed' => $vault->invoicing->checkMonthClosed($values['invoicingMonth'])
	));
	
})->value('action', 'none');

// *** INVOICING UPDATE STATUS ***/
$app->get('/ajax/update-job-status/{jobid}/{statusid}', function ($jobid, $statusid) use ($app, $vault) {
	return $vault->jobs->changeJobStatus($jobid, $statusid);
});

// *** INVOICING UPDATE STATUS ***/
$app->post('/invoicing/update-job-po', function () use ($app, $vault) {
	
		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->jobs->updateJobPO($values['pk'], $values['value']);

		return $result;
	
});

// *** INVOICING UPDATE INVOICE DATE ***/
$app->post('/invoicing/update-invoice-date', function () use ($app, $vault) {
	
		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->invoicing->updateJobInvoiceDate($values['pk'], $values['value']);

		return $result;
	
});

// *** INVOICING UPDATE INVOICE NUMBER ***/
$app->post('/invoicing/update-invoice-number', function () use ($app, $vault) {
	
	// get form values
	$values = $app['request']->request->all();

	// run insert
	$result = $vault->invoicing->updateJobInvoiceNumber($values['pk'], $values['value']);

	return $result;
	
});



//******************************************************************************************************************************************//
//                  INVOICING 	               																							    //
//******************************************************************************************************************************************//

// *** ADD NOTE ***/
$app->post('/notes/add', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();
		
		// get user details
		$user = $app['session']->get('user');

		/* check form */
		print_r($_FILES['noteAttachment']); // for debugging
		
		$file_ary = $vault->reArrayFiles($_FILES['noteAttachment']);

		// run insert
		$noteid = $vault->notes->addNote($values['noteCompany'], $values['noteBody'], 0, $values['noteJob'], $user['id']);
		
		$filenames = '';
		
		foreach ($file_ary as $file) {

			if($file["tmp_name"]){
				move_uploaded_file($file["tmp_name"],"attachments/" . $file["name"]);
				if($filenames == ''){
					$filenames =  $filenames . str_replace('&','and', $file["name"]);
				}else{
					$filenames =  $filenames . ',' . str_replace('&','and', $file["name"]);
				}
			}
			
		}
		
		if ($filenames) {
			$result = $vault->notes->addAttachment($noteid, $filenames);
		}

			
		//return false; 
		
		/*foreach ($_FILES['noteAttachment'] as $file){
			
			move_uploaded_file($file["tmp_name"],"attachments/" . $file["name"]);
			$filename = $file["name"];
	
			// run insert
			$noteid = $vault->notes->addNote($values['noteCompany'], $values['noteBody'], $values['noteContact'], $values['noteJob'], $user['id']);
			
			if ($filename) {
				$result = $vault->notes->addAttachment($noteid, $filename);
			}
			
		}*/
		
		return $app->redirect($app['request']->headers->get('Referer') . '#_notes');
	
});



// *** DELETE NOTE ***/
$app->get('/notes/delete/{id}/{accountid}', function ($id, $accountid) use ($app, $vault) {

		// run delete
		$result = $vault->notes->deleteNote($id);
		return $app->redirect($app['request']->headers->get('Referer') . '#_notes');

});

//******************************************************************************************************************************************//
//                  HOSTING		               																							    //
//******************************************************************************************************************************************//

// *** HOSTING LIST - WEB ***/
$app->get('/hosting', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('hosting-list.html', array(
		'web' => $vault->hosting->getHostingList(2),
		'domains' => $vault->hosting->getHostingList(1),
		'email' => $vault->hosting->getHostingList(3),
		'web_archive' => $vault->hosting->getHostingListDeleted(2),
		'domains_archive' => $vault->hosting->getHostingListDeleted(1),
		'email_archive' => $vault->hosting->getHostingListDeleted(3)
	));
	
});

// *** ADD HOSTING ***/
$app->post('/hosting/add', function () use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->hosting->addHosting($values['hostingCompany'], $values['hostingJob'], $values['hostingDescription'], $values['hostingValue'], $values['hostingExpiryDate'], $values['hostingPeriod'], $values['hostingType']);

		return $app->redirect('/hosting/view/' . $result);

});


// *** HOSTING VIEW ***/
$app->get('/hosting/view/{id}', function ($id) use ($app, $vault) {

	// render template
	return $app['twig']->render('hosting-details.html', array(
		'hosting' => $vault->hosting->getHostingDetails($id)
	));
	
});

// *** HOSTING EDIT PAGE ***/
$app->get('/hosting/edit/{id}', function ($id) use ($app, $vault) {
	
	// render template
	return $app['twig']->render('hosting-edit.html', array(
		'hosting' => $vault->hosting->getHostingDetails($id)
	));
	
});

// *** UPDATE HOSTING ***/
$app->post('/hosting/update/{id}', function ($id) use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->hosting->updateHosting($id, $values['hostingCompany'], $values['hostingJob'], $values['hostingDescription'], $values['hostingValue'], $values['hostingExpiryDate'], $values['hostingPeriod'], $values['hostingType']);

		return $app->redirect('/hosting/view/' . $id);

});

// *** RENEW HOSTING ***/
$app->post('/hosting/renew/{id}', function ($id) use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->hosting->updateHostingExpiryDate($id);

		return $app->redirect('/hosting/view/' . $id);

});

// *** RENEW HOSTING ***/
$app->post('/hosting/delete/{id}', function ($id) use ($app, $vault) {

		// get form values
		$values = $app['request']->request->all();

		// run insert
		$result = $vault->hosting->deleteHosting($id);

		return $app->redirect('/hosting');

});


//******************************************************************************************************************************************//
//                  REPORTS		               																							    //
//******************************************************************************************************************************************//

// *** CLIENT LIST REPORT ***/
$app->get('/reports/client-list', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('report-client-list-start.html', array(

	));
	
});

$app->post('/reports/client-list', function () use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();
	
	$companyServices = 0;
	if(!empty($values['reportServices'])){
		foreach ($values['reportServices'] as &$value) {
			$companyServices = $companyServices + $value;
		}
	}
	
	// render template
	return $app['twig']->render('report-client-list.html', array(
		'table' => $vault->reports->getClientListReport($values['reportAccManager'],$values['reportSource'],$values['reportSector'],$values['reportStatus'],$companyServices,$values['dateFrom'],$values['dateTo']),
	));
	
});

// *** EMPLOYEE UTILISATION REPORT ***/
$app->get('/reports/utilisation', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('report-utilisation-start.html', array(

	));
	
});

$app->post('/reports/utilisation', function () use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();
	
	// calculate time between dates
	$daysArray = $vault->reports->getNumberOfDays($values['dateFrom'],$values['dateTo']);
	
	// render template
	return $app['twig']->render('report-utilisation.html', array(
		'hours' => $vault->reports->getEmployeeHoursBetweenDate($values['dateFrom'],$values['dateTo']),
		'dateFrom' => $values['dateFrom'],
		'dateTo' => $values['dateTo'],
		'total_hours' => array_sum($daysArray),
		'total_days' => count($daysArray)
	));
	
});

// *** JOB SUMMARY REPORT ***/
$app->get('/reports/summary', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('report-summary-start.html', array(

	));
	
});

$app->post('/reports/summary', function () use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();
	
	// render template
	return $app['twig']->render('report-summary.html', array(
		'invoiced' => $vault->reports->getJobSummaryReportInvoiced($values['dateFrom'],$values['dateTo']),
		'current' => $vault->reports->getJobSummaryReportCurrent($values['dateFrom'],$values['dateTo']),
		'dateFrom' => $values['dateFrom'],
		'dateTo' => $values['dateTo']
	));
	
});

$app->post('/reports/summary/export/invoiced', function () use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();
	
	$filename = "summary_invoiced_export_".date("Y_m_d_His").".csv";
	
	$response = new Response($app['twig']->render('report-summary.csv', array(
		'data' => $vault->reports->getJobSummaryReportInvoiced($values['dateFrom'],$values['dateTo']),
		'dateFrom' => $values['dateFrom'],
		'dateTo' => $values['dateTo']
	)));
 
	$response->headers->set('Content-Type', 'text/csv'); 
	$response->headers->set('Content-Disposition', 'attachment; filename='.$filename); 
	
	return $response; 	
	
});

$app->post('/reports/summary/export/current', function () use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();
	
	$filename = "summary_current_export_".date("Y_m_d_His").".csv";
	
	$response = new Response($app['twig']->render('report-summary.csv', array(
		'data' => $vault->reports->getJobSummaryReportCurrent($values['dateFrom'],$values['dateTo']),
		'dateFrom' => $values['dateFrom'],
		'dateTo' => $values['dateTo']
	)));
 
	$response->headers->set('Content-Type', 'text/csv'); 
	$response->headers->set('Content-Disposition', 'attachment; filename='.$filename); 
	
	return $response; 	
	
});


	
// *** JOB LIST REPORT ***/
$app->get('/reports/jobs', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('report-jobs-start.html', array(

	));
	
});


$app->post('/reports/jobs', function () use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();
	
	// render template
	return $app['twig']->render('report-jobs.html', array(
		'jobs' => $vault->reports->getJobsBetweenDate($values['dateFrom'],$values['dateTo']),
		'dateFrom' => $values['dateFrom'],
		'dateTo' => $values['dateTo']
	));
	
});

// *** LOGS REPORT ***/
$app->get('/reports/logs', function () use ($app, $vault) {
	
	// render template
	return $app['twig']->render('report-logs-start.html', array(

	));
	
});


$app->post('/reports/logs', function () use ($app, $vault) {

	// get form values
	$values = $app['request']->request->all();
	
	// render template
	return $app['twig']->render('report-logs.html', array(
		'logs' => $vault->logs->getLogsBetweenDate($values['dateFrom'],$values['dateTo']),
		'dateFrom' => $values['dateFrom'],
		'dateTo' => $values['dateTo']
	));
	
});


$app->run();
