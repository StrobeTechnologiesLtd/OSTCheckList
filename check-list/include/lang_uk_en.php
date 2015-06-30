<?php
# English language file
# Version: 1.1

global $month,$lang,$locale;

setlocale(LC_TIME, 'uk_en');
$monthstr=array("Jan","Feb","Mar","Apr","Mai","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
$statstr=array(-1 => "Error",0 => "Neutral",1 => "Good",2 => "Attention");

$lang[1]="Not enough text has been entered!";
$lang[2]="User: ";
$lang[3]="Select on the left a report for month %s and year %s\n";
$lang[4]="No additional text has been entered.";
$lang[5]="Graph for status ";
$lang[6]="Percentage";
$lang[7]="Percentile daily stored items";
$lang[8]="Day";
$lang[10]="Who did in %s/%s the most (percentile score)?";
$lang[11]="Employee";
$lang[12]="Number";
$lang[13]="Average daily stored items per month";
$lang[14]="Month";
$lang[15]="Unable to connect: ";
$lang[16]="Can not select the database";
$lang[17]="Checklist";
$lang[18]="Statistics";
$lang[19]="Reports";
$lang[20]="Search";
$lang[21]="Illegal query ";
$lang[22]="Menu";
$lang[23]="Main";
$lang[24]="Today";
$lang[25]="Fault or Attention messages this month";
$lang[26]="Statistics of entries this month";
$lang[27]="Graph for trendanalisis";
$lang[28]="Date/time".
$lang[29]="Message".
$lang[30]="By";
$lang[31]="(This check has been cancelled but still has some old entries)";
$lang[32]="Status";
$lang[33]="Admin";
$lang[34]=array(0=>"Daily",1=>"Weekly",2=>"Monthly");
$lang[35]=array(0=>"ID",1=>"Order",2=>"Menu ID",3=>"Indent Level",4=>"Header",5=>"Period",6=>"Description",7=>"Disabled");
$lang[36]="TAKE NOTE: This change also affects the interpretation of historical data. If you want to disable this item, then do so.";
//$lang[37]=array(0=>"id",1=>"number",2=>"Start time",3=>"End time");
$lang[38]="Reload page";
$lang[39]="New item";
$lang[40]="<- to be entered ->";
$lang[41]="What has happened today";
$lang[42]="Goto today!";
$lang[43]="Items can no longer be added to this day!";

?>
