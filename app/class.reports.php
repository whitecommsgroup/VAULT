<?php 


class Reports
{
	
    private $app;

	function __construct($app) { 
	
		$this->app = $app;

	}
	
	function getNumberOfDays($startDate, $endDate, $hoursPerDay="7.5", $excludeToday=false)
	{
		// d/m/Y
		$start = new DateTime(uk_date_to_mysql_date($startDate));
		$end = new DateTime(uk_date_to_mysql_date($endDate));
		$oneday = new DateInterval("P1D");
		$days = array();
	
		/* Iterate from $start up to $end+1 day, one day in each iteration.
		We add one day to the $end date, because the DatePeriod only iterates up to,
		not including, the end date. */
		foreach(new DatePeriod($start, $oneday, $end->add($oneday)) as $day) {
			$day_num = $day->format("N"); /* 'N' number days 1 (mon) to 7 (sun) */
			if($day_num < 6) { /* weekday */
				$days[$day->format("Y-m-d")] = $hoursPerDay;
			} 
		}    
	
		if ($excludeToday)
			array_pop ($days);
	
		return $days;       
	}

    function getClientListReport($managerid,$source,$sectors,$status,$services,$dateFrom,$dateTo)
    {
			
		$type = 1;
		if(!$source){$source=0;}
		if(!$sectors){$sectors=0;}
		if(!$services){$services=0;}
		if(!$status){$status=0;}
		
		$results = $this->app['db']->fetchAll('
			SELECT
				organisation_details.name, organisation.firstcontact, accountmanagerid,
				(SELECT name FROM organisation_states WHERE id = organisation.accountstatusid AND accounttype = type) As accountstatus,
				(SELECT name FROM account_sectors WHERE id = sectorid) As sector,
				(SELECT name FROM organisation_contactstates WHERE id = contactstatusid) As contactstate,
				(SELECT name FROM account_sources WHERE id = sourceid) As source,
				(SELECT name FROM account_sectors WHERE id = sectorid) As sector,
				(SELECT name FROM account_services WHERE id = services) As services,
				(SELECT firstname FROM wcg_employees WHERE id = accountmanagerid) As accountmanager
			FROM
				wcg_crm.organisation
			LEFT JOIN
				organisation_details ON organisation_details.organisationid = organisation.id
			WHERE
				deleted <> 1
			AND
				CASE WHEN ' . $type . '>0 THEN organisation.type = ' . $type . ' ELSE organisation.type IS NOT NULL END
			AND
				CASE WHEN ' . $source . '>0 THEN organisation.sourceid = ' . $source . ' ELSE organisation.sourceid IS NOT NULL END
			AND
				CASE WHEN ' . $sectors . '>0 THEN organisation.sectorid = ' . $sectors . ' ELSE organisation.sectorid IS NOT NULL END
			AND
				CASE WHEN ' . $services . ' >0 THEN organisation.services = ' . $services . ' ELSE organisation.services IS NOT NULL END
			AND
				CASE WHEN ' . $managerid . '>0 THEN organisation.accountmanagerid = ' . $managerid . ' ELSE organisation.accountmanagerid IS NOT NULL END
			AND
				CASE WHEN ' . $status . '>0 THEN organisation.accountstatusid = ' . $status . ' ELSE organisation.accountstatusid IS NOT NULL END
			AND
				CASE WHEN "' . uk_date_to_mysql_date($dateFrom) . '" IS NOT NULL THEN DATE(organisation.firstcontact) >= "' . uk_date_to_mysql_date($dateFrom) . '" ELSE DATE(organisation.firstcontact) = DATE(organisation.firstcontact) END
		   ');

		return $results;
    }

    function getJobsBetweenDate($dateFrom, $dateTo)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               jobs.*, (SELECT name FROM organisation_details WHERE organisation_details.organisationid = jobs.organisationid LIMIT 1) As companyname, firstname, lastname
           FROM
               jobs
		   LEFT JOIN
				wcg_employees 	
		   ON
		   		wcg_employees.id = jobs.accountmanagerid
			WHERE 
				DATE(jobs.date) >= ? AND DATE(jobs.date) <= ? AND jobnumber IS NOT NULL
		   ORDER BY jobnumber DESC
		   ', array(uk_date_to_mysql_date($dateFrom), uk_date_to_mysql_date($dateTo)));

		return $results;

    }
	
    function getEmployeeHoursByCategoryBetweenDate($employeeid, $dateFrom, $dateTo)
    {

		$results = $this->app['db']->fetchAll('
			SELECT
				SUM(hours) As hours, categoryid
			FROM
				jobs_timesheets_lines
			LEFT JOIN
				jobs_timesheets ON jobs_timesheets.id = jobs_timesheets_lines.timesheetid
			LEFT JOIN
				jobs ON jobs_timesheets_lines.jobid = jobs.jobnumber
			WHERE
				jobs_timesheets_lines.employeeid = ? AND DATE(jobs_timesheets.date) >= ? AND DATE(jobs_timesheets.date) <= ?
			GROUP BY 
				categoryid
			ORDER BY
				categoryid ASC
	   ', array($employeeid, uk_date_to_mysql_date($dateFrom), uk_date_to_mysql_date($dateTo)));

		return $results;
    }

    function getEmployeeHoursLastDay()
    {
		
		$interval = 1;
		$day_number = date('N', time());
		if($day_number == 1){ $interval = 3; }

		$results = $this->app['db']->fetchAll('
			SELECT
				 wcg_employees.id, firstname, lastname, SUM(hours) AS hours, timesheetid
			FROM
				jobs_timesheets_lines
			LEFT JOIN
				jobs_timesheets ON jobs_timesheets_lines.timesheetid = jobs_timesheets.id
			LEFT JOIN
				wcg_employees ON jobs_timesheets_lines.employeeid = wcg_employees.id
			WHERE
				DATE(date) = DATE(DATE_SUB(NOW(), INTERVAL ? DAY)) AND WEEKDAY(date) IN (0,1,2,3,4,5)
			GROUP BY
				jobs_timesheets_lines.employeeid
		   ', array($interval));

		return $results;
		
    }
	
    function getEmployeeHoursBetweenDate($dateFrom, $dateTo)
    {

		$results = $this->app['db']->fetchAll('
			SELECT
			   wcg_employees.id, firstname, lastname, SUM(hours) AS hours, (SELECT SUM(days) FROM wcg_holidays WHERE employeeid = wcg_employees.id AND DATE(startdate) >= ? AND DATE(enddate) <= ?) AS days
			FROM
			   jobs_timesheets_lines
			LEFT JOIN
				jobs_timesheets ON jobs_timesheets.id = jobs_timesheets_lines.timesheetid
			LEFT JOIN
				wcg_employees ON jobs_timesheets_lines.employeeid = wcg_employees.id
			WHERE 
				DATE(jobs_timesheets.date) >= ? AND DATE(jobs_timesheets.date) <= ?
			GROUP BY
				wcg_employees.id
		   ', array(uk_date_to_mysql_date($dateFrom), uk_date_to_mysql_date($dateTo), uk_date_to_mysql_date($dateFrom), uk_date_to_mysql_date($dateTo)));

		return $results;
    }

    function getJobSummaryReportCurrent_OLD($dateFrom, $dateTo)
    {

		$results = $this->app['db']->fetchAll('
			SELECT
			   jobnumber, organisationid, title, jobs_invoice_lines.invoicedate, accountmanagerid, jobs_estimated.hours As estHours, jobs_estimated.rate As estRate, SUM(jobs_timesheets_lines.hours) As actHours, AVG(wcg_employees.rate) As actRate, (SELECT IFNULL(jobs_outsourced.valuein,0) FROM jobs_outsourced WHERE jobid = jobs.id GROUP BY jobid) as boughtIn, (SELECT IFNULL(jobs_outsourced.valueout,0) FROM jobs_outsourced WHERE jobid = jobs.id GROUP BY jobid) as boughtOut, SUM(jobs_invoice_lines.invoicevalue) as invoicevalue
			FROM
			   jobs
			LEFT JOIN
				jobs_estimated ON jobs.id = jobs_estimated.jobid
			LEFT JOIN
				jobs_timesheets_lines ON jobs_timesheets_lines.worktypeid = jobs_estimated.worktypeid AND jobs_timesheets_lines.jobid = jobs.jobnumber AND jobs_timesheets_lines.approved = 1
			LEFT JOIN
				wcg_employees ON jobs_timesheets_lines.employeeid = wcg_employees.id
			LEFT JOIN
				jobs_outsourced ON jobs_outsourced.jobid = jobs.id
			LEFT JOIN
				jobs_invoice_lines ON jobs_invoice_lines.jobid = jobs.id
			WHERE 
				DATE(jobs.statusdate) > ? AND DATE(jobs.statusdate) < ? AND jobs.statusid < 2
			GROUP BY
			   jobs.id
		   ', array($dateFrom, $dateTo));

		return $results;
    }

	
    function getJobSummaryReportCurrent($dateFrom, $dateTo)
    {

		$results = $this->app['db']->fetchAll('
			SELECT
			   jobnumber, organisationid, title, jobs.statusdate, accountmanagerid, (SELECT SUM(hours) FROM jobs_estimated WHERE jobs_estimated.jobid = jobs.id) As estHours, jobs_estimated.rate As estRate, (SELECT SUM(hours) FROM jobs_timesheets_lines WHERE jobid = jobnumber) As actHours, (SELECT IFNULL(jobs_outsourced.valuein,0) FROM jobs_outsourced WHERE jobid = jobs.id GROUP BY jobid) as boughtIn, (SELECT IFNULL(jobs_outsourced.valueout,0) FROM jobs_outsourced WHERE jobid = jobs.id GROUP BY jobid) as boughtOut, SUM(jobs_invoice_lines.studiovalue) as invoicevalue
			FROM
			   jobs
			LEFT JOIN
				jobs_estimated ON jobs.id = jobs_estimated.jobid
			LEFT JOIN
				jobs_outsourced ON jobs_outsourced.jobid = jobs.id
			LEFT JOIN
				jobs_invoice_lines ON jobs_invoice_lines.jobid = jobs.id
			WHERE 
				DATE(jobs.date) >= ? AND DATE(jobs.date) <= ? AND jobs.statusid = 1 AND (finalinvoice < 1 OR finalinvoice IS NULL)
			GROUP BY
			   jobs.id
		   ', array(uk_date_to_mysql_date($dateFrom), uk_date_to_mysql_date($dateTo)));

		return $results;
    }

    function getJobSummaryReportInvoiced($dateFrom, $dateTo)
    {

		$results = $this->app['db']->fetchAll('
			SELECT
			   jobnumber, organisationid, title, jobs.statusdate, accountmanagerid, (SELECT SUM(hours) FROM jobs_estimated WHERE jobs_estimated.jobid = jobs.id) As estHours, IFNULL(jobs_estimated.rate,70) As estRate, (SELECT SUM(hours) FROM jobs_timesheets_lines WHERE jobid = jobnumber) As actHours, (SELECT SUM(IFNULL(jobs_outsourced.valuein,0)) FROM jobs_outsourced WHERE jobid = jobs.id GROUP BY jobid) as boughtIn, (SELECT SUM(IFNULL(jobs_outsourced.valueout,0)) FROM jobs_outsourced WHERE jobid = jobs.id GROUP BY jobid) as boughtOut, SUM(jobs_invoice_lines.studiovalue + jobs_invoice_lines.chargeout) as invoicevalue
			FROM
			   jobs
			LEFT JOIN
				jobs_estimated ON jobs.id = jobs_estimated.jobid
			LEFT JOIN
				jobs_outsourced ON jobs_outsourced.jobid = jobs.id
			LEFT JOIN
				jobs_invoice_lines ON jobs_invoice_lines.jobid = jobs.id
			WHERE 
				DATE(jobs.date) >= ? AND DATE(jobs.date) <= ? AND finalinvoice = 1
			GROUP BY
			   jobs.id
		   ', array(uk_date_to_mysql_date($dateFrom), uk_date_to_mysql_date($dateTo)));

		return $results;
    }
	
    function getSingleJobReport($id)
    {

		$results = $this->app['db']->fetchAssoc('
			SELECT
			   jobs.id, jobnumber, organisationid, title, jobs.statusdate, accountmanagerid, jobs_estimated.hours As estHours, jobs_estimated.rate As estRate, (SELECT SUM(hours) FROM jobs_timesheets_lines WHERE jobid = jobnumber) As actHours, (SELECT IFNULL(jobs_outsourced.valuein,0) FROM jobs_outsourced WHERE jobid = jobs.id GROUP BY jobid) as boughtIn, (SELECT IFNULL(jobs_outsourced.valueout,0) FROM jobs_outsourced WHERE jobid = jobs.id GROUP BY jobid) as boughtOut, SUM(jobs_invoice_lines.invoicevalue) as invoicevalue, ((jobs_estimated.rate * jobs_estimated.hours) - (jobs_estimated.rate * (SELECT SUM(hours) FROM jobs_timesheets_lines WHERE jobid = jobnumber))) As margin
			FROM
			   jobs
			LEFT JOIN
				jobs_estimated ON jobs.id = jobs_estimated.jobid
			LEFT JOIN
				jobs_outsourced ON jobs_outsourced.jobid = jobs.id
			LEFT JOIN
				jobs_invoice_lines ON jobs_invoice_lines.jobid = jobs.id
			WHERE
				jobs.id = ?
			GROUP BY
			   jobs.id
	   ', array($id));

		return $results;
    }
	

}

?>