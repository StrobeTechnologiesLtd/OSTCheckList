<?php
// *** osTicket - Includes
// ***********************
require('staff.inc.php');
// ***********************


// *** osTicket - Menu and Navigation
// **********************************
$nav->setTabActive('apps');
/*$nav->addSubMenu(array('desc'=>'Statistics',
						'title'=>'Check List Statistics',
						'href'=>'checklist-statistics.php',
						'iconclass'=>'closedTickets'
				));*/
/*$nav->addSubMenu(array('desc'=>'Reports',
						'title'=>'Check List Reports',
						'href'=>'checklist-reports.php',
						'iconclass'=>'closedTickets'
				));*/
/*$nav->addSubMenu(array('desc'=>'Search',
						'title'=>'Check List Search',
						'href'=>'checklist-search.php',
						'iconclass'=>'closedTickets'
				)); */
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
include (CHECKLIST_INCLUDE_DIR.'lib.php');
require (CHECKLIST_INCLUDE_DIR.'calendar.php');

$num_columns=6;
$status=0;

if ( strlen($_GET['datum'])<2 ) { 
	$datum=date("Y-m-d",time()); 
} else {
	$datum = $_GET['datum'];
	list($year,$month, $day) = split('[/.-]', $_GET['datum']);
}	
if ( ! isset($month) ) { $month=date("m"); }
if ( ! isset($year) )  { $year=date("Y"); }

if (   isset($_POST['not_ok']))  { $status=-1;}
if (   isset($_POST['ok']))      { $status=1;}
if (   isset($_POST['warning'])) { $status=2;}

setlocale(LC_TIME, 'uk_en');
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

require_once(STAFFINC_DIR.'header.inc.php');

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
									//echo '( <a href="checklist.php"><b>'.$lang[42].'</b></a> ) ';
									echo '( <a href="checklist.php"><b>Goto today!</b></a> ) ';
									# display the checklist
									display_checklist($datum);
								echo '</td>';
							echo '</tr>';
						echo '</table>';
					echo '</td>';
					echo '<td valign="top">';
						//echo '<font size="+2"><b>'.$lang[41].' ('.$datum.')?</b></font><br />';
						echo '<font size="+2"><b>What has happened today ('.$datum.')?</b></font><br />';
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