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
$num_columns=6;
$status=0;

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

# Work out if comments / update posted
if (isset($_POST['not_ok']))  { $status=-1;}
if (isset($_POST['ok']))      { $status=1;}
if (isset($_POST['warning'])) { $status=2;}

$time = time(); 
$today = date('j',$time);
$current_user = $thisstaff->getFirstName() . ' ' . $thisstaff->getLastName();
// ********************************************


// *** Check List Plugin - Functions / Tasks
// *****************************************
# First action is to store any information that has been sent
if ($_POST['act']==2) { 
	store_form($current_user,$_POST['act'],$_POST['id'],$_POST['logmessage'],$status); 
}
// *****************************************


// *** Check List Plugin - Display
// *******************************
$ost->addExtraHeader('<link rel="stylesheet" type="text/css" href="checklist.css" />');
$ost->addExtraHeader('<link rel="stylesheet" type="text/css" href="css-pop.css" />');
$ost->addExtraHeader('<script type="text/javascript" src="css-pop.js"></script>');
$ost->addExtraHeader("<SCRIPT type='text/javascript'>
<!--
function changeSpanText(InnerText) { 
	document.getElementById('helpwords').innerText = InnerText;
	popup('popUpDiv')
} 
//-->
</SCRIPT>");

require_once(STAFFINC_DIR.'header.inc.php');

echo '<!--POPUP-->';    
echo '<div id="blanket" style="display:none;"></div>';
echo '<div id="popUpDiv" style="display:none;">';
	echo '<h2>Check List Item Help</h2>';
	echo '<br />';
	echo '<span id="helpwords"></span>';
    echo '<a href="#" onclick="popup(\'popUpDiv\')" >Click to Close Help</a>';
echo '</div>';
//echo '<a href="#" onclick="popup(\'popUpDiv\')">Click to Open CSS Pop Up</a>';
echo '<!-- / POPUP-->';

echo '<table>';
	echo '<tr>';
		echo '<td class="border" valign="top">';
			echo '&nbsp;';
		echo '</td>';
		echo '<hr>';
		echo '<td valign="top">';
			echo '<table>';
				echo '<tr>';
					echo '<td>';
						echo '<table>';
							echo '<tr>';
								echo '<td valign="top">'; 
									echo '<table>';
										echo '<tr>';
											echo '<td>';
												show_calendar("checklist.php",$year,$month);
											echo '</td>';
											echo '<td valign="top" align="center">';
												echo '<br><img src="check-list-img/Vista-folder_green.png"><br>';
												daily_percent($datum);
												echo '<br />';
												weekly_percent($datum);
												echo '<br />';
												monthly_percent($datum);
											echo '</td>';
										echo '</tr>';
									echo '</table>';
									echo '( <a href="checklist.php"><b>' . $lang[42] . '</b></a> ) ';
									# display the checklist
									display_checklist($datum);
								echo '</td>';
							echo '</tr>';
						echo '</table>';
					echo '</td>';
					echo '<td valign="top">';
						echo '<font size="+2"><b>' . $lang[41] . ' ('.$datum.')?</b></font><br />';
						if ( isset($_GET['id'])){
							edit_form($_GET['act'],$_GET['id'],$current_user);
						}
						echo '<hr>';
						display_datelog($datum);
						echo '<hr>';
						echo '<font color="red">'.$errormsg.'</font>';
					echo '</td>';
				echo '</tr>';
			echo '</table>';
		echo '</td>';
	echo '</tr>';
echo '</table>';


require_once(STAFFINC_DIR.'footer.inc.php');
// *******************************
?>