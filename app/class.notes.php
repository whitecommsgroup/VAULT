<?php 


class Notes
{

    private $app;
    private $logs;

	function __construct($app, $logs) { 
	
		$this->app = $app;
		$this->logs = $logs;

	}

	function addNote($accountid, $body, $contactid, $jobid, $accountmanagerid){
		   
		$this->app['db']->insert(
			'organisation_notes',
			array(
				'organisationid' => $accountid,
				'type' => 1,
				'accountmanagerid' => $accountmanagerid,
				'date' => date("Y-m-d H:i:s", time()),
				'body' => $body,
				'contactid' => $contactid,
				'jobid' => $jobid
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('organisation_notes', $return, 'insert');
		
		return $return;

	}

	function addAttachment($noteid, $filenames){
		   
		$this->app['db']->insert(
			'account_attachments',
			array(
				'noteid' => $noteid,
				'filenames' => $filenames
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('account_attachments', $return, 'insert');
		
		return $return;

	}

	function addProspectNote($orgid, $body, $userid){
		   
		$this->app['db']->insert(
			'organisation_notes',
			array(
				'organisationid' => $orgid,
				'body' => $body,
				'accountmanagerid' => $userid,
				'date' => date("Y-m-d H:i:s", time())
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('organisation_notes', $return, 'insert');
		
		return $return;

	}

	function deleteNote($id){
		   
	  $return = $this->app['db']->update(
			'organisation_notes',
			array(
				'deleted' => 1
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('organisation_notes', $id, 'delete');

		return $return;

	}

    function getNotes()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               *
           FROM
               organisation_notes
           WHERE
               deleted = 0
		   ORDER BY id DESC
		   ');

		return $results;

    }

    function getNotesByJob($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               organisation_notes.*, account_attachments.filenames
           FROM
               organisation_notes
			LEFT JOIN
				account_attachments ON account_attachments.noteid = organisation_notes.id
           WHERE
               organisation_notes.jobid = ? AND organisation_notes.deleted = 0
			ORDER BY
				date DESC
		   ', array($id));
		
		return $results;

    }

    function getNotesByClient($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               organisation_notes.*, wcg_employees.firstname AS employee_firstname, organisation_contacts.firstname AS contact_firstname, account_attachments.filenames
           FROM
               organisation_notes
			LEFT JOIN
				wcg_employees ON wcg_employees.id = organisation_notes.accountmanagerid
			LEFT JOIN
				organisation_contacts ON organisation_contacts.id = organisation_notes.contactid
			LEFT JOIN
				account_attachments ON account_attachments.noteid = organisation_notes.id
           WHERE
               organisation_notes.organisationid = ? AND organisation_notes.deleted = 0
			ORDER BY
				date DESC
		   ', array($id));
		
		return $results;

    }

    function getNotesByContact($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               organisation_notes.*, account_attachments.filenames, wcg_employees.firstname AS employee_firstname
           FROM
               organisation_notes
			LEFT JOIN
				wcg_employees ON wcg_employees.id = organisation_notes.accountmanagerid
			LEFT JOIN
				account_attachments ON account_attachments.noteid = organisation_notes.id
           WHERE
               organisation_notes.contactid = ? AND organisation_notes.deleted = 0
			ORDER BY
				date DESC
		   ', array($id));
		
		return $results;

    }


}

?>