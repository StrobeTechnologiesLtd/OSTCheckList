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

if ( ! isset($searchstr) ) { $searchstr=""; }

$time = time(); 
$today = date('j',$time);
$current_user = $thisstaff->getFirstName() . ' ' . $thisstaff->getLastName();
// ********************************************




function display_page($datum,$month,$year,$searchstr) {
	global $lang;
	global $errormsg;

	print "<table>";
	print "<tr>";
	
	print "<hr><td valign='top'><table><tr><td><!-- <H1>Main</h1><p> -->";
	if ( strlen($datum)<2 ) { $datum=date("Y-m-d",time()); }
	# first row contains calendar
	$pn = array('&laquo;'=>'./checklist-search.php?vorige', '&raquo;'=>'./checklist-search.php?volgende'); 
	print "<tr><td valign='top' nowrap>\n";
	$days = array($today=>array(NULL,NULL, 
		'<span style="color: red; font-weight: bold; font-size: larger; 
		text-decoration: blink;">' .$today.'</span>')); 
		show_calendar("checklist-search.php",$year,$month);
	print "</td><td valign='top' align='center'>";
	print "<img src='check-list-img/Vista-kdisknav.png'>";
	print "</td></tr></table>";
	#print "</td>\n\n<!-- end off calendar -->\n\n";
	print "\n\n<!-- end off kalendar -->\n\n";
	# display available reports
	print "<!-- start search entry -->\n";
	print "<form method='get' id='myform' action='checklist-search.php'>\n";
	print "<input type='text' name='searchstr' id='zoekveld' value=''><br>\n";
	print "</form>\n";
	print "<td valign='top'>";
	
	print "<font color='red'>".$errormsg."</font>\n";
	print "<!-- start build edit form -->\n";
	if (strlen($searchstr)>=2) {
		search_function($searchstr);
	}
	# doe de search query
	print "</tr></td></table></td></tr>";
	print "</table>";
}
	

function search_function($searchstr) {
	global $lang;
	# split search words to an array
	$words = split("[\n\r\t ]+", $searchstr);
	print "The quest for <span class='blue'>'".$searchstr."'</span> has produced the following:<br><br>\n";
	$query = "SELECT * FROM ". CHECKLIST_TABLE_ENTRIES ." WHERE MATCH (tekst) AGAINST ('".$searchstr."') ORDER BY datum DESC";
    #print "$query";
	$result = db_query($query);
	print "<table>";
	while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
		print "<tr><td nowrap valign='top'>";
		print $row["datum"];
		print "</td><td>";
		if ( $row["status"] == -1 ) { print "<span class='not_ok'>";}
        if ( $row["status"] ==  1 ) { print "<span class='ok'>";}
        if ( $row["status"] ==  2 ) { print "<span class='warning'>";}
		# put blue span arround mathing words 
		$regel=rtrim($row["tekst"]);
		foreach($words as $waarde) {
			$regel=ereg_replace("(".$waarde.")", "<span class='blue'>\\1</span>", $regel);
		}
		print "<span class='code'>".$regel."</span>";
		print "</span></td>";
		print "</tr>\n";
    }
	print "</table>";
    print "\n\n\n";

}


##############################################################################
#                            End of functions                                #
##############################################################################
# decide what to display, a graph (which one?) or the page itself	

require_once(STAFFINC_DIR.'header.inc.php');

display_page($datum,$month,$year,$searchstr); 

require_once(STAFFINC_DIR.'footer.inc.php');
?>
