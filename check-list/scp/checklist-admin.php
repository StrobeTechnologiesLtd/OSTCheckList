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
$version="Version$Revision: 0.0.2 $ (Date$Date: 29/06/2015 07:31:00 $)";
$version=str_replace("$", "",$version);
$errormsg="";

include (CHECKLIST_INCLUDE_DIR.'settings.php');
include (CHECKLIST_INCLUDE_DIR.'lib.php');
require (CHECKLIST_INCLUDE_DIR.'calendar.php');
	
# initialisatie: 
#error_reporting(E_ALL);
$datum='';
$act='';
/*import_request_variables("gpc");*/
extract($_REQUEST);
$num_columns=6;
//$current_user=getenv('REMOTE_USER');
$current_user = $thisstaff->getFirstName() . ' ' . $thisstaff->getLastName();
if ( strlen($current_user)<2 and isset($user) ) {
	$current_user=$user;
}
if ( strlen($datum)<2 ) { 
	$datum=date("Y-m-d",time()); 
} else {
	list($year,$month, $day) = split('[/.-]', $datum);
}	
if ( ! isset($month) ) { $month=date("m"); }
if ( ! isset($year) )  { $year=date("Y"); }
$status=0;
if (   isset($not_ok))  { $status=-1;}
if (   isset($ok))      { $status=1;}
if (   isset($warning)) { $status=2;}


function get_row($id) {
	$query = "SELECT * FROM checklist where id=".$id."";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
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
	$query = "update checklist set ".$field."=".$field."+1 where id=".$id." and ".$field." < ".$max;
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
}
function decr_field($id,$field) {
	global $lang;
	$query = "update checklist set ".$field."=".$field."-1 where id=".$id." and ".$field." > 0";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
}

function move_item_up($id) {
	# function to move a checklistentrie up the list.
	# find the id of the item before us
	# since we do not display up or down arrows next to first and last entry we don't check this again here :-)
	$row1=get_row($id);
	//$query = "SELECT * FROM checklist where orde<".$row1["orde"]." order by orde limit 1";
	$query = "SELECT * FROM checklist where orde=".($row1["orde"]-1)." order by orde limit 1";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
	//$row2 = mysql_fetch_array($result, MYSQL_ASSOC);
	$row2 = db_fetch_array($result, MYSQL_ASSOC);
	echo "id ".$row1["id"]." with orde ".$row1["orde"]." will become ".$row2["orde"]."<br>\n";
	echo "id ".$row2["id"]." with orde ".$row2["orde"]." will become ".$row1["orde"]."<br>\n";
	$query = "update checklist set orde=".$row2["orde"]." where id=".$row1["id"]."";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
	$query = "update checklist set orde=".$row1["orde"]." where id=".$row2["id"]."";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
}

function move_item_down($id) {
	# function to move a checklistentrie down the list.
	# find the id of the item after us
	# since we do not display up or down arrows next to first and last entry we don't check this again here :-)
	$row1=get_row($id);
	//$query = "SELECT * FROM checklist where orde>".$row1["orde"]." order by orde limit 1";
	$query = "SELECT * FROM checklist where orde=".($row1["orde"]+1)." order by orde limit 1";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
	//$row2 = mysql_fetch_array($result, MYSQL_ASSOC);
	$row2 = db_fetch_array($result, MYSQL_ASSOC);
	#echo "id ".$row1["id"]." with orde ".$row1["orde"]." will become ".$row2["orde"]."<br>\n";
	#echo "id ".$row2["id"]." with orde ".$row2["orde"]." will become ".$row1["orde"]."<br>\n;
	$query = "update checklist set orde=".$row2["orde"]." where id=".$row1["id"]."";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
	$query = "update checklist set orde=".$row1["orde"]." where id=".$row2["id"]."";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
}

function change_period($id,$value) {
	# change the period of the item.
	$query = "update checklist set period=".$value." where id=".$id."";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
}

function update_text($id,$value) {
	# change the text of the item.
	$query = "update checklist set tekst='".$value."' where id=".$id."";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
}

function update_disabled($id,$value,$field) {
	# change the text of the item.
	$value=strval($value);
	$query = "update checklist set ".$field."='".$value."' where id=".$id."";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
}

function insert_new_item() {
	$orde_no = totalnumberOfCheckpoints() + 1;
	//$query = "insert into checklist (id) values (0) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)";
	$query = "insert into checklist (orde) values (".$orde_no.")";
	#INSERT INTO table (a,b,c) VALUES (1,2,3) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id), c=3;
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
}

function checklist_admin($cid,$action,$value) {
	global $lang;
	#echo "id: ".$id.", action=".$action.", value=".$value."<br>";
	print "<a href='checklist-admin.php'>".$lang[38]."</a> / <a href='checklist-admin.php?action=new'>".$lang[39]."</a><hr>\n";
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
	print "<table cellspacing=5 cellpadding=5 border=0>";

	# display checklist items with links.
	$query = "SELECT * FROM checklist ORDER BY orde";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
	$rows = mysql_num_rows($result);
	print "<tr>";
	foreach ($lang[35] as $key => $period) {
		print "<th valign=\"middle\" nowrap>";
		print $lang[35][$key]."</th>";
	}
	print "</tr>";
	$counter=0;
	//while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
		$counter++;
		# ID
		$id=$row["id"];
		print "<tr>";
	    print "<td valign=\"middle\" nowrap>".$id."</td>";

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
		#show_number($id,$row["header"],"header");
        print "<td align=\"middle\" nowrap>";
		print "<form action='checklist-admin.php' name='form' >";
		print "<input type=hidden name='id' value='".$id."'>";
		print "<input type=hidden name='action' value='header'>";
		print "<INPUT TYPE='checkbox' NAME='value' onchange=\"form.submit()\" value='1' ";
		if ($row["header"]==1) print "checked";
			print "></form>";
			#print $row["disabled"];
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
			#print $row["disabled"];
			print "</td>\n";
			
		# part of the day
		show_number($id,$row["dagdeel"],"dagdeel");
        #$print "<td align=\"middle\" nowrap>".$row["dagdeel"]."</td>\n";

		print "</tr>";
			
		}
		print "</table>";
		print "<hr>";
		# display edit form to change the parts of the day. TODO :-)
		print "<table cellspacing=5 cellpadding=5 border=0>";
		$query = "SELECT * FROM dagdelen ORDER BY id";
		//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
		$result = db_query($query);
		$rows = mysql_num_rows($result);
		print "<tr>";
		foreach ($lang[37] as $key => $period) {
			print "<th valign=\"middle\" nowrap>";
			print $lang[37][$key]."</th>";
		}
		print "</tr>";
		$counter=0;
		//while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
			$counter++;
			# ID
		print "<tr>";
			$id=$row["id"];
			print "<td>".$id."</td>";
			print "<td>".$row["nummer"]."</td>";
			print "<td>".$row["starttijd"]."</td>";
			print "<td>".$row["eindtijd"]."</td>";
		print "</tr>";
		}
		//mysql_free_result($result);

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
print '<tr><td class="border" valign="top"><!-- <h1>Menu</h1> -->';
print "<!-- Gebruiker <? print $current_user; ?><br> -->";
print "<!-- <a href='checklist.php?f=1'>Nieuw</a><br> -->";
print "</td>";

print "<hr>";
	print "<td valign='top'><table><tr><td><!-- <H1>Main</h1><p> -->\n";
	if ( strlen($datum)<2 ) { $datum=date("Y-m-d",time()); }
	# first row contains calendar
	$pn = array('&laquo;'=>'./checklist.php?vorige', '&raquo;'=>'./checklist.php?volgende'); 
	print "<tr><td valign='top'>\n";
	setlocale(LC_TIME, 'nl_NL'); #dutch 
	$time = time(); 
	$today = date('j',$time); 
	$days = array($today=>array(NULL,NULL,"<span style='to_late'>".$today."</span>")); 
	# display checklist items
	//display_checklist($datum,$wikiurl,$uses_wiki);
	display_checklist($datum);
	print "</td>";
	print "<td valign='top'>";
	checklist_admin($_GET['id'],$action,$value);
	#print "<hr>\n";
	print "<font color='red'>".$errormsg."</font>\n";
	print "</td>";
	print "</tr>\n";
	print "</tr></td></table></td></tr>";
	print "</table>";
	print "<font size='1'>".$version."</font><br>"; 
	mysql_close($link);

require_once(STAFFINC_DIR.'footer.inc.php');	
?>