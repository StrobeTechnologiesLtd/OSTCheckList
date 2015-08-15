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


// *** Check List Plugin - Includes / Variables
// ********************************************
$errormsg="";

global $errormsg;

## JpGraph Includes
include (CHECKLIST_JPGRAPH.'jpgraph.php');
include (CHECKLIST_JPGRAPH.'jpgraph_line.php');
include (CHECKLIST_JPGRAPH.'jpgraph_bar.php');
	
# initialisation: 
extract($_REQUEST);
$num_columns=6;


## Check to see if full date passed via GET or not, if not get todays date
if ( strlen($_GET['datum'])<2 ) { 
	$datum=date("Y-m-d",time()); 
} else {
	$datum = $_GET['datum'];
	list($year,$month, $day) = split('[/.-]', $_GET['datum']);
}

## Work out if month or year was passed via GET and fill in month id no date or GET sent
if (isset($_GET['month']))  { $month=$_GET['month']; }
if (isset($_GET['year']))  { $year=$_GET['year']; }
if ( ! isset($month) ) { $month=date("m"); }
if ( ! isset($year) )  { $year=date("Y"); }

$time = time(); 
$today = date('j',$time);
$current_user = $thisstaff->getFirstName() . ' ' . $thisstaff->getLastName();
// ********************************************


// *** Check List Plugin - Page Functions
// **************************************
function display_page($datum,$month,$year) {
	global $lang;
	global $errormsg;

	echo '
	<table border="0" width="930">
		<tr>
			<td colspan="2"><h1>Check List Management Reports</h1></td>
		</tr>
		<tr>
			<td width="300">
	';
				show_calendar("checklist-managerreports.php",$year,$month);
	echo 		'( <a href="checklist-managerreports.php"><b>' . $lang[42] . '</b></a> ) ';
	echo 		'<br /><br />
				<h2>Reports</h2>
	';
				display_manreportlist("checklist-managerreports.php");
	echo 		'<p>&nbsp;</p>
			</td>
	';
			if (! isset($_GET['act'])) {
				echo '<td><p>Please choose the required report from the left handside.</p></td>';
			}
	echo '</tr>
	</table>
	';
}
// **************************************
	

// *** Check List Plugin - Page Logic
// **********************************
require_once(STAFFINC_DIR.'header.inc.php');
display_page($datum,$month,$year); 
require_once(STAFFINC_DIR.'footer.inc.php');
// **********************************
?>
