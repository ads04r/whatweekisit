<?php

include_once("./academicyear.php");

$date = trim($_GET['date']);
$format = strtolower($_GET['format']);

$current_date = time();

if((strlen($date) == 8) & (strlen(preg_replace("/[^0-9]/", "", $date)) == 8))
{
	$year = (int) substr($date, 0, 4);
	$month = (int) substr($date, 4, 2);
	$day = (int) substr($date, 6, 2);
	if(checkdate($month, $day, $year))
	{
		$current_date = strtotime($year . "-" . $month . "-" . $day . " 12:00:00");
	}
	else
	{
		$current_date = 0;
		$date = "";
	}
}
else
{
	$date = "";
}


$start = getAcademicYearStart($current_date);
$week = getWeekNumber($current_date);

if(($week < 0) | ($week > 52) | ($current_date == 0))
{
	header("HTTP/1.0 404 Not Found");
	print("<h1>404 Not Found</h1>");
	print("<p>You input an invalid date, or a date that's not in our data... sorry!</p>");
	exit();
}

if(strcmp($format, "xml") == 0)
{
	// Render XML
	header("Content-type: application/xml");
	print("<?xml version=\"1.0\"?>\n");
	print("<week>");
	print("<number><![CDATA[" . $week . "]]></number>");
	print("<start_date unixtime=\"" . $start . "\"><![CDATA[" . date("jS F Y", $start) . "]]></start_date>");
	print("<current_date unixtime=\"" . $current_date . "\" />");
	print("</week>");
	exit();
}

if(strcmp($format, "json") == 0)
{
	// Render JSON
	header("Content-type: application/json");
	$json = array();
	$start_date = array();
	$json['week'] = $week;
	$start_date['unixtime'] = $start;
	$start_date['friendly'] = date("jS F Y", $start);
	$json['start_date'] = $start_date;
	$json['current_date'] = $current_date;
	print(json_encode($json));
	exit();
}

if(strlen($format) > 0)
{
	header("Location: ./");
	exit();
}


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>What week is it?</title>
		<meta http-equiv="Content-Language" content="English" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="./style.css" />
	</head>
	<body>
		<div id="calendarbox">
			<h2>WEEK</h2>
			<h1><? print($week); ?></h1>
			<p>of the year that started <? print(date("jS F Y", $start)); ?>.</p>
		</div>
		<div id="formatbox">Other formats | <a href="./?<? if(strlen($date) > 0) { print("date=" . $date . "&"); } ?>format=xml">XML</a> | <a href="./?<? if(strlen($date) > 0) { print("date=" . $date . "&"); } ?>format=json">JSON</a></div>
	</body>
</html>
