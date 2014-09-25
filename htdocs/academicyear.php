<?php

if(!(function_exists("sparql_get")))
{
	include_once("~/tools/PHP-SPARQL-Lib/sparqllib.php");
}

function getAcademicYearStart($timestamp)
{
	$ds = gmdate("Y-m-d", $timestamp) . "T00:00:00Z";
	$query = "SELECT ?from WHERE {
                    ?session <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://id.southampton.ac.uk/ns/AcademicSession> .
                    ?session <http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime> ?from .
                    FILTER ( ?from <= '" . $ds . "'^^<http://www.w3.org/2001/XMLSchema#dateTime> ).
                } ORDER BY DESC(?from) LIMIT 1";
	$result = sparql_get("http://sparql.data.southampton.ac.uk/", $query);

	if(!isset($result))
	{
		return(-1);
	}
	$dt = -1;
	foreach($result as $row)
	{
		$dt = strtotime($row['from']);
		$dt = strtotime(date("Y-m-d", $dt) . " 00:00:00"); // Sorts out the local time/DST issue
	}
	return($dt);
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
