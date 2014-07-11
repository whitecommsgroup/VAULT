<?php 


class Timesheets
{

    private $app;
    private $logs;

	function __construct($app, $logs) { 
	
		$this->app = $app;
		$this->logs = $logs;

	}

    function getTimesheetLinesByTimesheetId($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               jobs_timesheets_lines.*, jobs.organisationid, jobs.id as jobidentity
           FROM
               jobs_timesheets_lines
           LEFT JOIN
               jobs ON jobs_timesheets_lines.jobid = jobs.jobnumber
           WHERE
               timesheetid = ?
			ORDER BY	   
			 	id ASC
		   ', array($id));

		return $results;

    }

    function getTimesheetById($id)
    {
		$results = $this->app['db']->fetchAssoc('
           SELECT
               *
           FROM
               jobs_timesheets
           WHERE
               id = ?
		   ', array($id));

		return $results;

    }

    function getTimesheetsByEmployee($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               jobs_timesheets.*, SUM(jobs_timesheets_lines.hours) As totalhours
           FROM
               jobs_timesheets
           LEFT JOIN
               jobs_timesheets_lines
           ON
               jobs_timesheets_lines.timesheetid = jobs_timesheets.id
           WHERE
               jobs_timesheets.employeeid = ?
           GROUP BY
               jobs_timesheets.id
		   ORDER BY date DESC
		   ', array($id));

		return $results;

    }

    function getTimeByJobAndEmployee($jobnumber, $employeeid)
    {
		$results = $this->app['db']->fetchAll('
			SELECT hours, worktypeid, date, worksubtypeid, description, approved, description
			FROM jobs_timesheets_lines
			LEFT JOIN jobs_timesheets ON jobs_timesheets.id = jobs_timesheets_lines.timesheetid
			WHERE jobid = ? AND jobs_timesheets_lines.employeeid = ?
		', array($jobnumber, $employeeid));

		return $results;

    }


    function getTimesheets()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               jobs_timesheets.*
           FROM
               jobs_timesheets
		   ORDER BY date DESC
		   ');

		return $results;

    }

    function getTimesheetsForApproval()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               jobs_timesheets.*
           FROM
               jobs_timesheets
           LEFT JOIN
               jobs_timesheets_lines
           ON
               jobs_timesheets_lines.timesheetid = jobs_timesheets.id
           WHERE
               jobs_timesheets_lines.approved = 0 AND DATE(jobs_timesheets.date) < DATE(NOW())
           GROUP BY
               jobs_timesheets.id
		   ORDER BY date DESC
		   ');

		return $results;

    }

    function getWorkTypes()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               *
           FROM
               jobs_worktypes
 		  ORDER BY
		  	name ASC
		   ');
		
		return $results;

    }

	function addTimeSheet($employeeid, $date){
		   
		$this->app['db']->insert(
			'jobs_timesheets',
			array(
				'employeeid' => $employeeid,
				'date' => uk_date_to_mysql_date($date)
			)
		);

		$return = $this->app['db']->lastInsertId();
		
		$this->logs->addLog('jobs_timesheets', $return, 'insert');
		
		return $return;

	}

	function updateTimesheet($id, $date){
		   
	  $return = $this->app['db']->update(
			'jobs_timesheets',
			array(
				'date' => uk_date_to_mysql_date($date)
			),
			array(
				'id' => $id
			)
		);
		
		return $return;

	}

	function addTimeSheetLine($timesheetid, $hours, $jobid, $worktypeid, $description, $images, $employeeid){
		   
		$this->app['db']->insert(
			'jobs_timesheets_lines',
			array(
				'timesheetid' => $timesheetid,
				'hours' => $hours,
				'jobid' => $jobid,
				'worktypeid' => $worktypeid,
				'description' => $description,
				'images' => $images,
				'employeeid' => $employeeid
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('jobs_timesheets_lines', $return, 'insert');

		return $return;
		
	}

	function updateTimeSheetLine($id, $timesheetid, $hours, $jobid, $worktypeid, $description, $images, $employeeid, $approved){
		   
		  $return = $this->app['db']->update(
			'jobs_timesheets_lines',
			array(
				'timesheetid' => $timesheetid,
				'hours' => $hours,
				'jobid' => $jobid,
				'worktypeid' => $worktypeid,
				'description' => $description,
				'images' => $images,
				'employeeid' => $employeeid,
				'approved' => $approved
			),
			array(
				'id' => $id
			)
		);

		//$this->logs->addLog('jobs_timesheets_lines', $id, 'update');

		return $return;
	}

	function approveLine($id){
		   
		  $return = $this->app['db']->update(
			'jobs_timesheets_lines',
			array(
				'approved' => 1,
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('jobs_timesheets_lines', $id, 'approve');

		return $return;
	}

	function unApproveLine($id){
		   
		  $return = $this->app['db']->update(
			'jobs_timesheets_lines',
			array(
				'approved' => 0,
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('jobs_timesheets_lines', $id, 'un-approve');

		return $return;
	}

    function deleteLine($id){
		   
		  $return = $this->app['db']->delete(
			'jobs_timesheets_lines',
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('jobs_timesheets_lines', $id, 'delete');

		return $return;
		
	}


}

?>