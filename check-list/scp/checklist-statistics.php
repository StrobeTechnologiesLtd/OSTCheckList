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



	function display_page($datum,$month,$year) {
		global $lang;
		global $errormsg;

		print "<table>";
		print "<tr><td class='border' valign='top'>";

		print "</td>";

		print "<hr><td valign='top'><table><tr><td>";
		if ( strlen($datum)<2 ) { $datum=date("Y-m-d",time()); }
		# first row contains calendar
		$pn = array('&laquo;'=>'./checklist-statistics.php?vorige', '&raquo;'=>'./checklist-statistics.php?volgende'); 
		print "<tr><td valign='top' nowrap>\n";
		$days = array($today=>array(NULL,NULL, 
			'<span style="color: red; font-weight: bold; font-size: larger; 
			text-decoration: blink;">' .$today.'</span>')); 
		# last 2 on next line is # chars for columnname
		show_calendar("checklist-statistics.php",$year,$month);
		print "\n\n<!-- end off calendar -->\n\n";
		print "( <a href='./checklist-statistics.php'><b>".$lang[42]."</b></a> ) ";
		print "<!-- start log of date -->\n";
		print "<td valign='top'>";
		print "<font size='+2'><b>".$lang[18]." (".$datum.")?</b></font><br>\n";
		print "<img src='checklist-statistics.php?graph=1&month=".$month."'><br>";
		print "<img src='checklist-statistics.php?graph=2&month=".$month."'><br>";
		print "<img src='checklist-statistics.php?graph=3&month=".$month."'>";
		print "<font color='red'>".$errormsg."</font>\n";
		print "<!-- start build edit form -->\n";
		print "</td>";
		print "</tr>\n";
		print "</tr></td></table></td></tr>";
		print "</table>";
	}
	
	function show_average_day_score($month,$year) {
		global $lang;
		# function to calculate and displat the daily percentile scores
		# get number of checkpoints 
		$totaal=numberOfCheckpoints();
		
		# how many have daily been entered?
		$query = "SELECT day(datum) AS nr, count(distinct(ref))  AS ingevuld FROM ". CHECKLIST_TABLE_ENTRIES ." 
			WHERE month(datum)=".$month." AND year(datum)=".$year." GROUP BY day(datum) 
			ORDER BY datum" ;
		#echo "<!-- ".$query." -->\n";
		
		# initialise array
		for ($i=1;$i<=31; $i++) {
			$l2datay[$i]=0;
		}
		
		# get results
		$result = db_query($query);
		while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
			$l2datay[$row["nr"]-1]=round(($row["ingevuld"]/$totaal)*100) ;
		}
	
		// Create the graph.
		$graph = new Graph(600,200,"auto");
		$graph->SetScale("textlin");
		$graph->SetMargin(40,130,20,40);
		$graph->SetShadow();

		// Create the bar plot
		$bplot = new BarPlot($l2datay);
		$bplot->SetFillColor("orange");
		$bplot->SetLegend($lang[6]);

		// Add the plots to t'he graph
		$graph->Add($bplot);

		$graph->title->Set($lang[7]);

		$graph->xaxis->title->Set($lang[8]);
		$graph->yaxis->title->Set($lang[6]);
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

		// Display the graph
		$graph->Stroke();
	}
	

	function show_personel_activity($month,$year) {
		global $lang;
		# function for calculating who has made how many entries
	
		# how many entries  have been entered daily?
		$query = "SELECT door, count(*)  AS ingevuld FROM ". CHECKLIST_TABLE_ENTRIES ." 
			WHERE month(datum)=".$month." AND year(datum)=".$year." GROUP BY door 
			ORDER BY door" ;
		#echo "<!-- ".$query." -->\n";
		
		#get results
		$result = db_query($query);
		$i=0;
		$totaal=0;
		while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
			$databarx[$i]=$row["door"];
			$databary[$i]=$row["ingevuld"];
			$totaal+=$row["ingevuld"];
			$i++;
		}
		$aantal=$i-1;
		for ($teller=0;$teller<=$aantal;$teller++) {
			$databary[$teller]=round(($databary[$teller]/$totaal)*100,2);
		}
		array_multisort ($databarx, $databary); 

		// Create the graph.
		$graph = new Graph(600,200,"auto");
		// Use a "text" X-scale
		$graph->SetScale("textlin");
		$graph->SetShadow();

		// Specify X-labels
		$graph->xaxis->SetTickLabels($databarx);
		
		// Create the bar plot
		$bplot = new BarPlot($databary);
		$bplot->SetFillColor("orange");
		$bplot->SetLegend($lang[6]);
		$bplot->SetWidth(0.4);
		$bplot->value-> Show();

		// Add the plots to t'he graph
		$graph->Add($bplot);

		$graph->title->Set(sprintf($lang[10],$year,$month));

		$graph->xaxis->title->Set($lang[11]);
		$graph->yaxis->title->Set($lang[12]);
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

		// Display the graph
		$graph->Stroke();

	}
	
	
	function show_monthly_averages() {
		global $lang;
		# function for calculating and displaying of monthly average percentile score
	
		# get number of checkpoints op
		$totaal=numberOfCheckpoints();
		
		# how may have been entered daily?
		$query = "SELECT day(datum) AS dag, month(datum) AS maand, count(distinct(ref)) AS ingevuld 
			FROM ". CHECKLIST_TABLE_ENTRIES .",". CHECKLIST_TABLE_CHECKLIST ." WHERE DATE_SUB(CURDATE(),INTERVAL 12 month)<=datum AND ref=". CHECKLIST_TABLE_CHECKLIST .".id and disabled=0 
			GROUP BY day(datum),month(datum) 
			ORDER BY year(datum),month(datum),day(datum)";
		#echo "<!-- ".$query." -->\n";
		
		# get results
		$result = db_query($query);
		$teller=0;
		$maand=0;
		$aantaldagen=0;
		while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
			if ( $maand <> $row["maand"]  and $maand <> 0) {
				# convert to average
				$databary[$teller]=$databary[$teller]/$aantaldagen;
				$databary[$teller]=$databary[$teller]/$totaal*100;
				$aantaldagen=1;
				$teller++;
				$databary[$teller]=0;
			}
			else {
				$aantaldagen++;
			}
			$maand=$row["maand"];
			$databarx[$teller]=$maand;
			$databary[$teller]+=$row["ingevuld"];
		}
		$databary[$teller]=$databary[$teller]/$aantaldagen;
		$databary[$teller]=$databary[$teller]/$totaal*100;
	
		// Create the graph.
		$graph = new Graph(600,200,"auto");
		$graph->SetScale("textlin");
		$graph->SetShadow();

		// Specify X-labels
		$graph->xaxis->SetTickLabels($databarx);
		
		// Create the bar plot
		$bplot = new BarPlot($databary);
		$bplot->SetFillColor("orange");
		$bplot->SetLegend($lang[6]);
		$bplot->value-> Show();

		// Add the plots to t'he graph
		$graph->Add($bplot);

		$graph->title->Set($lang[13]);

		$graph->xaxis->title->Set($lang[14]);
		$graph->yaxis->title->Set($lang[6]);
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

		// Display the graph
		$graph->Stroke();
	}
	


	##############################################################################
	#                            End of functions                                #
	##############################################################################
	# decide what to display, a graph (which one?) or the page itself	

	//display_page($datum,$month,$year); 
	if ( ! isset($graph) ) {
		require_once(STAFFINC_DIR.'header.inc.php');
		display_page($datum,$month,$year); 
		require_once(STAFFINC_DIR.'footer.inc.php');
	} else {
		switch ($graph) {
			case 1:
				show_average_day_score($month,$year);
				break;
			case 2:
				show_personel_activity($month,$year);
				break;
			case 3:
				show_monthly_averages();
				break;
		}
	}
?>
