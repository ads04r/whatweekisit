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

	function addWeekEvents($day)
	{
		$dt = $day->academicYearStart();
		$dte = $day->academicYearEnd();
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
	}

	function render($feedid)
	{
		$ret = array();
		$ret[] = "BEGIN:VCALENDAR";
		$ret[] = "VERSION:1.0";
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
