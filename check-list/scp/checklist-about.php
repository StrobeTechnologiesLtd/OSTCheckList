<?php
// *** osTicket - Includes
// ***********************
require('staff.inc.php');
// ***********************


// *** osTicket - Menu and Navigation
// **********************************
$nav->setTabActive('apps');
$nav->addSubMenu(array('desc'=>'Statistics',
						'title'=>'Check List Statistics',
						'href'=>'checklist-statistics.php',
						'iconclass'=>'closedTickets'
				));
$nav->addSubMenu(array('desc'=>'Reports',
						'title'=>'Check List Reports',
						'href'=>'checklist-reports.php',
						'iconclass'=>'closedTickets'
				));
$nav->addSubMenu(array('desc'=>'Managment Reports',
						'title'=>'Check List Management Reports',
						'href'=>'checklist-managerreports.php',
						'iconclass'=>'closedTickets'
				));
$nav->addSubMenu(array('desc'=>'Search',
						'title'=>'Check List Search',
						'href'=>'checklist-search.php',
						'iconclass'=>'closedTickets'
				));
$nav->addSubMenu(array('desc'=>'Admin',
						'title'=>'Check List Admin',
						'href'=>'checklist-admin.php',
						'iconclass'=>'closedTickets'
				));
$nav->addSubMenu(array('desc'=>'About',
						'title'=>'About Check List',
						'href'=>'checklist-about.php',
						'iconclass'=>'closedTickets'
				));
// **********************************
				
				
// *** Check List Plugin - Display
// *******************************
$ost->addExtraHeader('<link rel="stylesheet" type="text/css" href="checklist.css" />');

require_once(STAFFINC_DIR.'header.inc.php');

echo '<font size="+2"><b>About Check List</b></font><br />';
echo '<hr>';
echo '
	<p>Check list is a osTicket plugin brought you by Strobe Technologies Ltd.<br />
	This plugin enables you to add a daily, weekly and monthly task list to your helpdesk installation
	meaning staff can be given checks like backups and more than do not require tickets but needs doing and recording.</p>
	<p>
		<b>Application:</b> Check List Plugin<br />
		<b>Version:</b> v0.2.0<br />
		<b>Date:</b> 21/08/2015<br />
		<b>Copyright:</b> Strobe Technologies Ltd<br />
		<b>Website:</b> http://www.strobe-it.co.uk/
	</p>
';
echo '<hr>';

require_once(STAFFINC_DIR.'footer.inc.php');
// *******************************
?>