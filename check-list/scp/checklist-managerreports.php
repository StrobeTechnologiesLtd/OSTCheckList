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
include (CHECKLIST_JPGRAPH.'jpgraph_pie.php');
	
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


// *** Check List Plugin - Graph Functions
// ***************************************
function display_graph($graph,$style,$month,$year,$ref) {
	global $lang;
	
	switch($graph) {
		case 1:
			// Monthly
			$sql = 'SELECT count(status) AS count, status FROM '. CHECKLIST_TABLE_ENTRIES .' WHERE ref='. $ref .' AND month(datum)='. $month .' AND year(datum)='. $year .' GROUP BY status';
			$result = db_query($sql);
			break;
		case 2:
			// Yearly
			$sql = 'SELECT count(status) AS count, status FROM '. CHECKLIST_TABLE_ENTRIES .' WHERE ref='. $ref .' AND year(datum)='. $year .' GROUP BY status';
			$result = db_query($sql);
			break;
	}
	
	while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
		if ( $row["status"] == -1 ) {
			//print $lang[45][$row["status"]] .' '. $row["count"] .'<br />';
			$xdata[] = $lang[45][$row["status"]];
			$ydata[] = $row["count"];
		}
		if ( $row["status"] ==  0 ) {
			//print $lang[45][$row["status"]] .' '. $row["count"] .'<br />';
			$xdata[] = $lang[45][$row["status"]];
			$ydata[] = $row["count"];
		}
		if ( $row["status"] ==  1 ) {
			//print $lang[45][$row["status"]] .' '. $row["count"] .'<br />';
			$xdata[] = $lang[45][$row["status"]];
			$ydata[] = $row["count"];
		}
		if ( $row["status"] ==  2 ) {
			//print $lang[45][$row["status"]] .' '. $row["count"] .'<br />';
			$xdata[] = $lang[45][$row["status"]];
			$ydata[] = $row["count"];
		}
	}
	
	switch($style) {
		case 1:
			// Bar Graph
			// Width and height of the graph
			$width = 300; $height = 200;
 
			// Create a graph instance
			$graph = new Graph($width,$height);
 
			// Specify what scale we want to use,
			// text = integer scale for the X-axis
			// int = integer scale for the Y-axis
			$graph->SetScale('textint');
 
			// Setup a title for the graph
			//$graph->title->Set('Title to be set by vars above?');
 
			// Setup titles and X-axis labels
			$graph->xaxis->title->Set('(Check Types)');
			$graph->xaxis->SetTickLabels($xdata);
 
			// Setup Y-axis title
			$graph->yaxis->title->Set('(# of Checks)');
 
			// Create the linear plot
			//$lineplot=new LinePlot($ydata);
			// Create the bar plot
			$barplot=new BarPlot($ydata);
 
			// Add the plot to the graph
			//$graph->Add($lineplot);
			// Add the plot to the graph
			$graph->Add($barplot);
 
			// Display the graph
			$graph->Stroke();
			break;
		case 2:
			// Pie Chart
			//$data = array(40,60,21,33);
 
			$graph = new PieGraph(300,200);
			$graph->SetShadow();
 
			//$graph->title->Set("A simple Pie plot");
 
			//$p1 = new PiePlot($data);
			$p1 = new PiePlot($ydata);
			$p1->SetLegends($xdata);
			$graph->Add($p1);
			$graph->Stroke();
			break;
	}
}
// ***************************************


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
			<td width="300" valign="top">
	';
				show_calendar("checklist-managerreports.php",$year,$month);
	echo 		'( <a href="checklist-managerreports.php"><b>' . $lang[42] . '</b></a> ) ';
	echo 		'<br /><br />
				<h2>Reports</h2>
	';
				display_manreportlist("checklist-managerreports.php",$year,$month);
	echo 		'<p>&nbsp;</p>
			</td>
	';
			if (! isset($_GET['act'])) {
				// has not been submitted
				echo '<td><p>Please choose the required report from the left handside.<br />
				All reports are based on the Menu_id configured in the admin screen.</p></td>';
			} else {
				// Submitted
				echo '<td valign="top">';
				
					echo '<h2>Report</h2>
						<p>Generated Report with the following Details: -<br />
						<b>Report Run On:</b> ';
						echo date("D M d, Y G:i a");
					echo '<br />';
					echo '<b>Month:</b> '. $month .'<br />';
					echo '<b>Year:</b> '. $year .'</p>';

					$sql = 'SELECT * FROM '. CHECKLIST_TABLE_CHECKLIST .' WHERE menu_id = '. $_GET['men'] .' ORDER BY orde';
					$result = db_query($sql);
					$header = false;
					echo '<table>';
					while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
						if (!$header) {
							echo '<tr><th colspan="2">';
								echo $row['tekst'];
							echo '</th></tr>';
							$header = true;
						} else {
							echo '<tr>
								<td>Reporting on:</td>
								<td>';
								echo $row['tekst'];
							echo '</td></tr>';
						}
					}
					echo '</table>';
					echo '<p>&nbsp;</p>';
					
					$sql = 'SELECT * FROM '. CHECKLIST_TABLE_CHECKLIST .' WHERE menu_id = '. $_GET['men'] .' ORDER BY orde';
					$result = db_query($sql);
					$header = true;
					while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
						if (!$header) {
							echo '<h3>'. $row['tekst'] .'</h3>';
							echo '<h4>Monthly Results</h4>';
							echo '<img src="checklist-managerreports.php?graph=1&style=1&month='.$month.'&year='.$year.'&ref='.$row['id'].'">';
							echo '<img src="checklist-managerreports.php?graph=1&style=2&month='.$month.'&year='.$year.'&ref='.$row['id'].'">';
							echo '<p>&nbsp</p>';
							echo '<h4>Yearly Results</h4>';
							echo '<img src="checklist-managerreports.php?graph=2&style=1&month='.$month.'&year='.$year.'&ref='.$row['id'].'">';
							echo '<img src="checklist-managerreports.php?graph=2&style=2&month='.$month.'&year='.$year.'&ref='.$row['id'].'">';
						} else {
							$header = false;
						}
					}
					
				echo '</td>';
			}
	echo '</tr>
	</table>
	';
}
// **************************************
	

// *** Check List Plugin - Page Logic
// **********************************
if ( ! isset($graph) ) {
	require_once(STAFFINC_DIR.'header.inc.php');
	display_page($datum,$month,$year); 
	require_once(STAFFINC_DIR.'footer.inc.php');
} else {
	display_graph($graph,$style,$month,$year,$ref);
}
// **********************************
?>
