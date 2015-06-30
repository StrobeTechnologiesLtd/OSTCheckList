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
//include (CHECKLIST_INCLUDE_DIR.'settings.php');
include (CHECKLIST_INCLUDE_DIR.'lib.php');
require (CHECKLIST_INCLUDE_DIR.'calendar.php');

//extract($_REQUEST);
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
/*$ost->addExtraHeader("<script type='text/javascript'>
<!--
function switchMenu(obj) {
        var el = document.getElementById(obj);
        if ( el.style.display != 'none' ) {
                el.style.display = 'none';
        }
        else {
                el.style.display = '';
        }
}
//-->
</script>");
$ost->addExtraHeader("<script language='javascript'>
function toggleLayer(whichLayer) {
	if (document.getElementById) {
		var style2 = document.getElementById(whichLayer).style;
		style2.display = style2.display? '':'block';
	} else if (document.all) {
		var style2 = document.all[whichLayer].style;
		style2.display = style2.display? '':'block';
	} else if (document.layers) {
		var style2 = document.layers[whichLayer].style;
		style2.display = style2.display? '':'block';
	}
}
</script>");*/
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
						# first row contains calendar
//$pn = array('&laquo;'=>'./checklist.php?vorige', '&raquo;'=>'./checklist.php?volgende'); 
						echo '<table>';
							echo '<tr>';
								echo '<td valign="top">'; 
//$days = array($today=>array(NULL,NULL,"<span style='to_late'>".$today."</span>")); 
									# last 2 on next line is # char for columnname
									echo '<table>';
										echo '<tr>';
											echo '<td>';
												show_calendar("checklist.php",$year,$month);
											echo '</td>';
											echo '<td valign="top" align="center">';
												echo '<br><img src="check-list-img/Vista-folder_green.png"><br>';
												//show_percentage($datum);
												//echo '<br />';
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
									//display_checklist($datum,$wikiurl,$uses_wiki);
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

//mysql_close($link);

require_once(STAFFINC_DIR.'footer.inc.php');
// *******************************
?>