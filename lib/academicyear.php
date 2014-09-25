<?php

if(!(function_exists("sparql_get")))
{
	include_once("~/tools/PHP-SPARQL-Lib/sparqllib.php");
}

function getAcademicYearStart($timestamp)
{
	global $var_dir;

	$g = new Graphite();
	$g->load($var_dir . "/current.ttl");

	$dt = time();
	foreach($g->allOfType("http://id.southampton.ac.uk/ns/AcademicSession") as $res)
	{

		if(!($res->has("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"))) { continue; }
		$dt_st = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"));
		$dt_ed = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime"));
		if(($dt < $dt_st) | ($dt > $dt_ed)) { continue; }

		return($dt_st);
	}

	return 0;
}

function getWeekNumber($timestamp)
{
	$dt = getAcademicYearStart($timestamp);
	while(gmdate("w", $dt) != 0)
	{
		$dt = $dt - 86400;
	}
	$w = (int) (($timestamp - $dt) / (7 * 86400));

	return($w);
}

function getCurrentWeek()
{
	return(getWeekNumber(time()));
}
