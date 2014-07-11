<?php 


class Jobs
{
	
    private $app;
    private $logs;

	function __construct($app, $logs) { 
	
		$this->app = $app;
		$this->logs = $logs;

	}

    function getRecentWorkedJobsByAmountNearlyOverBudgetByEstimated()
    {
		
		$results = $this->app['db']->fetchAll('
			SELECT DISTINCT
				jobs.id,
				title,
				value,
				accountmanagerid,
				organisationid,
				(SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) AS esthours,
				(SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines LEFT JOIN jobs_timesheets ON jobs_timesheets.id = jobs_timesheets_lines.timesheetid WHERE approved = 1 AND jobid = jobs.jobnumber) AS acthours,
				((SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) - (SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines LEFT JOIN jobs_timesheets ON jobs_timesheets.id = jobs_timesheets_lines.timesheetid WHERE approved = 1 AND jobid = jobs.jobnumber)) As diff, 
				(SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines LEFT JOIN jobs_timesheets ON jobs_timesheets.id = jobs_timesheets_lines.timesheetid WHERE approved = 1 AND jobid = jobs.jobnumber) * 70 AS actvalue,
				(SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) * 70 AS estvalue,
				((SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) - (SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines LEFT JOIN jobs_timesheets ON jobs_timesheets.id = jobs_timesheets_lines.timesheetid WHERE approved = 1 AND jobid = jobs.jobnumber)) * 70 As diffvalue,
				(SELECT jobs_timesheets.date FROM jobs_timesheets_lines LEFT JOIN jobs_timesheets ON jobs_timesheets.id = jobs_timesheets_lines.timesheetid WHERE DATE(jobs_timesheets.date) > DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND approved = 1 AND jobid = jobs.jobnumber LIMIT 1) AS lastworkdate
			FROM
				jobs
			WHERE
				value > 0 AND statusid = 1
			ORDER BY
				diff ASC
		   ');

		return $results;
		
    }

    function getOldJobsByAmountOverBudgetByEstimatedWorkType()
    {
		
		$results = $this->app['db']->fetchAll('
			SELECT
				jobs.id, title, jobs_estimated.worktypeid, jobs_estimated.hours AS estHours, jobs_timesheets_lines.hours AS actHours, (jobs_estimated.hours - jobs_timesheets_lines.hours) AS diffHours, organisationid, accountmanagerid
			FROM
				jobs_estimated
			LEFT JOIN
				jobs ON jobs_estimated.jobid = jobs.id
			LEFT JOIN
				jobs_timesheets_lines ON jobs_timesheets_lines.jobid = jobs.jobnumber
			WHERE
				jobs_estimated.hours IS NOT NULL AND jobs_timesheets_lines.hours IS NOT NULL AND jobs.value > 0 AND statusid = 3
			GROUP BY
				jobs.id, worktypeid
			ORDER BY
				diffHours ASC
		   ');

		return $results;
		
    }


    function getLiveJobsByAmountOverBudgetByEstimatedWorkType()
    {
		
		$results = $this->app['db']->fetchAll('
			SELECT
				jobs.id, title, jobs_estimated.worktypeid, jobs_estimated.hours AS estHours, jobs_timesheets_lines.hours AS actHours, (jobs_estimated.hours - jobs_timesheets_lines.hours) AS diffHours, organisationid, accountmanagerid
			FROM
				jobs_estimated
			LEFT JOIN
				jobs ON jobs_estimated.jobid = jobs.id
			LEFT JOIN
				jobs_timesheets_lines ON jobs_timesheets_lines.jobid = jobs.jobnumber
			WHERE
				jobs_estimated.hours IS NOT NULL AND jobs_timesheets_lines.hours IS NOT NULL AND jobs.value > 0 AND statusid = 1
			GROUP BY
				jobs.id, worktypeid
			ORDER BY
				diffHours ASC
		   ');

		return $results;
		
    }

    function getLiveJobsByAmountOverBudgetByEstimated()
    {
		
		$results = $this->app['db']->fetchAll('
			SELECT DISTINCT
				jobs.id, title, value, accountmanagerid, organisationid, (SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) AS esthours, (SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines WHERE approved = 1 AND jobid = jobs.jobnumber) AS acthours, ((SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) - (SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines WHERE approved = 1 AND jobid = jobs.jobnumber)) As diff, (SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines WHERE approved = 1 AND jobid = jobs.jobnumber) * 70 AS actvalue, (SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) * 70 AS estvalue, ((SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) - (SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines WHERE approved = 1 AND jobid = jobs.jobnumber)) * 70 As diffvalue
			FROM
				jobs
			WHERE
				value > 0 AND statusid = 1
			ORDER BY
				diff ASC
		   ');

		return $results;
		
    }

    function getOldJobsByAmountOverBudgetByEstimated()
    {
		
		$results = $this->app['db']->fetchAll('
			SELECT DISTINCT
				jobs.id, title, value, accountmanagerid, organisationid, (SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) AS esthours, (SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines WHERE approved = 1 AND jobid = jobs.jobnumber) AS acthours, ((SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) - (SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines WHERE approved = 1 AND jobid = jobs.jobnumber)) As diff, (SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines WHERE approved = 1 AND jobid = jobs.jobnumber) * 70 AS actvalue, (SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) * 70 AS estvalue, ((SELECT SUM(jobs_estimated.hours) FROM jobs_estimated WHERE jobid = jobs.id) - (SELECT SUM(jobs_timesheets_lines.hours) FROM jobs_timesheets_lines WHERE approved = 1 AND jobid = jobs.jobnumber)) * 70 As diffvalue
			FROM
				jobs
			WHERE
				value > 0 AND statusid = 3
			ORDER BY
				diff ASC
		   ');

		return $results;
		
    }

    function getLiveJobsByAmountOverBudget()
    {
		
		$results = $this->app['db']->fetchAll('
			SELECT DISTINCT
				jobs.id, title, jobs.value, SUM(jobs_timesheets_lines.hours) AS acthours, SUM(jobs_timesheets_lines.hours)*70 AS actvalue, (jobs.value - SUM(jobs_timesheets_lines.hours)*70) AS diff, accountmanagerid, organisationid
			FROM
				jobs
			LEFT JOIN
				jobs_timesheets_lines on jobs_timesheets_lines.jobid = jobs.jobnumber
			WHERE
				jobs_timesheets_lines.hours IS NOT NULL AND value > 0 AND statusid = 1
			GROUP BY
				jobs.id
			ORDER BY
				diff ASC
		   ');

		return $results;
		
    }
	
    function getAllJobsByAmountOverBudget()
    {
		
		$results = $this->app['db']->fetchAll('
			SELECT DISTINCT
				jobs.id, title, jobs.value, SUM(jobs_timesheets_lines.hours) AS acthours, SUM(jobs_timesheets_lines.hours)*70 AS actvalue, (jobs.value - SUM(jobs_timesheets_lines.hours)*70) AS diff
			FROM
				jobs
			LEFT JOIN
				jobs_timesheets_lines on jobs_timesheets_lines.jobid = jobs.jobnumber
			WHERE
				jobs_timesheets_lines.hours IS NOT NULL AND value > 0 AND statusid = 1
			GROUP BY
				jobs.id
			ORDER BY
				diff ASC
		   ');

		return $results;
		
    }

    function getJobsForRenewal()
    {
		
		$results = $this->app['db']->fetchAll('
           SELECT
               *
           FROM
               jobs
		   WHERE 
               recurring = 1 AND MONTH(jobs.date) < MONTH(NOW()) AND statusid = 1
		   ');

		return $results;
		
    }

    function getJobsWithoutEstimate($accountmanagerid)
    {
		
		$results = $this->app['db']->fetchAll('
			SELECT DISTINCT
				jobs.id, jobs.jobnumber, title, accountmanagerid, organisationid
			FROM
				wcg_crm.jobs
			LEFT JOIN
				jobs_estimated ON jobs_estimated.jobid = jobs.id
			LEFT JOIN 
				jobs_timesheets_lines ON jobs_timesheets_lines.jobid = jobs.jobnumber
			WHERE
				jobs_timesheets_lines.hours IS NOT NULL AND jobs_estimated.hours IS NULL AND accountmanagerid = ? AND organisationid <> 2517 AND categoryid = 3 AND date > "2014/04/01"
			GROUP BY jobnumber
			ORDER BY date DESC
	   ', array($accountmanagerid));

		return $results;
		
    }

    function getAllJobsWithoutEstimate()
    {
		
		$results = $this->app['db']->fetchAll('
			SELECT DISTINCT
				jobs.id, jobs.jobnumber, title, accountmanagerid, organisationid
			FROM
				wcg_crm.jobs
			LEFT JOIN
				jobs_estimated ON jobs_estimated.jobid = jobs.id
			LEFT JOIN 
				jobs_timesheets_lines ON jobs_timesheets_lines.jobid = jobs.jobnumber
			WHERE
				jobs_timesheets_lines.hours IS NOT NULL AND jobs_estimated.hours IS NULL AND organisationid <> 2517 AND categoryid = 3 AND date > "2014/04/01"
			GROUP BY jobnumber
			ORDER BY date DESC
	   ');

		return $results;
		
    }

    function getJobDetails($id)
    {
		$results = $this->app['db']->fetchAssoc('
           SELECT
               jobs.*, (SELECT name FROM organisation_details WHERE organisation_details.organisationid = jobs.organisationid LIMIT 1) As companyname
           FROM
               jobs
		   WHERE 
               id = ?
		   ', array($id));

		return $results;
    }

	function addInvoiceLine($jobid, $studioValue, $chargeOut, $chargeIn, $note, $invoicemonth){
		   
		$this->app['db']->insert(
			'jobs_invoice_lines',
			array(
				'jobid' => $jobid,
				'studiovalue' => $studioValue,
				'chargein' => $chargeIn,
				'chargeout' => $chargeOut,
				'note' => $note,
				'statusid' => 1,
				'statusdate' => date("Y-m-d H:i:s", time()),
				'invoicemonth' => substr($invoicemonth,0,2),
				'invoiceyear' => substr($invoicemonth,3,4)
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('jobs_invoice_lines', $return, 'insert');
		
		return $return;

	}

    function deleteInvoiceLine($id){
		   
	  $return = $this->app['db']->delete(
			'jobs_invoice_lines',
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('jobs_invoice_lines', $id, 'delete');

		return $return;
	}

	function changeInvoiceStatus($id, $statusid){
		   
		  $return = $this->app['db']->update(
			'jobs_invoice_lines',
			array(
				'statusid' => $statusid,
				'statusdate' => date("Y-m-d H:i:s", time())
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('jobs_invoice_lines', $id, 'update');

		return $return;

	}
	
	function changeInvoiceFinal($id, $final){
		   
		  $return = $this->app['db']->update(
			'jobs_invoice_lines',
			array(
				'finalinvoice' => $final
			),
			array(
				'id' => $id
			)
		);

		return $return;

	}

	function updateJobPO($jobId, $po){
		   
		  $return = $this->app['db']->update(
			'jobs',
			array(
				'po' => $po,
			),
			array(
				'id' => $jobId
			)
		);

		$this->logs->addLog('jobs', $jobId, 'update PO');

		return $return;

	}

	function convertEstimateToJob($jobNumber, $jobId){
		   
		  $return = $this->app['db']->update(
			'jobs',
			array(
				'jobnumber' => $jobNumber,
				'description' => $jobDescription,
				'statusid' => 1
			),
			array(
				'id' => $jobId
			)
		);

		$this->logs->addLog('jobs', $jobId, 'update estimate to job');

		return $return;

	}

	function changeJobStatus($jobId, $statusid){
		   
		  $return = $this->app['db']->update(
			'jobs',
			array(
				'statusid' => $statusid,
				'statusdate' => date("Y-m-d H:i:s", time())
			),
			array(
				'id' => $jobId
			)
		);

		$this->logs->addLog('jobs', $jobId, 'update status');

		return $return;

	}

	function addJob($jobNumber, $jobCompany, $jobBrand, $jobContact, $jobAccManager, $jobValue, $jobType, $jobDate, $jobTitle, $jobDescription, $po, $jobCategory, $recurring = 0){
		   
		$this->app['db']->insert(
			'jobs',
			array(
				'jobnumber' => $jobNumber,
				'organisationid' => $jobCompany,
				'brandid' => $jobBrand,
				'contactid' => $jobContact,
				'accountmanagerid' => $jobAccManager,
				'value' => $jobValue,
				'type' => $jobType,
				'date' => uk_date_to_mysql_date($jobDate),
				'title' => $jobTitle,
				'description' => $jobDescription,
				'po' => $po,
				'statusid' => 1,
				'categoryid' => $jobCategory,
				'recurring' => $recurring
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('jobs', $return, 'insert');
		
		return $return;

	}

	function addEstimate($jobCompany, $jobBrand, $jobContact, $jobAccManager, $jobValue, $jobType, $jobDate, $jobTitle, $jobDescription, $jobCategory){
		   
		$this->app['db']->insert(
			'jobs',
			array(
				'organisationid' => $jobCompany,
				'brandid' => $jobBrand,
				'contactid' => $jobContact,
				'accountmanagerid' => $jobAccManager,
				'value' => $jobValue,
				'type' => $jobType,
				'date' => uk_date_to_mysql_date($jobDate),
				'title' => $jobTitle,
				'description' => $jobDescription,
				'statusid' => 2,
				'categoryid' => $jobCategory
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('jobs', $return, 'insert estimate');
		
		return $return;

	}

	function updateJob($jobId, $jobCompany, $jobContact, $jobAccManager, $jobValue, $jobType, $jobDate, $jobTitle, $jobDescription, $po, $jobCategory){
		   
		  $return = $this->app['db']->update(
			'jobs',
			array(
				'organisationid' => $jobCompany,
				'contactid' => $jobContact,
				'accountmanagerid' => $jobAccManager,
				'value' => $jobValue,
				'type' => $jobType,
				'date' => uk_date_to_mysql_date($jobDate),
				'title' => $jobTitle,
				'description' => $jobDescription,
				'po' => $po,
				'categoryid' => $jobCategory
			),
			array(
				'id' => $jobId
			)
		);

		$this->logs->addLog('jobs', $jobId, 'update');

		return $return;

	}
	
	
	
	function updateEstimate($jobId, $jobCompany, $jobContact, $jobAccManager, $jobValue, $jobType, $jobDate, $jobTitle, $jobDescription){
		   
		  $return = $this->app['db']->update(
			'jobs',
			array(
				'organisationid' => $jobCompany,
				'contactid' => $jobContact,
				'accountmanagerid' => $jobAccManager,
				'value' => $jobValue,
				'type' => $jobType,
				'date' => date("Y-m-d H:i:s", strtotime($jobDate)),
				'title' => $jobTitle,
				'description' => $jobDescription,
				'categoryid' => $jobCategory
			),
			array(
				'id' => $jobId
			)
		);

		$this->logs->addLog('jobs', $jobId, 'update estimate');

		return $return;

	}

	function addEstimateLine($jobid, $worktypeid, $worksubtypeid, $hours, $rate, $value){
		   
		$this->app['db']->insert(
			'jobs_estimated',
			array(
				'jobid' => $jobid,
				'worktypeid' => $worktypeid,
				'worksubtypeid' => $worksubtypeid,
				'hours' => $hours,
				'rate' => $rate,
				'value' => $value
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('jobs_estimated', $return, 'insert');

		return $return;
	}

	function updateEstimateLine($id, $jobid, $worktypeid, $worksubtypeid, $hours, $rate, $value){
		   
		  $return = $this->app['db']->update(
			'jobs_estimated',
			array(
				'jobid' => $jobid,
				'worktypeid' => $worktypeid,
				'worksubtypeid' => $worksubtypeid,
				'hours' => $hours,
				'rate' => $rate,
				'value' => $value
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('jobs_estimated', $id, 'update');

		return $return;
	}

    function deleteEstimateLine($id){
		   
		  $return = $this->app['db']->delete(
			'jobs_estimated',
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('jobs_estimated', $id, 'delete');

		return $return;
	}
	
/*	function getJobTime($id)
    {
		$results = $this->app['db']->fetchAll('
		   SELECT
               SUM(hours) AS hours, SUM(rate) As rate, (SELECT name FROM jobs_worktypes WHERE id = worktypeid) As worktypename, (SELECT name FROM jobs_worksubtypes WHERE id = worksubtypeid) As worksubtypename
           FROM
               jobs_timesheets_lines
	 	   LEFT JOIN
		   		jobs_worktypes ON jobs_worktypes.id = jobs_timesheets_lines.worktypeid
		   WHERE 
               jobid = ? AND approved = 1
		   ', array($id));

		return $results;
    }*/

    function getInvoiceLines($id)
    {
		$results = $this->app['db']->fetchAll('

		SELECT
               jobs_invoice_lines.*, jobs_invoice_states.name
           FROM
               jobs_invoice_lines
	 	   LEFT JOIN
		   		jobs_invoice_states ON jobs_invoice_states.id = jobs_invoice_lines.statusid
		   WHERE 
               jobid = ?
		   ORDER BY 
               id
		   ', array($id));

		return $results;
    }

    function getSummaryHoursByTypeAndEmployee($id)
    {
		$results = $this->app['db']->fetchAll('

		SELECT
               employeeid, worktypeid, SUM(jobs_timesheets_lines.hours), SUM(wcg_employees.rate), (SUM(jobs_timesheets_lines.hours) * SUM(wcg_employees.rate)) As value
           FROM
               jobs_timesheets_lines
	 	   LEFT JOIN
		   		wcg_employees ON jobs_timesheets_lines.employeeid = wcg_employees.id
		   WHERE 
               jobid = ?
		GROUP BY
			worktypeid, employeeid
		   ', array($id));

		return $results;
    }

    function getJobEstVsActCost($id, $jobnumber)
    {
	/*	$results = $this->app['db']->fetchAll('
			
			DROP TABLE IF EXISTS temp_timesheets;
			CREATE TEMPORARY TABLE temp_timesheets ( SELECT worktypeid, SUM(hours) AS actHours
			FROM jobs_worktypes
			RIGHT JOIN jobs_timesheets_lines ON jobs_timesheets_lines.worktypeid = jobs_worktypes.id
			WHERE jobs_timesheets_lines.jobid = 16336
			GROUP BY jobs_worktypes.id );
			
			DROP TABLE IF EXISTS temp_est;
			CREATE TEMPORARY TABLE temp_est ( SELECT worktypeid, SUM(hours) AS estHours, rate
			FROM jobs_worktypes
			RIGHT JOIN jobs_estimated ON jobs_estimated.worktypeid = jobs_worktypes.id
			WHERE jobs_estimated.jobid = 627
			GROUP BY jobs_worktypes.id );
			
			SELECT jobs_worktypes.id, jobs_worktypes.name, estHours, actHours, IFNULL(rate,70) AS rate
			FROM jobs_worktypes
			LEFT JOIN temp_est ON temp_est.worktypeid = jobs_worktypes.id
			LEFT JOIN temp_timesheets ON temp_timesheets.worktypeid = jobs_worktypes.id


		');*/
		
			if($id && $jobnumber){
	
			
				$statement = $this->app['db']->prepare('
				
					CREATE TEMPORARY TABLE temp_timesheets ( SELECT worktypeid, SUM(hours) AS actHours
					FROM jobs_worktypes
					RIGHT JOIN jobs_timesheets_lines ON jobs_timesheets_lines.worktypeid = jobs_worktypes.id
					WHERE jobs_timesheets_lines.jobid = ' . $jobnumber . ' AND approved = 1
					GROUP BY jobs_worktypes.id );
				
				');
				
				$statement->execute();
	
				$statement = $this->app['db']->prepare('
				
					CREATE TEMPORARY TABLE temp_est ( SELECT worktypeid, SUM(hours) AS estHours, rate
					FROM jobs_worktypes
					RIGHT JOIN jobs_estimated ON jobs_estimated.worktypeid = jobs_worktypes.id
					WHERE jobs_estimated.jobid = ' . $id . '
					GROUP BY jobs_worktypes.id );
				
				');
				
				$statement->execute();
				
				$statement = $this->app['db']->prepare('
				
					SELECT jobs_worktypes.id AS worktypeid, jobs_worktypes.name, estHours, actHours, IFNULL(rate,70) AS estRate
					FROM jobs_worktypes
					LEFT JOIN temp_est ON temp_est.worktypeid = jobs_worktypes.id
					LEFT JOIN temp_timesheets ON temp_timesheets.worktypeid = jobs_worktypes.id
				
				');
	
				$statement->execute();
				
				$result = $statement->fetchAll();
	
				return $result;
	
			}

			return false;
			

    }

    function getJobEstVsActCost_workswithestimatedonly($id)
    {
		$results = $this->app['db']->fetchAll('
			SELECT
               jobs_estimated.worktypeid, jobs_estimated.hours As estHours, jobs_estimated.rate As estRate, (SELECT SUM(hours) FROM jobs_timesheets_lines WHERE jobid = jobs.jobnumber AND approved = 1 AND jobs_timesheets_lines.worktypeid = jobs_estimated.worktypeid) As actHours
           FROM
               jobs_estimated
		   LEFT JOIN
				jobs ON jobs.id = jobs_estimated.jobid
		   WHERE
		        jobs_estimated.jobid = ?
		   GROUP BY
		   	   worktypeid
		', array($id));

		return $results;
    }

    function old_getJobEstVsActCost($id)
    {
		$results = $this->app['db']->fetchAll('
			SELECT
               jobs_estimated.worktypeid, jobs_estimated.hours As estHours, jobs_estimated.rate As estRate, SUM(jobs_timesheets_lines.hours) As actHours, AVG(wcg_employees.rate) As actRate
           FROM
               jobs_estimated
		   LEFT JOIN
				jobs ON jobs.id = jobs_estimated.jobid
	 	   LEFT JOIN
		   		jobs_timesheets_lines ON jobs_timesheets_lines.worktypeid = jobs_estimated.worktypeid AND jobs_timesheets_lines.jobid = jobs.jobnumber AND jobs_timesheets_lines.approved = 1
	 	   LEFT JOIN
		   		wcg_employees ON jobs_timesheets_lines.employeeid = wcg_employees.id
		   WHERE
		        jobs_estimated.jobid = ?
		   GROUP BY
		   	   worktypeid
		', array($id));

		return $results;
    }
			
			
    function getJobEstVsActTime($id)
    {
		$results = $this->app['db']->fetchAll('
			SELECT
               jobs_estimated.worktypeid, jobs_estimated.hours As estHours, (SELECT SUM(hours) FROM jobs_timesheets_lines WHERE jobid = jobs.jobnumber AND approved = 1 AND jobs_timesheets_lines.worktypeid = jobs_estimated.worktypeid) As actHours
           FROM
               jobs
		   JOIN
				jobs_estimated ON jobs.id = jobs_estimated.jobid
			
		   WHERE
		        jobs.id = ?
			GROUP BY jobs_estimated.worktypeid
		   ', array($id));

		return $results;
    }

    function old_getJobEstVsActTime($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               jobs_estimated.worktypeid, jobs_estimated.hours As estHours, SUM(jobs_timesheets_lines.hours) As actHours
           FROM
               jobs_estimated
		   LEFT JOIN
				jobs ON jobs.id = jobs_estimated.jobid
	 	   LEFT JOIN
		   		jobs_timesheets_lines ON jobs_timesheets_lines.worktypeid = jobs_estimated.worktypeid AND jobs_timesheets_lines.jobid = jobs.jobnumber AND jobs_timesheets_lines.approved = 1
		   WHERE
		        jobs_estimated.jobid = ?
		   GROUP BY
		   	   worktypeid
		   ', array($id));

		return $results;
    }

    function getJobTimeActual($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               jobs_timesheets_lines.worktypeid, jobs_timesheets_lines.worksubtypeid, jobs_timesheets_lines.employeeid, SUM(jobs_timesheets_lines.hours) As hours, wcg_employees.rate, (SUM(jobs_timesheets_lines.hours) * wcg_employees.rate) As value
           FROM
               jobs
	 	   LEFT JOIN
		   		jobs_timesheets_lines ON jobs_timesheets_lines.jobid = jobs.jobnumber AND jobs_timesheets_lines.approved = 1
	 	   LEFT JOIN
		   		wcg_employees ON jobs_timesheets_lines.employeeid = wcg_employees.id
		   WHERE 
               jobs.id = ?
		   GROUP BY
		   	   employeeid
		   ORDER BY
		   	   worktypeid
		   ', array($id));

		return $results;
    }


    function getJobTimeEstimated($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               jobs_estimated.*, (hours * rate) As value
           FROM
               jobs_estimated
		   WHERE 
               jobid = ?
		   ', array($id));

		return $results;
    }

	function addOutsourcedLine($jobid, $outsourcedtypeid, $valueIn, $valueOut, $po){
		   
		$this->app['db']->insert(
			'jobs_outsourced',
			array(
				'jobid' => $jobid,
				'outsourcetypeid' => $outsourcedtypeid,
				'valuein' => $valueIn,
				'valueout' => $valueOut,
				'po' => $po
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('jobs_outsourced', $return, 'insert');
		
		return $return;

	}

	function updateOutsourcedLine($id, $jobid, $outsourcetypeid, $valueIn, $valueOut, $po){
		   
		  $return = $this->app['db']->update(
			'jobs_outsourced',
			array(
				'jobid' => $jobid,
				'outsourcetypeid' => $outsourcetypeid,
				'valuein' => $valueIn,
				'valueout' => $valueOut,
				'po' => $po
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('jobs_outsourced', $id, 'insert');

		return $return;
	}

    function deleteOutsourcedLine($id){
		   
		  $return = $this->app['db']->delete(
			'jobs_outsourced',
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('jobs_outsourced', $id, 'delete');

		return $return;
	}

    function getJobOutsourced($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               jobs_outsourced.*, jobs_outsourcedtypes.name
           FROM
               jobs_outsourced
           LEFT JOIN
               jobs_outsourcedtypes ON jobs_outsourcedtypes.id = jobs_outsourced.outsourcetypeid
		   WHERE 
               jobid = ?
		   ', array($id));

		return $results;
    }
	
    function getJobCostAnalysis($id)
    {
		$results = $this->app['db']->fetchAssoc('
           SELECT
				(SELECT SUM(jobs_estimated.hours *  jobs_estimated.rate) FROM jobs_estimated WHERE jobid = jobs.id) As estStudio,
				(SELECT IFNULL(AVG(jobs_estimated.rate),70) FROM jobs_estimated WHERE jobid = jobs.id) As estRate,
				(SELECT (SUM(jobs_timesheets_lines.hours)) AS actCost FROM jobs_timesheets_lines LEFT JOIN jobs ON jobs.jobnumber = jobs_timesheets_lines.jobid WHERE jobs.id = ? AND approved = 1 ) As actStudio,
				(SELECT SUM(valueout) FROM jobs_outsourced WHERE jobid = jobs.id) As chargedOut,
				(SELECT (SUM(studiovalue) + SUM(chargeout)) FROM jobs_invoice_lines WHERE jobid = jobs.id) As totalinvoicedvalue,
				(SELECT (SUM(studiovalue)) FROM jobs_invoice_lines WHERE jobid = jobs.id) As totalstudiovalue,
				(SELECT (SUM(chargeout)) FROM jobs_invoice_lines WHERE jobid = jobs.id) As totalchargeoutvalue
			FROM  
				jobs
			WHERE 
				id = ?
		   ', array($id, $id));

		return $results;
    }

    function getJobsByStatus($id)
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
               statusid = ?
		   ORDER BY id DESC
		   ', array($id));

		return $results;

    }

    function getJobs()
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
		   ORDER BY jobnumber DESC
		   ');

		return $results;

    }

    function getJobsByClient($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               *
           FROM
               jobs
           WHERE
               organisationid = ?
		   ORDER BY jobnumber DESC
		   ', array($id));
		
		return $results;

    }

    function getJobsByContact($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               *
           FROM
               jobs
           WHERE
               contactid = ?
		   ', array($id));
		

		return $results;

    }

    function getJobMargin($id)
    {

		$results = $this->app['db']->fetchAssoc('
				SELECT
				  ((jobs_estimated.rate * jobs_estimated.hours) - (jobs_estimated.rate * (SELECT SUM(hours) FROM jobs_timesheets_lines WHERE jobid = jobnumber))) As margin
				FROM
				   jobs
				LEFT JOIN
					jobs_estimated ON jobs.id = jobs_estimated.jobid
				LEFT JOIN
					jobs_outsourced ON jobs_outsourced.jobid = jobs.id
				LEFT JOIN
					jobs_invoice_lines ON jobs_invoice_lines.jobid = jobs.id
				WHERE jobs.id = ?
				GROUP BY
				   jobs.id
	   ', array($id));

		return $results;

//				  (jobs_estimated.rate * jobs_estimated.hours) As estCost, (jobs_estimated.rate * (SELECT SUM(hours) FROM jobs_timesheets_lines WHERE jobid = jobnumber)) As actCost, ((jobs_estimated.rate * jobs_estimated.hours) - (jobs_estimated.rate * (SELECT SUM(hours) FROM jobs_timesheets_lines WHERE jobid = jobnumber))) As margin


    }

    function getOutsourcedTypes()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               *
           FROM
               jobs_outsourcedtypes
		   ');
		
		return $results;

    }

	function getJobStates()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM jobs_states ORDER BY id');
		return $results;
	}

	function getInvoiceStates()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM jobs_invoice_states ORDER BY id');
		return $results;
	}

	function getJobTypes()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM jobs_types');
		return $results;
	}

	function getJobCategories()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM jobs_categories');
		return $results;
	}

	function getJobTypeName($id)
	{
		
		return $this->app['db']->fetchColumn('SELECT name FROM jobs_types WHERE id = ?', array($id), 0);

	}

	function getJobStatusName($id)
	{
		
		return $this->app['db']->fetchColumn('SELECT name FROM jobs_states WHERE id = ?', array($id), 0);

	}

	function getJobNumber($id)
	{
		
		return $this->app['db']->fetchColumn('SELECT jobnumber FROM jobs WHERE id = ?', array($id), 0);

	}

	function getJobIdByNumber($jobnumber)
	{
		
		return $this->app['db']->fetchColumn('SELECT id FROM jobs WHERE jobnumber = ?', array($jobnumber), 0);

	}

	function getJobTitle($id)
	{
		
		return $this->app['db']->fetchColumn('SELECT title FROM jobs WHERE id = ?', array($id), 0);

	}

	function getWorkTypeName($id)
	{
		
		return $this->app['db']->fetchColumn('SELECT name FROM jobs_worktypes WHERE id = ?', array($id), 0);

	}

	function getWorkSubTypeName($id)
	{
		
		return $this->app['db']->fetchColumn('SELECT name FROM jobs_worksubtypes WHERE id = ?', array($id), 0);

	}

	function getAccountRate($id)
	{
		
		return $this->app['db']->fetchColumn('SELECT rate FROM jobs_rates WHERE organisationid = ?', array($id), 0);

	}

	function getNextJobNumber()
	{
		
		return ($this->app['db']->fetchColumn('SELECT jobnumber FROM jobs ORDER BY jobnumber DESC') + 1);

	}

}

?>