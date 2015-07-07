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
/*$nav->addSubMenu(array('desc'=>'Reports',
						'title'=>'Check List Reports',
						'href'=>'checklist-reports.php',
						'iconclass'=>'closedTickets'
				));*/
/*$nav->addSubMenu(array('desc'=>'Search',
						'title'=>'Check List Search',
						'href'=>'checklist-search.php',
						'iconclass'=>'closedTickets'
				));*/
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

/*include (CHECKLIST_INCLUDE_DIR.'lib.php');
require (CHECKLIST_INCLUDE_DIR.'calendar.php');*/

/*$jpgraphpath="jpgraph/src/";
include ($jpgraphpath."jpgraph.php");
include ($jpgraphpath."jpgraph_line.php");
include ($jpgraphpath."jpgraph_bar.php");*/
#DEFINE ("TTF_DIR","/usr/X11R6/lib/X11/fonts/TTF/" );
	
# initialisation: 
#error_reporting(E_ALL);
/*import_request_variables("gpc");*/
extract($_REQUEST);
$num_columns=6;
$current_user=getenv('REMOTE_USER');
if ( ! isset($datum) ) { 
	$datum=date("Y-m-d",time()); 
} else {
	list($year,$month, $day) = split('[/.-]', $datum);
}	
if ( ! isset($month) ) { $month=date("m"); }
if ( ! isset($year) ) { $year=date("Y"); }

// ********************************************



	function display_page($datum,$month,$year) {
		global $lang;
		global $errormsg;

		print "<table>";
		print "<tr><td class='border' valign='top'><!-- <h1>Menu</h1> -->";
		#print "<!-- user ".$current_user."<br> -->";
		#print "<!-- <a href='index.php?f=1'>New</a><br> -->";

		print "</td>";
		//global_menu();

		print "<hr><td valign='top'><table><tr><td><!-- <H1>Main</h1><p> -->";
		if ( strlen($datum)<2 ) { $datum=date("Y-m-d",time()); }
		# first row contains calendar
		$pn = array('&laquo;'=>'./checklist.php?vorige', '&raquo;'=>'./checklist.php?volgende'); 
		print "<tr><td valign='top' nowrap>\n";
		$time = time(); 
		$today = date('j',$time); 
		$days = array($today=>array(NULL,NULL, 
			'<span style="color: red; font-weight: bold; font-size: larger; 
			text-decoration: blink;">' .$today.'</span>')); 
		# last 2 on next line is # chars for columnname
		show_calendar("checklist-statistics.php",$year,$month);
		#print "</td>\n\n<!-- end off calendar -->\n\n";
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
		$query = "SELECT day(datum) as nr, count(distinct(ref))  as ingevuld FROM entries 
			where month(datum)=".$month." and year(datum)=".$year." group by day(datum) 
			order by datum" ;
		#echo "<!-- ".$query." -->\n";
		# initialise array
		for ($i=1;$i<=31; $i++) {
			$l2datay[$i]=0;
		}
		# get results
		$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			#print $row["dag"]." = array('./index.php?datum='".$row["dag"].",'linked-day'";
			$l2datay[$row["nr"]-1]=round(($row["ingevuld"]/$totaal)*100) ;
			#print $row["nr"]." - ".$row["ingevuld"]." - ".$l2datay[$row["nr"]]."<br>";
		}
	

		// Create the graph.
		$graph = new Graph(600,200,"auto");
		$graph->SetScale("textlin");
		$graph->SetMargin(40,130,20,40);
		$graph->SetShadow();

		// Create the linear error plot
		#$l1plot=new LinePlot($l1datay);
		#$l1plot->SetColor("red");
		#$l1plot->SetWeight(2);
		#$l1plot->SetLegend("Entered % per day");

		//Center the line plot in the center of the bars
		#$l1plot->SetBarCenter();

		// Create the bar plot
		$bplot = new BarPlot($l2datay);
		$bplot->SetFillColor("orange");
		$bplot->SetLegend($lang[6]);

		// Add the plots to t'he graph
		$graph->Add($bplot);
		#$graph->Add($l1plot);

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
		$query = "SELECT door, count(*)  AS ingevuld FROM entries 
			where month(datum)=".$month." and year(datum)=".$year." GROUP BY door 
			ORDER BY door" ;
		#echo "<!-- ".$query." -->\n";
		#get results
		$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
		$i=0;
		$totaal=0;
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			#print $row["dag"]." = array('./index.php?datum='".$row["dag"].",'linked-day'";
			$databarx[$i]=$row["door"];
			$databary[$i]=$row["ingevuld"];
			$totaal+=$row["ingevuld"];
			#print $row["door"]."-".$row["ingevuld"]."-".$databarx[$i]."-".$databary[$i]."<br>";
			$i++;
		}
		$aantal=$i-1;
		for ($teller=0;$teller<=$aantal;$teller++) {
			$databary[$teller]=round(($databary[$teller]/$totaal)*100,2);
		}
		# joke.... Just uncomment and enter here the name of your boss :-)
		#array_push($databarx,"kostermm");
		#array_push($databary,-15.3);
		array_multisort ($databarx, $databary); 
	
		#$datax=$gDateLocale->GetShortMonth();

		// Create the graph.
		$graph = new Graph(600,200,"auto");
		// Use a "text" X-scale
		$graph->SetScale("textlin");
		#$graph->SetMargin(40,130,20,40);
		$graph->SetShadow();
		#$graph->xaxis->SetTickLabels($datax);

		//Center the line plot in the center of the bars
		#$l1plot->SetBarCenter();

		// Specify X-labels
		$graph->xaxis->SetTickLabels($databarx);
		#$graph->xaxis->SetTextLabelInterval(1);
		#$graph->xaxis->SetTextTickInterval(3);
		// Create the bar plot
		$bplot = new BarPlot($databary);
		$bplot->SetFillColor("orange");
		$bplot->SetLegend($lang[6]);
		$bplot->SetWidth(0.4);
		$bplot->value-> Show();
		#$bplot->value->SetAngle(45); 

		// Add the plots to t'he graph
		$graph->Add($bplot);
		#$graph->Add($l1plot);

		$graph->title->Set(sprintf($lang[10],$year,$month));

		$graph->xaxis->title->Set($lang[11]);
		#$graph->xaxis->SetTextLabelIntervall(4 );
		#$graph->xaxis->SetLabelMargin(50);
		$graph->yaxis->title->Set($lang[12]);
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		#$graph->title->SetFont(FF_ARIAL,FS_BOLD);
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
		$query = "SELECT day(datum) as dag, month(datum) as maand, count(distinct(ref))  as ingevuld 
			FROM entries,checklist where DATE_SUB(CURDATE(),INTERVAL 12 month)<=datum and ref=checklist.id and disabled=0 
			group by day(datum),month(datum) 
			order by year(datum),month(datum),day(datum)";

		#echo "<!-- ".$query." -->\n";
		# initialise array
		#for ($i=1;$i<=31; $i++) {
		#	$l2datay[$i]=0;
		#}
		# get results
		$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
		$teller=0;
		$maand=0;
		$aantaldagen=0;
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if ( $maand <> $row["maand"]  and $maand <> 0) {
				# convert to average
				$databary[$teller]=$databary[$teller]/$aantaldagen;
				$databary[$teller]=$databary[$teller]/$totaal*100;
				$aantaldagen=1;
				# houskeeping work
				#print "$teller , $maand, $databary[$teller]<br>";
				$teller++;
				$databary[$teller]=0;
			}
			else {
				$aantaldagen++;
			}
			$maand=$row["maand"];
			$databarx[$teller]=$maand;
			$databary[$teller]+=$row["ingevuld"];
			#$databary[$teller]+=$row["ingevuld"];
			
			#print $row["dag"]." = array('./index.php?datum='".$row["dag"].",'linked-day'";
			#$l2datay[$row["nr"]-1]=round(($row["ingevuld"]/$totaal)*100) ;
			#print $row["nr"]." - ".$row["ingevuld"]." - ".$l2datay[$row["nr"]]."<br>";
		}
		$databary[$teller]=$databary[$teller]/$aantaldagen;
		$databary[$teller]=$databary[$teller]/$totaal*100;
		#print "$teller , $maand, $databary[$teller]<br>";
	
		#$datax=$gDateLocale->GetShortMonth();

		// Create the graph.
		$graph = new Graph(600,200,"auto");
		$graph->SetScale("textlin");
		#$graph->SetMargin(40,130,20,40);
		$graph->SetShadow();
		#$graph->xaxis->SetTickLabels($datax);

		// Create the linear error plot
		#$l1plot=new LinePlot($l1datay);
		#$l1plot->SetColor("red");
		#$l1plot->SetWeight(2);
		#$l1plot->SetLegend("Ingevuld % per dag");

		//Center the line plot in the center of the bars
		#$l1plot->SetBarCenter();

		// Specify X-labels
		$graph->xaxis->SetTickLabels($databarx);
		// Create the bar plot
		$bplot = new BarPlot($databary);
		$bplot->SetFillColor("orange");
		$bplot->SetLegend($lang[6]);
		$bplot->value-> Show();

		// Add the plots to t'he graph
		$graph->Add($bplot);
		#$graph->Add($l1plot);

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
	
require_once(STAFFINC_DIR.'header.inc.php');

	/*if ( ! isset($graph) ) { 
		display_page($datum,$month,$year); 
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
	}*/
	display_page($datum,$month,$year); 
	
require_once(STAFFINC_DIR.'footer.inc.php');
?>
