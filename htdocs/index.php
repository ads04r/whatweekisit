<?php

$lib_dir = dirname(dirname(__FILE__)) . "/lib";
$var_dir = dirname(dirname(__FILE__)) . "/var";
$etc_dir = dirname(dirname(__FILE__)) . "/etc";

include_once($lib_dir . "/arc2/ARC2.php");
include_once($lib_dir . "/Graphite/Graphite.php");
if($fh = opendir($lib_dir . "/wwii"))
{
	while(false !== ($libfile = readdir($fh)))
	{
		if(preg_match("/\\.php$/", $libfile) == 0) { continue; }
		include_once($lib_dir . "/wwii/" . $libfile);
	}
}

function render($f3, $date="", $format="html")
{
	global $var_dir;

	if(!(file_exists($var_dir . "/current.ttl")))
	{
		$template = new Template();
		return($template->render("../etc/intro.html"));
	}

	$day = new Day($f3, $date);
	if($day->academicYearStart() == 0)
	{
		$f3->error(404);
		return("");
	}

	if(strcmp($format, "json") == 0)
	{
		header("Content-type: application/json");
		return($day->toJson());
	}

	if(strcmp($format, "xml") == 0)
	{
		header("Content-type: application/xml");
		return($day->toXml());
	}

	if(strcmp($format, "ttl") == 0)
	{
		header("Content-type: text/plain");
		return($day->toTtl());
	}

	if(strcmp($format, "nt") == 0)
	{
		header("Content-type: text/plain");
		return($day->toNTriples());
	}

	if(strcmp($format, "rdf") == 0)
	{
		header("Content-type: application/rdf+xml");
		return($day->toRdfXml());
	}

	if(strcmp($format, "html") != 0)
	{
		$f3->error(404);
		return("");
	}

	$f3->set('day', $day);
	$template = new Template();
	return($template->render("../etc/wwii.html"));
}

function render_old($f3)
{
	// Here for backward compatibility purposes.

	$day = new Day($f3, "");
	$data = $day->getInfo();
	$ret = array();

	$ret['week'] = $data['week']['week'];
	$ret['start_date'] = array();
	$ret['start_date']['unixtime'] = $day->academicYearStart();
	$ret['start_date']['friendly'] = date("jS F Y", $day->academicYearStart());
	$ret['current_date'] = time();

	header("Content-type: application/json");
	return(json_encode($ret));
}

function render_feed($f3, $feedid)
{
	$cache = dirname(dirname(__FILE__)) . "/var/feeds/" . preg_replace("/[^a-z]/", "", $feedid) . ".ics";
	if(file_exists($cache))
	{
		$dt = time() - 86400;
		$fdt = filemtime($cache);
		if($fdt > $dt)
		{
			header("Content-type: text/calendar");
			$ics = file_get_contents($cache);
			return($ics);
		}
	}

	$feed = new Feed();
	$day = new Day($f3, "");

	if(strcmp($feedid, "weeks") == 0)
	{
		$feed->addWeekEvents($day);
		$feed->setTitle("University Weeks");
	}

	if(strcmp($feedid, "holidays") == 0)
	{
		$feed->addHolidayEvents($day);
		$feed->setTitle("University Vacation Periods");
	}

	if(strcmp($feedid, "closures") == 0)
	{
		$feed->addclosureEvents();
		$feed->setTitle("University Official Closures");
	}

	if($feed->count == 0)
	{
		$f3->error(404);
		return("");
	}

	$ics = $feed->render($feedid);
	$fp = @fopen($cache, "w");
	if($fp)
	{
		fwrite($fp, $ics);
		fclose($fp);
	}

	header("Content-type: text/calendar");
	return($ics);
}

// Set up F3

$f3 = require($lib_dir . "/fatfree/lib/base.php");
$f3->set("TEMP", "/tmp/");
$f3->set('DEBUG', true);

// Redirects

$f3->route("GET /advanced.@format", function($f3) { $f3->reroute('/today.' . $f3->get('PARAMS.format')); } );
$f3->route("GET /?format=json", function($f3) { $f3->reroute('/today_old.json'); } );
$f3->route("GET /?date=@date&format=@format", function($f3) { $f3->reroute('/' . $f3->get('PARAMS.date') . '.' . $f3->get('PARAMS.format')); } );
$f3->route("GET /?format=@format&date=@date", function($f3) { $f3->reroute('/' . $f3->get('PARAMS.date') . '.' . $f3->get('PARAMS.format')); } );
$f3->route("GET /?format=@format", function($f3) { $f3->reroute('/today.' . $f3->get('PARAMS.format')); } );

// Routing for open data formats

$f3->route("GET /today.@format", function($f3) { print(render($f3, "", $f3->get('PARAMS.format'))); } );
$f3->route("GET /@date.@format", function($f3) { print(render($f3, $f3->get('PARAMS.date'), $f3->get('PARAMS.format'))); } );
$f3->route("GET /today_old.json", function($f3) { print(render_old($f3)); } );

// Routing for ICS feeds

$f3->route("GET /feed/@feedname.ics", function($f3) { print(render_feed($f3, $f3->get('PARAMS.feedname'))); } );

// Static pages

$f3->route("GET /data.html", function($f3) { $template = new Template(); print($template->render("../etc/data.html")); } );
$f3->route("GET /", function($f3) { print(render($f3, "", "html")); } );

$f3->run();

