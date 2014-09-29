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
$f3->route("GET /", function($f3) { print(render($f3, "", "html")); } );
$f3->run();
