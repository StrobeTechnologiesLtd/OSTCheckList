<?
# load language file
include (CHECKLIST_INCLUDE_DIR."lang_".$locale.".php");

/*$link = mysql_connect($dbhost,$dbuser,$dbpassword)
	or die($lang[15].mysql_error());
mysql_select_db($database) or die($lang[16]);*/
	
function getPartOfDay() {
	global $lang;
	# get the current part of the day
	$query = "SELECT nummer from dagdelen where time(now())>=starttijd 
		and time(now())< eindtijd"; # WHERE id=".$id." ";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	$dagdeel=$row["nummer"];
	mysql_free_result($result);
	return $dagdeel;
}

function show_calendar($file, $year, $month) {
	//global $lang;
	# get current month from database and mark the dates that have entries
	# select distinct date(datum) from entries where month(datum)=10 and year(datum)=2006;
	$days=array();
	$query = "select distinct day(datum) as dag from entries where month(datum)=" 
		.$month." and year(datum)=".$year;
	//$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
	$result = db_query($query);
	//while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
		$days[$row["dag"]] = array('./checklist.php?datum='.
			$year."-".$month."-".$row["dag"],'linked-day') ;
	}
	# make links to month before and month after
	$pn = array(	'&laquo;'=>"./".$file."?month=".($month-1) , 
			'&raquo;'=>"./".$file."?month=".($month+1) ); 
	# show calendar 
	echo generate_calendar($year, $month, $days,2,NULL,0,$pn); 
}

function numberOfCheckpoints() {
	global $lang;
	# get the number of 
	$query = "SELECT count(*) AS total FROM checklist where disabled != true and header=0";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	$total=$row["total"];
	//mysql_free_result($result);
	return $total;
}

function totalnumberOfCheckpoints() {
	global $lang;
	# get the number of 
	$query = "SELECT count(*) AS total FROM checklist";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	$total=$row["total"];
	//mysql_free_result($result);
	return $total;
}

function numberOfEnteredCheckpointsToday() {
	global $lang;
	$query = "SELECT count(distinct(ref))  AS entered FROM entries WHERE date(datum) LIKE date(now())";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	$entered=$row["entered"];
	//mysql_free_result($result);
	return $entered;
}

function numberOfChecks($period) {
	global $lang;
	$query = "SELECT count(*)  AS number FROM checklist WHERE period=".$period." and header=0";
	//$result = mysql_query($query) or exit ($lang[21].mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	$number=$row["number"];
	//mysql_free_result($result);
	return $number;
}

function display_checklist($datum){
	global $lang; //?????
	
	$vandaag=date("Y-m-d",time()); //vandaag is Dutch for today
	# fill an array to use later
	$ref=array();
	
	# get the things to check from the database
		# if an entrie already exists display it in green, else in red.
		# extra: display split between parts of the day
		# extra: blink items of items due
		# extra: display color of former days and not only today
	echo '<table cellspacing="0" cellpadding="0" border="0">';
	# get the thing already done today
	# expand query with weekly and monthly checks
	$query = "SELECT entries.ref as ref,entries.datum as datum,checklist.period as period ";
	$query .= " from entries,checklist  where entries.ref=checklist.id ";
	$query .= " and (    ( period=0 and date(datum)=date('".$datum."')  ) ";
	$query .= " or ( period=1 and week(date(datum))=week(date('".$datum."')) )";
	$query .= " or ( period=2 and month(date(datum))=month(date('".$datum."')) )    )";
	#print "\n<!-- $query -->\n";
	//$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
	$result = db_query($query);
	//while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
		$ref[$row["ref"]]=1;
	}
	print "\n\n\n";
	//mysql_free_result($result);
	//$dagdeel=getPartOfDay(); //?? (dagdeel is Dutch for day part)


	# display checklist with link if applicable.
	$query = "SELECT * FROM checklist where disabled != true ORDER BY dagdeel,orde";
	//$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
	$result = db_query($query);
	$oudedagdeel=1;
		echo '<tr>';
			//echo '<td valign="middle" nowrap>';
			echo '<td valign="middle">';
				//while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
					if ($oudedagdeel < $row["dagdeel"] ) {
						$oudedagdeel=$row["dagdeel"];
						print "<hr>\n";
					}
					$menu=$row["menu_id"];
					for ($i=0;$i<$row["indent"];$i++) {
						print "&nbsp;&nbsp;";
					}
					switch ($row["header"]) {
						case 0:
						#this is a normal line
							if (    (! isset($datum) or (strtotime($vandaag)==strtotime($datum)) )  ) {
								print "<a href='checklist.php?act=1&id=".$row["id"]."'>\n";	
							}
							if (isset($ref[$row["id"]]) and $ref[$row["id"]]==1) {
								# key known so info in database
								print "<font color='green'>";
							} else {
								print "<font color='red' ";
								# check if item is in former part of day. if so: blink!!
								if ( ($dagdeel> $row["dagdeel"]) and ($vandaag==$datum) ) {	
									print " class='blinking' ";
								}
								print ">";
							}
							print $row["tekst"];
							print "</font>";
							print "</a>";
							print "<br>\n";
							break;
						case 1:
						# this is a header that functions as a menu too
							print "<span class='kop'>"; 
							//print "<a href=\"javascript:toggleLayer('myvar".$menu."');\"> ";
							//echo $menu;
							print $row["tekst"];
							print "</a>";
							print "</span>";
							print "\n";
							echo '<br />';
							//print "<DIV class='hidden' id='myvar".$menu."' >\n";	
							break;
						case 2:
						# this is only a header
							print "<span class='kop'>"; 
							print $row["tekst"];
							print "</span>";
							print "<br>\n";
							break;
						case -1:
						# this is the end of a menu
							//print "</DIV>";	
							print "<br>\n";
							break;
					}
				}
			echo '</td>';
		echo '</tr>';
	echo '</table>';
	
	//mysql_free_result($result);
}

function display_datelog($datum) {
	global $lang;
	# this funrction display the entries of a certain date.
	if ( strtotime($datum) != strtotime(date("Y-m-d",time())) ) {
		print "<hr><font color='red'><b>";
		print $lang[43];
		print "</b></font><hr>\n";
	}
	if ( strlen($datum)<2 ) { $datum=date("Y-m-d",time()); }	
	$heading=$lang[34];
	for ($period=0;$period<=2;$period++) {
		switch ($period) {
			case 0:
				# daily checks
				$query  = "SELECT *,entries.tekst as tekst, checklist.tekst as tekst2 FROM entries,checklist WHERE ";
				$query .= " entries.ref=checklist.id and checklist.period=0 and date(datum) like date('".$datum."') ORDER BY datum ASC";
				break;
			case 1:
				# weekly checks
				$query  = "SELECT *,entries.tekst as tekst, checklist.tekst as tekst2 FROM entries,checklist WHERE ";
				$query .= " entries.ref=checklist.id and checklist.period=1 and week(date(datum)) like week(date('".$datum."')) ORDER BY datum ASC";
				break;
			case 2:
				# monthly checks
				$query  = "SELECT *,entries.tekst as tekst, checklist.tekst as tekst2 FROM entries,checklist WHERE ";
				$query .= " entries.ref=checklist.id and checklist.period=2 and month(date(datum)) like month(date('".$datum."')) ORDER BY datum ASC";
				break;
		}
		echo "<!-- ".$query." -->\n";
		# start display of section
		if ( numberOfChecks($period)>0 ) {
			print "<h2>".$heading[$period]."</h2>";
			//$result = mysql_query($query) or exit ("Ongeldige query " . mysql_error()); 
			$result = db_query($query);
			print "<ul>";
			//while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			while ($row = db_fetch_array($result, MYSQL_ASSOC)) {
				print "<li>";
				if ( $row["status"] == -1 ) { print "<span class='not_ok'>";}
				if ( $row["status"] ==  1 ) { print "<span class='ok'>";}
				if ( $row["status"] ==  2 ) { print "<span class='warning'>";}
				print "<b>".$row["tekst2"]."</b></span>\n";	
				print "<span class='taskwho'>( ".$row["datum"]." by ".$row["door"]." )</span>"; //door is Dutch for by
				print "<br>\n";
				if ( $row["status"] !=  0 ) { print "</span>";}
				print "<span class='code'>".rtrim($row["tekst"])."</span>";
				print "</li>\n";
			}
			print "</ul>";
			print "<hr>\n";
		}
	}
}

function edit_form($act,$id,$current_user) {
	//global $lang;
	if ( isset($act)  ) {
		$query = "SELECT * FROM checklist WHERE id=".$id." ";
		//$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
		$result = db_query($query);
		//$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$row = db_fetch_array($result, MYSQL_ASSOC);
		echo '<h2>'.$row['tekst'].'</h2>';
		//mysql_free_result($result);
		echo '<form method="post" action="checklist.php">';
			csrf_token();
			echo '<input type="hidden" name="act" value="2">';
			echo '<input type="hidden" name="id" value="'.$id.'">';
			echo '<textarea name="logmessage" cols="60" rows="10" ></textarea><br>';
			echo '<input class="button_ok" type="submit" name="ok" value="Good">';
			echo '<input class="button_not_ok" type="submit" name="not_ok" value="Error">';
			echo '<input class="button_warning" type="submit" name="warning" value="Attention">';
			echo '<input type="submit" name="neutral" value="Neutral">';
		echo '</form>';
	}
}

function store_form($current_user,$act,$id,$logmessage,$status) {
	global $lang;
	if ( ($act==2) ) {
		#storage of data
		$escaped_logmessage = mysql_escape_string($logmessage);
		$query  = "INSERT INTO entries (door,datum,ref,tekst,status) values('";
		$query .= $current_user."',now(),".$id.",'".$escaped_logmessage."',".$status.")\n";
		//$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error());
		$result = db_query($query);
	} else {
		$errormsg .= $lang[1];
	}
}
	
/*function show_percentage($datum) {
	//global $lang;
	# get number of checkpoints
	$query = "SELECT count(*) as total FROM checklist where disabled !=true and header=0 ";
	$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$total=$row["total"];
	mysql_free_result($result);
	# get number of today entered checkpoints 
	$query = "SELECT count(distinct(ref)) as entered FROM entries where date(datum) like date('".$datum."')";
	$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$entered=$row["entered"];
	mysql_free_result($result);
	# show percentage 
	printf ("<font size='20'>%1.0f%%</font>\n",($entered/$total)*100  );
}*/

function daily_percent($date) {
	// Get Daily total of checks to perform
	$query = "SELECT count(*) as total FROM checklist where disabled !=true AND header=0 AND period=0";
	//$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	$total=$row["total"];
	//mysql_free_result($result);
	
	// Get Daily processed total
	$query = "SELECT *,entries.tekst as tekst, checklist.tekst as tekst2, count(distinct(ref)) as entered FROM entries,checklist WHERE entries.ref=checklist.id AND checklist.period=0 AND date(datum) LIKE date('".$date."')";
	//$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	$entered=$row["entered"];
	//mysql_free_result($result);
	
	// Print Percentage
	printf ("<font size='5px'>Daily %1.0f%%</font>\n",($entered/$total)*100  );
}

function weekly_percent($date) {
	// Get Weekly total of checks to perform
	$query = "SELECT count(*) as total FROM checklist where disabled !=true AND header=0 AND period=1";
	//$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	$total=$row["total"];
	//mysql_free_result($result);
	
	// Get Weekly processed total
	$query = "SELECT *,entries.tekst as tekst, checklist.tekst as tekst2, count(distinct(ref)) as entered FROM entries,checklist WHERE entries.ref=checklist.id AND checklist.period=1 AND week(date(datum)) LIKE week(date('".$date."'))";
	//$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	$entered=$row["entered"];
	//mysql_free_result($result);
	
	// Print Percentage
	printf ("<font size='5px'>Weekly %1.0f%%</font>\n",($entered/$total)*100  );
}

function monthly_percent($date) {
	// Get Monthly total of checks to perform
	$query = "SELECT count(*) as total FROM checklist where disabled !=true AND header=0 AND period=2";
	//$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	$total=$row["total"];
	//mysql_free_result($result);
	
	// Get Monthly processed total
	$query = "SELECT *,entries.tekst as tekst, checklist.tekst as tekst2, count(distinct(ref)) as entered FROM entries,checklist WHERE entries.ref=checklist.id AND checklist.period=2 AND month(date(datum)) LIKE month(date('".$date."'))";
	//$result = mysql_query($query) or exit ("Invalid Query: " . mysql_error()); 
	$result = db_query($query);
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$row = db_fetch_array($result, MYSQL_ASSOC);
	$entered=$row["entered"];
	//mysql_free_result($result);
	
	// Print Percentage
	printf ("<font size='5px'>Monthly %1.0f%%</font>\n",($entered/$total)*100  );
}

?>
