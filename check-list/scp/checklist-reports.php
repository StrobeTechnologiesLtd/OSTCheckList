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



function display_page($datum,$month,$year,$report) {
	global $lang;
	global $errormsg;

	print "<table>";
	print "<tr><td class='border' valign='top'><!-- <h1>".$lang[22]."</h1> -->";

	print "</td>";

	//global_menu();
	print "<hr><td valign='top'><table><tr><td><!-- <H1>".$lang[23]."</h1><p> -->";
	if ( strlen($datum)<2 ) { $datum=date("Y-m-d",time()); }
	# first row contains calendar
	$pn = array('&laquo;'=>'./checklist-reports.php?vorige', '&raquo;'=>'./checklist-reports.php?volgende'); 
	print "<tr><td valign='top' nowrap class=border>\n";
	$time = time(); 
	$today = date('j',$time); 
	$days = array($today=>array(NULL,NULL, 
		'<span style="color: red; font-weight: bold; font-size: larger; 
		text-decoration: blink;">' .$today.'</span>')); 
	# laatste 2 op volgende  regel is aantal chars voor kolomnaam
	show_calendar("checklist-reports.php",$year,$month);
	print "</td><td valign='top' align='center'>";
    print "<img src='check-list-img/Vista-folder_print.png'>";
    print "</td></tr></table>";
	print "<a href='checklist-reports.php'>".$lang[24]."</a><br>";

	#print "</td>\n\n<!-- end off calender -->\n\n";
	print "\n\n<!-- end off calendar -->\n\n";
	# display the possible report types
	print "<!-- start list of reports -->\n";
	print "<a href='checklist-reports.php?report=1&month=".$month."&year=".$year."'>".$lang[25]."</a><br>\n";
	print "<a href='checklist-reports.php?report=2&month=".$month."&year=".$year."'>".$lang[26]."</a><br>\n";
	print "<a href='checklist-reports.php?report=3&month=".$month."&year=".$year."'>".$lang[27]."</a><br>\n";
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
}
	
function display_report($report,$month,$year) {
	global $lang;
	switch ($report) {
		case 1:
			$query  = "SELECT ". CHECKLIST_TABLE_CHECKLIST .".tekst AS checktekst,". CHECKLIST_TABLE_ENTRIES .".datum AS datum, ". CHECKLIST_TABLE_ENTRIES .".tekst AS tekst, ". CHECKLIST_TABLE_ENTRIES .".door AS door, ". CHECKLIST_TABLE_ENTRIES .".ref AS ref, ". CHECKLIST_TABLE_ENTRIES .".status AS status ";
			$query .= " FROM ". CHECKLIST_TABLE_ENTRIES .",". CHECKLIST_TABLE_CHECKLIST ." WHERE ". CHECKLIST_TABLE_CHECKLIST .".id=". CHECKLIST_TABLE_ENTRIES .".ref AND status!=0 AND status!=1 ";
			$query .= " AND month(datum)=".$month." AND year(datum)=".$year." ORDER BY ref,datum DESC";
	                #print "$query";
					$result = db_query($query);
	                print "<table >"; 
			print "<tr><th>".$lang[28]."</th><th>".$lang[29]."</th><th>".$lang[30]."</th></tr>\n";
			$oldref=-1;
					while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
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
			break;
		case 2:
			$query  = "SELECT * FROM ". CHECKLIST_TABLE_CHECKLIST ." ORDER BY orde DESC";
			#print "$query";
			$result = db_query($query);
			print "<table border='0'>"; 
			$oldref=-1;
			while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
				# ask per checklistitem the number of entered entries this month per status
				$query2="SELECT count(status) AS count,status FROM ". CHECKLIST_TABLE_ENTRIES ." WHERE ref=".$row["id"]." AND month(datum)=".$month." AND year(datum)=".$year." GROUP BY status ";
				$result2 = db_query($query2);
				if ( $row["header"] < 1 ) {
					print "<tr><th align='left'>".$row["tekst"];
					if ( $row["disabled"]==1 ) {
						print "<font size='1' color='red'>".$lang[31]."</font>";
					}
					print "</th></tr>\n";
					print "<tr><td align='right'><table border='1'><tr><th>".$lang[32]."</th><th>".$lang[12]."</th></tr>\n";
					while ($row2 = db_fetch_array($result2, MYSQL_ASSOC)) {
						print "<tr><td>"; #.$row2["status"]."</td><td>".$row2["count"]."</td></tr>\n";
		                        	if ( $row2["status"] == -1 ) { print "<span class='not_ok'>";}
			                        if ( $row2["status"] ==  1 ) { print "<span class='ok'>";}
			                        if ( $row2["status"] ==  2 ) { print "<span class='warning'>";}
	
						print $lang[45][$row2["status"]]."</span></td><td>".$row2["count"]."</td></tr>\n";
	
					}
		                	print "</table></td></tr>";
				}
	                }
	                print "</table>";
	                print "\n\n\n";
			break;
		case 3:
			# trendanalysis graph
			# show short cuts to former years
			$thisyear=date("Y");
			for ($j=-3;$j<=0;$j++) {
				$year1=$thisyear+$j;
				print "<a href='checklist-reports.php?report=3&year=".$year1."'>".$year1."</a> / ";
			}
			print "<hr>\n";
			# show some graphs
			for ($i=-1;$i<=2;$i++) {
				print $lang[5];
                        	if ( $i == -1 ) { print "<span class='not_ok'>";}
	                        if ( $i ==  1 ) { print "<span class='ok'>";}
	                        if ( $i ==  2 ) { print "<span class='warning'>";}
				print $lang[45][$i].":</span><br>";
			print "<img src='checklist-reports.php?graph=1&sub=".$i."&month=".$month."&year=".$year."'><hr>\n";
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
			$query  = "SELECT id,tekst FROM ". CHECKLIST_TABLE_CHECKLIST ." WHERE header < 1 ORDER BY orde DESC";
	                #print "$query";
	
					$result = db_query($query);
			#$aantalkleuren=mysql_num_rows($result); ???? TEMP BELOW
			$aantalkleuren = 2000;
			#initialisation: filling of color table
			$color=array();
			$stapje=hexdec('FFFFFF')/$aantalkleuren;
			for ($i=0;$i<=$aantalkleuren;$i++) {
				$color[$i]=sprintf("#%06X",$i*$stapje+1);
			}
			$data=array();
			$legend=array();
			$i=0;
				while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
					$i++;
					$query2="SELECT count(status) AS count,month(datum) AS maand FROM ". CHECKLIST_TABLE_ENTRIES ." WHERE ref=".$row["id"]." AND status=".$sub." AND year(datum)=".$year." GROUP BY month(datum) ";
					$result2 = db_query($query2);
					$datay=array_fill(0, 12, 0); 
					if ($result2) {
						while ($row2 = db_fetch_array($result2, MYSQL_ASSOC)) {
							$datay[$row2["maand"]]=$row2["count"];
						}
					}
					$data[$i]=$datay;
					$legend[$i]=$row["tekst"];
				}
				$max=$i;
				
				$lineplot=array();
				$graph = new Graph(600,500,"auto");
				$graph->SetScale("textlin");
				#$graph->img->SetMargin(40,380,40,40);
				$graph->SetScale("textlin");
				#$graph->legend->Pos(0.02,0.5,"right","center");
				#$graph->legend->SetAbsPos(0,700,"left","bottom");
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
				#$graph->title->Set("title");
				$graph->xaxis->title->Set($lang[14]);
				$graph->yaxis->title->Set($lang[12]);

				$graph->yaxis->SetColor("red");
				$graph->yaxis->SetWeight(2);
				$graph->xaxis->SetColor("red");
				$graph->xaxis->SetWeight(2);
				$graph->SetShadow();

				$graph->Stroke();
				print "<hr>";

				break;
		}
	}

	##############################################################################
	#                            End of functions                                #
	##############################################################################
	# decide what to display, a graph (which one?) or the page itself	

if ( ! isset($graph) ) {
	require_once(STAFFINC_DIR.'header.inc.php');
	display_page($datum,$month,$year,$report); 
	require_once(STAFFINC_DIR.'footer.inc.php');
} else {
	display_graph($graph,$sub,$datum,$month,$year,$report);
}
?>
