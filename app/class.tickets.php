<?php 


class Tickets
{

    private $app;
    private $logs;

	function __construct($app, $logs) { 
	
		$this->app = $app;
		$this->logs = $logs;

	}

	function changeTicketStatus($id, $closed){
		   
		  $return = $this->app['db']->update(
			'support_tickets',
			array(
				'statusid' => $closed
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('support_tickets', $id, 'update');

		return $return;

	}

	function changeTicketClosed($id, $closed){
		   
		  $return = $this->app['db']->update(
			'support_tickets',
			array(
				'closed' => $closed,
				'statusid' => 6
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('support_tickets', $id, 'update');

		return $return;

	}

    function getTickets()
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               support_tickets.*
           FROM
               support_tickets
		   ORDER BY lastchanged DESC
		   ');
		
		return $results;

    }

    function getTicketsByClient($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               support_tickets.*, support_replies.*
           FROM
               support_tickets
			LEFT JOIN
				support_replies ON support_replies.ticketid = support_tickets.id
           WHERE
               organisationid = ?
		  GROUP BY
		  	 support_tickets.id
		  ORDER BY
		  	 support_replies.id ASC
		   ', array($id));
		
		return $results;

    }

    function getTicketsByContact($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               support_tickets.*, support_replies.*
           FROM
               support_tickets
			LEFT JOIN
				support_replies ON support_replies.ticketid = support_tickets.id
           WHERE
               contactid = ?
		  GROUP BY
		  	 support_tickets.id
		  ORDER BY
		  	 support_replies.id ASC
		   ', array($id));
		
		return $results;

    }
	
    function getTicketDetails($id)
    {
		$results = $this->app['db']->fetchAssoc('
           SELECT
               support_tickets.*
           FROM
               support_tickets
          WHERE
               support_tickets.id = ?
		   ', array($id));
		
		return $results;

    }

    function getTicketReplies($id)
    {
		$results = $this->app['db']->fetchAll('
           SELECT
               support_replies.*, support_attachments.filenames
           FROM
               support_replies
			LEFT JOIN
				support_attachments ON support_replies.id = support_attachments.replyid
           WHERE
               ticketid = ?
			 ORDER BY date DESC
		   ', array($id));
		
		return $results;

    }

	
	function addReply($id, $reply, $userid){
		   
		$this->app['db']->insert(
			'support_replies',
			array(
				'ticketid' => $id,
				'body' => $reply,
				'usertype' => 2
				,
				'userid' => $userid,
				'date' => date("Y-m-d H:i:s", time())
			)
		);
		
		$lastid = $this->app['db']->lastInsertId();

		$this->logs->addLog('support_replies', $lastid, 'insert');

		 $this->app['db']->update(
			'support_tickets',
			array(
				'lastchanged' => date("Y-m-d H:i:s", time())
			),
			array(
				'id' => $id
			)
		);

		return $lastid;

	}

	function addAttachment($id, $filenames){
		   
		$this->app['db']->insert(
			'support_attachments',
			array(
				'replyid' => $id,
				'filenames' => $filenames
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('support_attachments', $return, 'insert');
		
		return $return;

	}

	function addTicket($trackingId, $ticketSubject, $ticketCompany, $ticketContact, $ticketJob, $ticketDepartment, $ticketAssigned, $ticketStatus, $ticketPriority, $ticketDate){

		$this->app['db']->insert(
			'support_tickets',
			array(
				'trackingid' => $trackingId,
				'subject' => $ticketSubject,
				'organisationid' => $ticketCompany,
				'contactid' => $ticketContact,
				'jobid' => $ticketJob,
				'departmentid' => $ticketDepartment,
				'assignedtoid' => $ticketAssigned,
				'statusid' => $ticketStatus,
				'priorityid' => $ticketPriority,
				'date' => date("Y-m-d H:i:s", time()),
				'lastchanged' => date("Y-m-d H:i:s", time())
			)
		);

		$return = $this->app['db']->lastInsertId();

		$this->logs->addLog('support_tickets', $return, 'insert');
		
		return $return;

	}

	function updateTicket($id, $ticketSubject, $ticketCompany, $ticketContact, $ticketJob, $ticketDepartment, $ticketAssigned, $ticketStatus, $ticketPriority, $ticketDate){
		   
		  $return = $this->app['db']->update(
			'support_tickets',
			array(
				'subject' => $ticketSubject,
				'organisationid' => $ticketCompany,
				'contactid' => $ticketContact,
				'jobid' => $ticketJob,
				'departmentid' => $ticketDepartment,
				'assignedtoid' => $ticketAssigned,
				'statusid' => $ticketStatus,
				'priorityid' => $ticketPriority,
				'lastchanged' => date("Y-m-d H:i:s", time())
			),
			array(
				'id' => $id
			)
		);

		$this->logs->addLog('support_tickets', $id, 'update');

		return $return;
	}

	function getDepartmentName($id)
	{
		return $this->app['db']->fetchColumn('SELECT name FROM support_departments WHERE id = ?', array($id), 0);
	}

	function getStateName($id)
	{
		return $this->app['db']->fetchColumn('SELECT name FROM support_states WHERE id = ?', array($id), 0);
	}

	function getPriorityName($id)
	{
		return $this->app['db']->fetchColumn('SELECT name FROM support_priorities WHERE id = ?', array($id), 0);
	}
	
	function getListOfDepartments()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM support_departments');
		return $results;
	}

	function getListOfSupportEmployees()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM support_users WHERE type = ?', array(2));
		return $results;
	}

	function getListOfSupportCustomers()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM support_users WHERE type = ?', array(2));
		return $results;
	}

	function getListOfStates()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM support_states');
		return $results;
	}

	function getListOfPriorities()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM support_priorities');
		return $results;
	}

	function getListOfSupportAgents()
	{
		$results = $this->app['db']->fetchAll('SELECT * FROM wcg_employees WHERE (groupid & 4)');
		return $results;
	}

	function checkTrackingId($id)
	{
		return $this->app['db']->fetchColumn('SELECT trackingid FROM support_tickets WHERE trackingid = ?', array($id), 0);
	}


	function createTrackingID()
	{
	
		/*** Generate tracking ID and make sure it's not a duplicate one ***/
	
		/* Ticket ID can be of these chars */
		$useChars = 'AEUYBDGHJLMNPQRSTVWXZ123456789';
	
		/* Set tracking ID to an empty string */
		$trackingID = '';
	
		/* Let's avoid duplicate ticket ID's, try up to 3 times */
		for ($i=1;$i<=3;$i++)
		{
			/* Generate raw ID */
			$trackingID .= $useChars[mt_rand(0,29)];
			$trackingID .= $useChars[mt_rand(0,29)];
			$trackingID .= $useChars[mt_rand(0,29)];
			$trackingID .= $useChars[mt_rand(0,29)];
			$trackingID .= $useChars[mt_rand(0,29)];
			$trackingID .= $useChars[mt_rand(0,29)];
			$trackingID .= $useChars[mt_rand(0,29)];
			$trackingID .= $useChars[mt_rand(0,29)];
			$trackingID .= $useChars[mt_rand(0,29)];
			$trackingID .= $useChars[mt_rand(0,29)];
	
			/* Format the ID to the correct shape and check wording */
			$trackingID = self::formatID($trackingID);
	
			/* Check for duplicate IDs */
			$res = $this->checkTrackingId($trackingID);
	
			if ($res != $trackingID){
				/* Everything is OK, no duplicates found */
				return $trackingID;
			}
	
			/* A duplicate ID has been found! Let's try again (up to 2 more) */
			$trackingID = '';
		}
	
		/* No valid tracking ID, try one more time with microtime() */
		$trackingID  = $useChars[mt_rand(0,29)];
		$trackingID .= $useChars[mt_rand(0,29)];
		$trackingID .= $useChars[mt_rand(0,29)];
		$trackingID .= $useChars[mt_rand(0,29)];
		$trackingID .= $useChars[mt_rand(0,29)];
		$trackingID .= substr(microtime(), -5);
	
		/* Format the ID to the correct shape and check wording */
		$trackingID = formatID($trackingID);
	
		$res = $this->checkTrackingId($trackingID);
	
		/* All failed, must be a server-side problem... */
		if ($res != $trackingID){
			return $trackingID;
		}
	
		return false;
	
	} // END hesk_createID()

	function formatID($id)
	{
	
		$useChars = 'AEUYBDGHJLMNPQRSTVWXZ123456789';
	
		$replace  = $useChars[mt_rand(0,29)];
		$replace .= $useChars[mt_rand(0,29)];
		$replace .= $useChars[mt_rand(0,29)];
	
		/*
		Remove 3 letter bad words from ID
		Possiblitiy: 1:27,000
		*/
		$remove = array(
		'ASS',
		'CUM',
		'FAG',
		'FUK',
		'GAY',
		'SEX',
		'TIT',
		'XXX',
		);
	
		$id = str_replace($remove,$replace,$id);
	
		/*
		Remove 4 letter bad words from ID
		Possiblitiy: 1:810,000
		*/
		$remove = array(
		'ANAL',
		'ANUS',
		'BUTT',
		'CAWK',
		'CLIT',
		'COCK',
		'CRAP',
		'CUNT',
		'DICK',
		'DYKE',
		'FART',
		'FUCK',
		'JAPS',
		'JERK',
		'JIZZ',
		'KNOB',
		'PISS',
		'POOP',
		'SHIT',
		'SLUT',
		'SUCK',
		'TURD',
	
		// Also, remove words that are known to trigger mod_security
		'WGET',
		);
	
		$replace .= $useChars[mt_rand(0,29)];
		$id = str_replace($remove,$replace,$id);
	
		/* Format the ID string into XXX-XXX-XXXX format for easier readability */
		$id = $id[0].$id[1].$id[2].'-'.$id[3].$id[4].$id[5].'-'.$id[6].$id[7].$id[8].$id[9];
	
		return $id;
	
	} // END hesk_formatID()
		

}

?>