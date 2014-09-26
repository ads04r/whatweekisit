<?php

class Day
{
	private $dt;
	private $lib_dir;
	private $var_dir;
	private $etc_dir;

	private $year_start;

	public function academicYearStart()
	{
		if($this->year_start > 0)
		{
			return($this->year_start);
		}

		$g = new Graphite();
		$g->load($this->var_dir . "/current.ttl");

		$dt = $this->dt;
		foreach($g->allOfType("http://id.southampton.ac.uk/ns/AcademicSession") as $res)
		{

			if(!($res->has("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"))) { continue; }
			$dt_st = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"));
			$dt_ed = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime"));
			if(($dt < $dt_st) | ($dt > $dt_ed)) { continue; }

			$this->year_start = $dt_st;
			return($dt_st);
		}

		return 0;
	}

	public function weekNumber()
	{
		$dt = $this->academicYearStart($this->dt);
		while(gmdate("w", $dt) != 0)
		{
			$dt = $dt - 86400;
		}
		$w = (int) (($this->dt - $dt) / (7 * 86400));

		return($w);
	}

	function __construct($f3, $date)
	{
		$ds = trim(preg_replace("/([^0-9]+)/", "_", $date), "_");
		if((strlen($ds) == 8) & (preg_match("/^([0-9]+)$/", $ds) > 0))
		{
			$ds = substr($ds, 0, 4) . "_" . substr($ds, 4, 2) . "_" . substr($ds, 6, 2);
		}
		if(preg_match("/^[0-9]{4}_[0-9]{2}_[0-9]{2}$/", $ds) == 0)
		{
			$dt = date("Y-m-d");
		}
		$this->dt = strtotime($ds . " 12:00:00 GMT");

		$root_dir = dirname(dirname(dirname(__FILE__)));
		$this->lib_dir = $root_dir . "/lib";
		$this->var_dir = $root_dir . "/var";
		$this->etc_dir = $root_dir . "/etc";
		$this->year_start = 0;
	}
}
