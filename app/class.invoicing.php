<?php 


class Invoicing
{
	
    private $app;
    private $logs;

	function __construct($app, $logs) { 
	
		$this->app = $app;
		$this->logs = $logs;

	}

	function setMonthOpen($date){

		$arr = explode("/", $date);
		   
		  $return = $this->app['db']->delete(
			'jobs_invoice_closedmonths',
			array(
				'year' => $arr[1],
				'month' => $arr[0]
			)
		);

		return $return;

	}
	
	function setMonthClosed($date){

		$arr = explode("/", $date);
		   
		$return = $this->app['db']->insert(
			'jobs_invoice_closedmonths',
			array(
				'year' => $arr[1],
				'month' => $arr[0]
			)
		);

		return $return;

	}


	function checkMonthClosed($date)
	{
		
		$arr = explode("/", $date);
		
		return $this->app['db']->fetchColumn('SELECT COUNT(*) FROM jobs_invoice_closedmonths WHERE year = ? AND month = ?', array($arr[1], $arr[0]));

	}
	
	function getClosedMonthLength()
	{
		
		return $this->app['db']->fetchColumn('SELECT TIMESTAMPDIFF(MONTH, NOW(), CONCAT(CAST(year AS CHAR), "-", CAST(month AS CHAR),"-01")) FROM jobs_invoice_closedmonths ORDER BY year DESC, month DESC LIMIT 1');

	}

    function getJobsForInvoicing($date)
    {
		
		$arr = explode("/", $date);
		
		$results = $this->app['db']->fetchAll('

		SELECT
		   jobs.id, jobs.jobnumber, jobs.organisationid, jobs.contactid, jobs.accountmanagerid, jobs.title, jobs.description, jobs.po, jobs_invoice_lines.id as lineid, jobs_invoice_lines.statusid, jobs_invoice_lines.chargein, jobs_invoice_lines.chargeout, jobs_invoice_lines.studiovalue, jobs_invoice_lines.invoicedate, jobs_invoice_lines.invoicenumber, jobs_invoice_lines.note, jobs_invoice_lines.finalinvoice
		FROM
		   jobs
		LEFT JOIN
			jobs_invoice_lines ON jobs_invoice_lines.jobid = jobs.id AND jobs_invoice_lines.statusid >= 2
		WHERE 
		   jobs_invoice_lines.invoiceyear = ? AND jobs_invoice_lines.invoicemonth = ?
		   ', array($arr[1], $arr[0]));

		return $results;
    }

    function getJobsForInvoicingCount($date)
    {

		$arr = explode("/", $date);
		
		$results = $this->app['db']->fetchAll('

		SELECT
			COUNT(*) As count, firstname, lastname, accountmanagerid, SUM(jobs_invoice_lines.studiovalue) As studiovalue, SUM(jobs_invoice_lines.chargein) As chargein, SUM(jobs_invoice_lines.chargeout) As chargeout
		FROM 
			jobs
		LEFT JOIN
			wcg_employees ON jobs.accountmanagerid = wcg_employees.id
		LEFT JOIN
			jobs_invoice_lines ON jobs_invoice_lines.jobid = jobs.id AND jobs_invoice_lines.statusid >= 2
		WHERE
			jobs_invoice_lines.statusid >= 2 AND jobs_invoice_lines.invoiceyear = ? AND jobs_invoice_lines.invoicemonth = ?
		GROUP BY
			accountmanagerid
		   ', array($arr[1], $arr[0]));

		return $results;
    }
	
	
	function updateJobInvoiceDate($invoicelineid, $value){
		
		if(!$value){
			$value = NULL;
		}
		
		  $return = $this->app['db']->update(
			'jobs_invoice_lines',
			array(
				'invoicedate' => $value
			),
			array(
				'id' => $invoicelineid
			)
		);

		$this->logs->addLog('jobs', $jobId, 'update');

		return $return;

	}
	
	function updateJobInvoiceNumber($invoicelineid, $value){

		if(!$value){
			$value = NULL;
		}
		   
		  $return = $this->app['db']->update(
			'jobs_invoice_lines',
			array(
				'invoicenumber' => $value
			),
			array(
				'id' => $invoicelineid
			)
		);

		$this->logs->addLog('jobs', $jobId, 'update');

		return $return;

	}

}

?>