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
	
# initialisatie: 
$datum='';
$act='';

extract($_REQUEST); // Extract vars from GET to just a normal var, not good and needs updating
$num_columns=6;
$current_user = $thisstaff->getFirstName() . ' ' . $thisstaff->getLastName();


function get_row($id) {
	$query = "SELECT * FROM " . CHECKLIST_TABLE_CHECKLIST . " WHERE id=".$id."";
	$result = db_query($query);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	return $row;
}

function show_number($id,$value,$field) {
	global $lang;
	
	print "<td align=\"middle\" nowrap>";
	print "<a href='checklist-admin.php?action=u-".$field."&id=".$id."'>^</a>"; 
	if ( $field == "period" ) { $tekst2=$lang[34][$value];} else { $tekst2=$value;}
	print $tekst2;
	if ($value>0) print "<a href='checklist-admin.php?action=d-".$field."&id=".$id."'>v</a>"; 
	print "</td>\n";
}

function incr_field($id,$field) {
	global $lang;
	
	$max=1000;
	if ( $field == "period" ) { $max=count($lang[34])-1; }
	$query = "UPDATE " . CHECKLIST_TABLE_CHECKLIST . " SET ".$field."=".$field."+1 WHERE id=".$id." AND ".$field." < ".$max;
	$result = db_query($query);
}

function decr_field($id,$field) {
	$query = "UPDATE " . CHECKLIST_TABLE_CHECKLIST . " SET ".$field."=".$field."-1 WHERE id=".$id." AND ".$field." > 0";
	$result = db_query($query);
}

function move_item_up($id) {
	# function to move a checklistentrie up the list.
	# find the id of the item before us
	# since we do not display up or down arrows next to first and last entry we don't check this again here :-)
	$row1=get_row($id);
	$query = "SELECT * FROM " . CHECKLIST_TABLE_CHECKLIST . " WHERE orde=".($row1["orde"]-1)." ORDER BY orde LIMIT 1";
	$result = db_query($query);
	$row2 = db_fetch_array($result, MYSQL_ASSOC);
	$query = "UPDATE " . CHECKLIST_TABLE_CHECKLIST . " SET orde=".$row2["orde"]." WHERE id=".$row1["id"]."";
	$result = db_query($query);
	$query = "UPDATE " . CHECKLIST_TABLE_CHECKLIST . " SET orde=".$row1["orde"]." WHERE id=".$row2["id"]."";
	$result = db_query($query);
}

function move_item_down($id) {
	# function to move a checklistentrie down the list.
	# find the id of the item after us
	# since we do not display up or down arrows next to first and last entry we don't check this again here :-)
	$row1=get_row($id);
	$query = "SELECT * FROM " . CHECKLIST_TABLE_CHECKLIST . " WHERE orde=".($row1["orde"]+1)." ORDER BY orde LIMIT 1";
	$result = db_query($query);
	$row2 = db_fetch_array($result, MYSQL_ASSOC);
	$query = "UPDATE " . CHECKLIST_TABLE_CHECKLIST . " SET orde=".$row2["orde"]." WHERE id=".$row1["id"]."";
	$result = db_query($query);
	$query = "UPDATE " . CHECKLIST_TABLE_CHECKLIST . " SET orde=".$row1["orde"]." WHERE id=".$row2["id"]."";
	$result = db_query($query);
}

function change_period($id,$value) {
	# change the period of the item.
	$query = "UPDATE " . CHECKLIST_TABLE_CHECKLIST . " SET period=".$value." WHERE id=".$id."";
	$result = db_query($query);
}

function update_text($id,$value) {
	# change the text of the item.
	$query = "UPDATE " . CHECKLIST_TABLE_CHECKLIST . " SET tekst='".$value."' WHERE id=".$id."";
	$result = db_query($query);
}

function update_starttext($id,$value) {
	# change the text of the item.
	$query = "UPDATE " . CHECKLIST_TABLE_CHECKLIST . " SET start='".$value."' WHERE id=".$id."";
	$result = db_query($query);
}

function update_helptext($id,$value) {
	# change the text of the item.
	$query = "UPDATE " . CHECKLIST_TABLE_CHECKLIST . " SET help='".$value."' WHERE id=".$id."";
	$result = db_query($query);
}

function update_disabled($id,$value,$field) {
	# change the text of the item.
	$value=strval($value);
	$query = "UPDATE " . CHECKLIST_TABLE_CHECKLIST . " SET ".$field."='".$value."' WHERE id=".$id."";
	$result = db_query($query);
}

function insert_new_item() {
	$orde_no = totalnumberOfCheckpoints() + 1;
	$query = "INSERT INTO " . CHECKLIST_TABLE_CHECKLIST . " (orde) VALUES (".$orde_no.")";
	$result = db_query($query);
}

function checklist_admin($cid,$action,$value) {
	global $lang;
	
	#echo "id: ".$id.", action=".$action.", value=".$value."<br>";
	print "<a href='checklist-admin.php'>" . $lang[38] . "</a> / <a href='checklist-admin.php?action=new'>" . $lang[39] . "</a><hr>\n";
	# get the things to check from the database
	# if an entrie already exists display it in green, else in red.
	# extra: display split between parts of the day
	# extra: blink items of items due
	# extra: display color of former days and not only today
	# handle actions
	if ( substr($action,0,2)=="d-" ) { decr_field($cid,substr($action,2)); }
	if ( substr($action,0,2)=="u-" ) { incr_field($cid,substr($action,2)); }
	switch ($action) {
		case "up": 	# move this row up if possible	
			move_item_up($cid);
			break;
		case "down": 	# move this row up if possible	
			move_item_down($cid);
			break;
		case "period": 	# change period of this entry
			change_period($cid,$value);
			break;
		case "textsub":	# change period of this entry
			update_text($cid,$value);
			break;
		case "starttextsub":	# change period of this entry
			update_starttext($cid,$value);
			break;
		case "helptextsub":	# change period of this entry
			update_helptext($cid,$value);
			break;
		case "disable":	# change period of this entry
			update_disabled($cid,$value,"disabled");
			break;
		case "header":	# change period of this entry
			update_disabled($cid,$value,"header");
			break;
		case "new":	# change period of this entry
			insert_new_item();
			break;
	}


	$vandaag=date("Y-m-d",time());
	# display checklist items with links.
	$query = "SELECT * FROM " . CHECKLIST_TABLE_CHECKLIST . " ORDER BY orde";
	$result = db_query($query);
	#$rows = mysql_num_rows($result);
	
	print "<table cellspacing=5 cellpadding=5 border=0>";
		print "<tr>";
			foreach ($lang[35] as $key => $period) {
				print "<th valign=\"middle\" nowrap>";
				print $lang[35][$key]."</th>";
			}
		print "</tr>";
	
		$counter=0;
	while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
		$counter++;
		# ID
		$id=$row["id"];
		print "<tr>";

			# orde
			print "<td align=\"middle\" nowrap>";
				if ($counter!=1) { print "<a href='checklist-admin.php?action=up&id=".$id."'>^</a>"; }
				print $row["orde"];
				if ($counter!=$rows) { print "<a href='checklist-admin.php?action=down&id=".$id."'>v</a>"; }
			print "</td>\n";

			# menu_id
			show_number($id,$row["menu_id"],"menu_id");

			# indent
			show_number($id,$row["indent"],"indent");

			# header
			print "<td align=\"middle\" nowrap>";
				print "<form action='checklist-admin.php' name='form' >";
				print "<input type=hidden name='id' value='".$id."'>";
				print "<input type=hidden name='action' value='header'>";
				print "<INPUT TYPE='checkbox' NAME='value' onchange=\"form.submit()\" value='1' ";
				if ($row["header"]==1) print "checked";
				print "></form>";
			print "</td>\n";


			# period
			show_number($id,$row["period"],"period");

			# tekst
			# for future reference: 
			# http://vijayk.wordpress.com/2006/11/20/steps-to-make-an-in-place-editing-in-html-using-javascript/
			# http://24ways.org/2005/edit-in-place-with-ajax
			print "<td valign=\"left\" nowrap>";
				if ( ($action=='text') and ($id==$cid) ) {
					print "<form action='checklist-admin.php' name='form' >";
					print "<input type=hidden name='id' value='".$id."'>";
					print "<input type=hidden name='action' value='textsub'>";
					print "<font color='red'>".$lang[36]."</font><br>";
					print "<input type=text name=value onchange=\"form.submit()\" value='".$row["tekst"]."'>";
					print "</form>";
				} else {
					# display hyperlink to get to editing
					print "<a href='checklist-admin.php?action=text&id=".$id."'>";
					if ( strlen($row["tekst"])<2 ) {
						print $lang[40];
					} else {
						print $row["tekst"];
					}
					print "</a>";
				}
			print "</td>\n";

			# disabled
			print "<td align=\"middle\" nowrap>";
				print "<form action='checklist-admin.php' name='form' >";
				print "<input type=hidden name='id' value='".$id."'>";
				print "<input type=hidden name='action' value='disable'>";
				print "<INPUT TYPE='checkbox' NAME='value' onchange=\"form.submit()\" value='1' ";
				if ($row["disabled"]==1) print "checked";
				print "></form>";
			print "</td>\n";
			
			# start
			print "<td valign=\"left\" nowrap>";
				if ( ($action=='starttext') and ($id==$cid) ) {
					print "<form action='checklist-admin.php' name='form' >";
					print "<input type=hidden name='id' value='".$id."'>";
					print "<input type=hidden name='action' value='starttextsub'>";
					print "<font color='red'>".$lang[36]."</font><br>";
					print "<input type=text name=value onchange=\"form.submit()\" value='".$row["start"]."'>";
					print "</form>";
				} else {
					# display hyperlink to get to editing
					print "<a href='checklist-admin.php?action=starttext&id=".$id."'>";
					if ( strlen($row["start"])<2 ) {
						print $lang[44];
					} else {
						print $row["start"];
					}
					print "</a>";
				}
			print "</td>\n";
			
			# help
			print "<td valign=\"left\" nowrap>";
				if ( ($action=='helptext') and ($id==$cid) ) {
					print "<form action='checklist-admin.php' name='form' >";
					print "<input type=hidden name='id' value='".$id."'>";
					print "<input type=hidden name='action' value='helptextsub'>";
					print "<font color='red'>".$lang[36]."</font><br>";
					print "<input type=text name=value onchange=\"form.submit()\" value='".$row["help"]."'>";
					print "</form>";
				} else {
					# display hyperlink to get to editing
					print "<a href='checklist-admin.php?action=helptext&id=".$id."'>";
					if ( strlen($row["help"])<2 ) {
						print $lang[40];
					} else {
						print $row["help"];
					}
					print "</a>";
				}
			print "</td>\n";
		
		print "</tr>";
	}	
	print "</table>";
	print "<hr>";
	}

	##############################################################################
	#                            End of functions                                #
	##############################################################################
	# als eerste actie moeten we eventueel ingestuurde invoer gaan opslaan
	if ($act==2) { 
		store_form($current_user,$act,$id,$invoer,$status); 
		# voorkom dat ze opnieuw worden opgeslagen.
		unset($act);
		unset($id);
		unset($invoer);
	};
// ********************************************

$ost->addExtraHeader("<SCRIPT type='text/javascript'>
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
</SCRIPT>");
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
</script>");

require_once(STAFFINC_DIR.'header.inc.php');

print "<table>";
	print '<tr>';;
	print "<hr>";
		print "<td valign='top'>";
			print "<table>";
				print "<tr>";
					print "<td valign='top'>";
						checklist_admin($_GET['id'],$action,$value);
						print "<font color='red'>".$errormsg."</font>";
					print "</td>";
				print "</tr>";
			print "</table>";
		print "</td>";
	print "</tr>";
print "</table>";

require_once(STAFFINC_DIR.'footer.inc.php');	
?>