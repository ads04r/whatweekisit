<?php

class Feed {

	private $events;
	public $count;

	function addEvent($title, $start_time, $end_time, $all_day=false, $description="", $location="")
	{
		$events = $this->events;
		$item = array();
		$item['title'] = $title;
		$item['start_time'] = $start_time;
		$item['end_time'] = $end_time;
		$item['all_day'] = $all_day;
		$item['description'] = $description;
		$item['location'] = $location;

		$this->events[] = $item;
		$this->count = count($this->events);
	}

	function addClosureEvents()
	{
		function sort_by_start($a, $b)
		{
			if($a['start'] < $b['start']) { return -1; }
			if($a['start'] > $b['start']) { return 1; }
			return 0;
		}

		$cachefile = dirname(dirname(dirname(__FILE__))) . "/var/all.ttl";
		if(!(file_exists($cachefile)))
		{
			return;
		}

		$g = new Graphite();
		$g->load($cachefile);
		$terms = array();
		foreach($g->allOfType("http://id.southampton.ac.uk/ns/UniversityClosureDay") as $term)
		{
			$item = array();
			$dss = "" . $term->get("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime");
			$dse = "" . $term->get("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime");
			$dts = strtotime($dss);
			$dte = strtotime($dse);
			$dss = date("Y-m-d", ($dts + 20000)) . " 06:00:00";
			$dse = date("Y-m-d", ($dte + 20000)) . " 06:00:00";
			$title = "" . $term->get("http://www.w3.org/2000/01/rdf-schema#label");
			$item['start'] = strtotime($dss);
			$item['end'] = strtotime($dse);
			$item['title'] = $title;
			if(strcmp($item['title'], "[NULL]") == 0) { $item['title'] = ""; }
			if($item['end'] < time()) { continue; }
			$terms[] = $item;
		}
		usort($terms, "sort_by_start");
		$c = count($terms);
		$i = 1;
		for($i = 1; $i < $c; $i++)
		{

			$dts = $terms[$i]['start'];
			$dte = $terms[$i]['end'];

			$title = $terms[$i]['title'];
			if(strlen($title) == 0) { $title = "University Closure Day"; }

			$this->addEvent($title, $dts, $dte, true);
		}
	}

	function addWeekEvents($day)
	{
		$dt = $day->academicYearStart();
		$dte = $day->academicYearEnd();

		if($dt < time()) // If this is the current year, do next year too.
		{
			$nextyear = $day->nextYear();
			$dte = $nextyear->academicYearStart() - 86400;
			$this->addWeekEvents($nextyear);
		}

		$year = date("Y", $dt);
		$academic_year = $year . "/" . ($year + 1);

		$week0 = $dt;
		while(date("N", $dt) != 1)
		{
			$dt = $dt + 86400;
		}
		$week1 = $dt;

		if($week1 != $week0)
		{
			$this->addEvent($academic_year . ", week 0", $week0, $week1 - (86400 * 2), true);
		}

		for($i = 0; $i < 53; $i++)
		{
			$thisweek = $dt + ($i * (86400 * 7));
			$thisweekend = $thisweek + (86400 * 6);
			if($thisweekend > $dte)
			{
				$thisweekend = $dte;
			}
			if($thisweek > $dte)
			{
				continue;
			}
			$this->addEvent($academic_year . ", week " . ($i + 1), $thisweek, $thisweekend, true);
		}
	}

	function addHolidayEvents($day)
	{
		function sort_by_start($a, $b)
		{
			if($a['start'] < $b['start']) { return -1; }
			if($a['start'] > $b['start']) { return 1; }
			return 0;
		}

		$cachefile = dirname(dirname(dirname(__FILE__))) . "/var/all.ttl";
		if(!(file_exists($cachefile)))
		{
			return;
		}

		$g = new Graphite();
		$g->load($cachefile);
		$terms = array();
		foreach($g->allOfType("http://id.southampton.ac.uk/ns/AcademicSessionTerm") as $term)
		{
			$item = array();
			$dss = "" . $term->get("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime");
			$dse = "" . $term->get("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime");
			$item['start'] = strtotime($dss);
			$item['end'] = strtotime($dse);
			if($item['end'] < time()) { continue; }
			$terms[] = $item;
		}
		usort($terms, "sort_by_start");
		$c = count($terms);
		$i = 1;
		for($i = 1; $i < $c; $i++)
		{
			$dts = $terms[$i - 1]['end'] + 86400;
			$dte = $terms[$i]['start'] - 86400;
			$m = (int) date("m", $dts);
			$y = (int) date("Y", $dts);
			$title = "Holiday";
			if($m == 12) { $title = "Christmas Vacation " . $y . "/" . ($y + 1); }
			if(($m == 3) | ($m == 4)) { $title = "Easter Vacation " . $y; }
			if(($m >= 6) & ($m < 9)) { $title = "Summer Vacation " . $y; }
			$this->addEvent($title, $dts, $dte, true);
		}
	}

	function render($feedid)
	{
		$ret = array();
		$ret[] = "BEGIN:VCALENDAR";
		$ret[] = "VERSION:2.0";
		$ret[] = "PRODID:-//whatweekisitsoton//calendar//" . $feedid;
		foreach($this->events as $event)
		{
			$ret[] = "UID:event" . md5(json_encode($event)) . "@whatweekisit.soton.ac.uk";
			$ret[] = "BEGIN:VEVENT";
			if($event['all_day'])
			{
				$ret[] = "DTSTART;VALUE=DATE:" . date("Ymd", $event['start_time']);
				$ret[] = "DTEND;VALUE=DATE:" . date("Ymd", $event['end_time']);
			} else {
				$ret[] = "DTSTART:" . gmdate("Ymd", $event['start_time']) . "T" . gmdate("His", $event['start_time']) . "Z";
				$ret[] = "DTEND:" . gmdate("Ymd", $event['end_time']) . "T" . gmdate("His", $event['end_time']) . "Z";
			}
			$ret[] = "SUMMARY:" . $event['title'];
			if(strlen($event['description']) > 0)
			{
				$ret[] = "DESCRIPTION:" . $event['description'];
			}
			if(strlen($event['location']) > 0)
			{
				$ret[] = "LOCATION:" . $event['location'];
			}
			$ret[] = "END:VEVENT";
		}
		$ret[] = "END:VCALENDAR";
		return(implode("\r\n", $ret));
	}

	function __construct()
	{
		$this->events = array();
		$this->count = 0;
	}

}
