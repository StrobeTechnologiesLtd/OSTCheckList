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
$nav->addSubMenu(array('desc'=>'About',
						'title'=>'About Check List',
						'href'=>'checklist-about.php',
						'iconclass'=>'closedTickets'
				));
// **********************************


// *** Check List Plugin - Includes / Variables
// ********************************************
$Date="";
global $errormsg;
$errormsg="";

//include (CHECKLIST_INCLUDE_DIR.'settings.php');
include (CHECKLIST_INCLUDE_DIR.'lib.php');
require (CHECKLIST_INCLUDE_DIR.'calendar.php');

$jpgraphpath="jpgraph/src/";
include ($jpgraphpath."jpgraph.php");
include ($jpgraphpath."jpgraph_line.php");
include ($jpgraphpath."jpgraph_bar.php");
	
# initialise: 
#error_reporting(E_ALL);
/*import_request_variables("gpc");*/
extract($_REQUEST);
$num_columns=6;
# we get this from some basic auth part of the site. 
//$current_user=getenv('REMOTE_USER');
$current_user = $thisstaff->getFirstName() . ' ' . $thisstaff->getLastName();
if ( ! isset($datum) ) { 
	$datum=date("Y-m-d",time()); 
} else {
	list($year,$month, $day) = split('[/.-]', $datum);
}	
if ( ! isset($month) ) { $month=date("m"); }
if ( ! isset($year) ) { $year=date("Y"); }
if ( ! isset($report) ) { $report=0; }
#if ( ! isset($graph) ) { $graph=0; }

function display_page($datum,$month,$year,$report) {
	global $lang;
	global $errormsg;
	global $version;
	//print "<html>";
	//print "<head>";
	//print "<title>CheckList</title>";
	//print "<link rel='stylesheet' type='text/css' href='groenemap.css' />";
	//print "</head>";
	//print "<body>";

	print "<table>";
	print "<tr><td class='border' valign='top'><!-- <h1>".$lang[22]."</h1> -->";

	print "</td>";

	//global_menu();
	print "<hr><td valign='top'><table><tr><td><!-- <H1>".$lang[23]."</h1><p> -->";
	if ( strlen($datum)<2 ) { $datum=date("Y-m-d",time()); }
	# first row contains calendar
	$pn = array('&laquo;'=>'./reports.php?vorige', '&raquo;'=>'./reports.php?volgende'); 
	print "<tr><td valign='top' nowrap class=border>\n";
	$time = time(); 
	$today = date('j',$time); 
	$days = array($today=>array(NULL,NULL, 
		'<span style="color: red; font-weight: bold; font-size: larger; 
		text-decoration: blink;">' .$today.'</span>')); 
	# laatste 2 op volgende  regel is aantal chars voor kolomnaam
	show_calendar("reports.php",$year,$month);
	print "</td><td valign='top' align='center'>";
    print "<img src='check-list-img/Vista-folder_print.png'>";
    print "</td></tr></table>";
	print "<a href='reports.php'>".$lang[24]."</a><br>";

	#print "</td>\n\n<!-- end off calender -->\n\n";
	print "\n\n<!-- end off calendar -->\n\n";
	# display the possible report types
	print "<!-- start list of reports -->\n";
	print "<a href='reports.php?report=1&month=".$month."&year=".$year."'>".$lang[25]."</a><br>\n";
	print "<a href='reports.php?report=2&month=".$month."&year=".$year."'>".$lang[26]."</a><br>\n";
	print "<a href='reports.php?report=3&month=".$month."&year=".$year."'>".$lang[27]."</a><br>\n";
	#print "</form>\n";
	print "<td valign='top'>";
	
	print "<font color='red'>".$errormsg."</font>\n";
	print "<!-- start build of edit form -->\n";
	print "<hr>";
	if ($report!=0) {
		display_report($report,$month,$year);
	} else {
		sprintf ($lang[3],$month,$year);
	}
	print "</td>";
	print "</tr>\n";
	print "</tr></td></table></td></tr>";
	print "</table>";
	//print "</body>";
	//print "</html>";
}
	
function display_report($report,$month,$year) {
	global $lang;
	global $statstr;
	switch ($report) {
		case 1:
			$query  = "SELECT checklist.tekst as checktekst,entries.datum as datum, entries.tekst as tekst, entries.door as door, entries.ref as ref, entries.status as status ";
			$query .= " from entries,checklist where checklist.id=entries.ref and  status!=0 and status!=1 ";
			$query .= " and month(datum)=".$month." and year(datum)=".$year." order by ref,datum desc";
	                #print "$query";
     	        	$result = mysql_query($query) or exit ($lang[21].mysql_error());
	                print "<table >"; 
			print "<tr><th>".$lang[28]."</th><th>".$lang[29]."</th><th>".$lang[30]."</th></tr>\n";
			$oldref=-1;
	                while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				if ( $oldref!=$row["ref"]) {	
					$oldref=$row["ref"];
					print "<tr><th></th><th align='left'>".$row["checktekst"]."</th><th><th></tr>\n";
				}
	                        print "<tr><td nowrap valign='top'>";
	                        print $row["datum"];
	                        print "</td><td  valign='top' padding='0'>";
	                        if ( $row["status"] == -1 ) { print "<span class='not_ok'>";}
	                        if ( $row["status"] ==  1 ) { print "<span class='ok'>";}
	                        if ( $row["status"] ==  2 ) { print "<span class='warning'>";}
	                        #print "<pre>"; #.$row["status"];
				print "<span class='code'>";
				if ( strlen($row["tekst"])>0 ) {
		                        print ltrim(rtrim($row["tekst"]));
				} else {
					print $lang[4];
				}
				print "</span>";
	                        #print "</pre>";
	                        print "</span></td>";
				if ( strlen($row["door"])>0 ) {
					print "<td  valign='top'>".$row["door"]."</td>";
				} else {
					print "<td  valign='top'>&nbsp;</td>";
				}
	                        print "</tr>\n";
	                }
	                print "</table>";
	                print "\n\n\n";
	                mysql_free_result($result);
			break;
		case 2:
			$query  = "SELECT * from checklist order by orde desc";
	                #print "$query";
     	        	$result = mysql_query($query) or exit ($lang[21].mysql_error());
	                print "<table border='0'>"; 
			$oldref=-1;
	                while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			# ask per checklistitem the number of entered entries this month per status
				$query2="SELECT count(status) as count,status from entries where ref=".$row["id"]." and month(datum)=".$month." and year(datum)=".$year." group by status ";
     	        		$result2 = mysql_query($query2) or exit ($lang[21].mysql_error());
				$num_rows = mysql_num_rows($result2);
				# we only display if there are entries for this checklist-item. disabled or not.
				if ( $num_rows > 0 ) { 
					print "<tr><th align='left'>".$row["tekst"];
					if ( $row["disabled"]==1 ) {
						print "<font size='1' color='red'>".$lang[31]."</font>";
					}
					print "</th></tr>\n";
					print "<tr><td align='right'><table border='1'><tr><th>".$lang[32]."Status</th><th>".$lang[12]."</th></tr>\n";
		                	while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
						print "<tr><td>"; #.$row2["status"]."</td><td>".$row2["count"]."</td></tr>\n";
		                        	if ( $row2["status"] == -1 ) { print "<span class='not_ok'>";}
			                        if ( $row2["status"] ==  1 ) { print "<span class='ok'>";}
			                        if ( $row2["status"] ==  2 ) { print "<span class='warning'>";}
	
						print $statstr[$row2["status"]]."</span></td><td>".$row2["count"]."</td></tr>\n";
	
					}
		                	print "</table></td></tr>";
				}
	                	mysql_free_result($result2);
	                }
	                print "</table>";
	                print "\n\n\n";
	                mysql_free_result($result);
			break;
		case 3:
			# trendanalysis graph
			# show short cuts to former years
			$thisyear=date("Y");
			for ($j=-3;$j<=0;$j++) {
				$year1=$thisyear+$j;
				print "<a href='reports.php?report=3&year=".$year1."'>".$year1."</a> / ";
			}
			print "<hr>\n";
			# show some graphs
			for ($i=-1;$i<=2;$i++) {
				print $lang[5];
                        	if ( $i == -1 ) { print "<span class='not_ok'>";}
	                        if ( $i ==  1 ) { print "<span class='ok'>";}
	                        if ( $i ==  2 ) { print "<span class='warning'>";}
				print $statstr[$i].":</span><br>";
			print "<img src='reports.php?graph=1&sub=".$i."&month=".$month."&year=".$year."'><hr>\n";
			}
			break;
		}
}

function display_graph($graph,$sub,$datum,$month,$year,$report) {
	global $lang;
	global $monthstr;
	switch($graph) {
		case 1:
			# graph for trendanalysis
			$query  = "SELECT id,tekst from checklist order by orde desc";
	                #print "$query";
	
     	        	$result = mysql_query($query) or exit ($lang[21].mysql_error());
			$aantalkleuren=mysql_num_rows($result);
			#initialisation: filling of color table
			$color=array();
			$stapje=hexdec('FFFFFF')/$aantalkleuren;
			for ($i=0;$i<=$aantalkleuren;$i++) {
				$color[$i]=sprintf("#%06X",$i*$stapje+1);
			}
			$data=array();
			$legend=array();
			$i=0;
               	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					$i++;
					$query2="SELECT count(status) as count,month(datum) as maand from entries where ref=".$row["id"]." and status=".$sub." and year(datum)=".$year." group by month(datum) ";
	     	        		$result2 = mysql_query($query2) or exit ($lang[21].mysql_error());
					$datay=array_fill(0, 12, 0); 
					$num_rows = mysql_num_rows($result2);
					if ($num_rows>0) {
		                		while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
							$datay[$row2["maand"]]=$row2["count"];
						}
					}
					$data[$i]=$datay;
					$legend[$i]=$row["tekst"];
	                		mysql_free_result($result2);
				}
				$max=$i;
				$lineplot=array();
				$graph = new Graph(1100,500,"auto");
				$graph->SetScale("textlin");
				$graph->img->SetMargin(40,380,40,40);    
				$graph->SetScale("textlin");
				$graph->legend->Pos(0.02,0.5,"right","center");
				$graph->xaxis->SetTickLabels($monthstr);
				for ($i=1;$i<=$max;$i++) {
					if ( count($data[$i])>0 ) {
						$lineplot[$i]=new LinePlot($data[$i]);	
						$graph->Add($lineplot[$i]);
						$lineplot[$i]->SetLegend($legend[$i]);
						$lineplot[$i]->SetColor($color[$i]);
						#$lineplot[$i]->SetWeight(1);
					}
				}
				$graph->title->Set("titel");
				$graph->xaxis->title->Set($lang[14]);
				$graph->yaxis->title->Set($lang[12]);

				$graph->yaxis->SetColor("red");
				$graph->yaxis->SetWeight(2);
				$graph->SetShadow();

				$graph->Stroke();
				print "<hr>";

				mysql_free_result($result);
				break;
		}
	}

	##############################################################################
	#                            End of functions                                #
	##############################################################################
	# decide what to display, a graph (which one?) or the page itself	

require_once(STAFFINC_DIR.'header.inc.php');
	
if ( ! isset($graph) ) {
	display_page($datum,$month,$year,$report); 
} else {
	display_graph($graph,$sub,$datum,$month,$year,$report);
}
//mysql_close($link);

require_once(STAFFINC_DIR.'footer.inc.php');
?>
