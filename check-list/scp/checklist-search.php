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
// **********************************


// *** Check List Plugin - Includes / Variables
// ********************************************
$Revision="";
$Date="";
global $errormsg;
global $version;
$errormsg="";
$version="Version$Revision: 0.0.2 $ (Date$Date: 29/06/2015 07:31:00 $)";
# de volgende $ is een RCS keywordbegin en geen php variabele!
$version=str_replace("$", "",$version);

include (CHECKLIST_INCLUDE_DIR.'settings.php');
include (CHECKLIST_INCLUDE_DIR.'lib.php');
require (CHECKLIST_INCLUDE_DIR.'calendar.php');

$jpgraphpath="jpgraph/src/";
include ($jpgraphpath."jpgraph.php");
include ($jpgraphpath."jpgraph_line.php");
include ($jpgraphpath."jpgraph_bar.php");
#DEFINE ("TTF_DIR","/usr/X11R6/lib/X11/fonts/TTF/" );
	
# initialisatie: 
#error_reporting(E_ALL);
/*import_request_variables("gpc");*/
extract($_REQUEST);
$num_columns=6;
//$current_user=getenv('REMOTE_USER');
$current_user = $thisstaff->getFirstName() . ' ' . $thisstaff->getLastName();
if ( ! isset($datum) ) { 
	$datum=date("Y-m-d",time()); 
} else {
	list($year,$month, $day) = split('[/.-]', $datum);
}	
if ( ! isset($month) ) { $month=date("m"); }
if ( ! isset($year) ) { $year=date("Y"); }
if ( ! isset($searchstr) ) { $searchstr=""; }

function display_page($datum,$month,$year,$searchstr) {
	global $lang;
	global $errormsg;
	global $version;
	//print "<html>";
	//print "<head>";
	//print "<title>Groene map</title>";
	//print "<link rel='stylesheet' type='text/css' href='groenemap.css' />";
	//print "</head>";
	#print "<body onload='document.forms[0].zoekveld.focus();'>  ";
	print "<body onload='document.forms[0].elements[0].focus();'>  ";

	print "<table>";
	print "<tr><td class='border' valign='top'><!-- <h1>Menu</h1> -->";
	#print "<!-- User ".$current_user."<br> -->";

	print "</td>";

	//global_menu();
	print "<hr><td valign='top'><table><tr><td><!-- <H1>Main</h1><p> -->";
	if ( strlen($datum)<2 ) { $datum=date("Y-m-d",time()); }
	# first row contains calendar
	$pn = array('&laquo;'=>'./checklist.php?vorige', '&raquo;'=>'./checklist.php?volgende'); 
	print "<tr><td valign='top' nowrap>\n";
	setlocale(LC_TIME, 'nl_NL'); #dutch 
	$time = time(); 
	$today = date('j',$time); 
	$days = array($today=>array(NULL,NULL, 
		'<span style="color: red; font-weight: bold; font-size: larger; 
		text-decoration: blink;">' .$today.'</span>')); 
		show_calendar("checklist-statistics.php",$year,$month);
	print "</td><td valign='top' align='center'>";
	print "<img src='check-list-img/Vista-kdisknav.png'>";
	print "</td></tr></table>";
	#print "</td>\n\n<!-- end off calendar -->\n\n";
	print "\n\n<!-- end off kalendar -->\n\n";
	# display available reports
	print "<!-- start search entry -->\n";
	print "<form method='post' id='myform' action='checklist-search.php'>\n";
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
	print "<font size='1'>".$version."</font>";
	//print "</body>";
	//print "</html>";
}
	

function search_function($searchstr) {
	global $lang;
	# split search words to an array
	$words = split("[\n\r\t ]+", $searchstr);
	print "De zoektocht naar <span class='blue'>'".$searchstr."'</span> heeft het volgende opgeleverd:<br><br>\n";
	#$query = "SELECT *  from entries where tekst like '%".$searchstr."%' order by datum desc";
	$query = "SELECT *  from entries where MATCH (tekst) AGAINST ('".$searchstr."') order by datum desc";
    #print "$query";
    $result = mysql_query($query) or exit ($lang[21].mysql_error());
	print "<table>";
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
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
    mysql_free_result($result);

}


##############################################################################
#                            End of functions                                #
##############################################################################
# decide what to display, a graph (which one?) or the page itself	

require_once(STAFFINC_DIR.'header.inc.php');

display_page($datum,$month,$year,$searchstr); 
mysql_close($link);

require_once(STAFFINC_DIR.'footer.inc.php');
?>
