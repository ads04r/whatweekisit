<?php

class Day
{
	private $dt;
	private $lib_dir;
	private $var_dir;
	private $etc_dir;
	private $g;
	private $f3;

	private $info;
	private $year_start;
	private $year_end;

	public function nextYear()
	{
		$ds = date("Y-m-d", $this->dt + (86400 * 365));
		$day = new Day($this->f3, $ds);
		return($day);
	}

	public function toJson()
	{
		$data = $this->getInfo();
		return(json_encode($data));
	}

	public function toXml()
	{
		$st_dt = $this->academicYearStart();
		$xml = "<?xml version=\"1.0\"?><week>";
		$xml .= "<number>" . $this->weekNumber() . "</number>";
		$xml .= "<start_date unixtime=\"" . $st_dt . "\">" . date("jS F Y", $st_dt) . "</start_date>";
		$xml .= "<current_date unixtime=\"" . time() . "\"/>";
		$xml .= "</week>";
		return($xml);
	}

	public function toTtl()
	{
		return($this->g->serialize("Turtle"));
	}

	public function toRdfXml()
	{
		return($this->g->serialize("RDFXML"));
	}

	public function toNTriples()
	{
		return($this->g->serialize("NTriples"));
	}

	public function fileName()
	{
		$fn = preg_replace("|^(.+)/([^/]*)$|", "$2", $_SERVER['REQUEST_URI']);
		$fn = preg_replace("|^(.+)\\.([^\\.]*)$|", "$1", $fn);
		$fn = trim($fn, "/");
		if(strlen($fn) == 0)
		{
			return("today");
		}
		return(urlencode($fn));
	}

	public function getInfo()
	{
		if(count($this->info) > 0)
		{
			return($this->info);
		}
		$data = array();

		$week = array("week"=>$this->weekNumber());
		$semester = array();
		$term = array();

		$dt = $this->dt;

		foreach($this->g->allSubjects() as $res)
		{
			if(!($res->has("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"))) { continue; }
			$dt_st = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"));
			$dt_ed = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime"));
			if(($dt < $dt_st) | ($dt > $dt_ed)) { continue; }

			if($res->isType("http://id.southampton.ac.uk/ns/AcademicSessionTerm"))
			{
				$id = preg_replace("|^(.+)#([a-z]+)term$|", "$2", "" . $res);
				if(strcmp($id, "" . $res) == 0) { continue; }
				if($res->has("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime")) {
					$term['start'] = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"));
				}
				if($res->has("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime")) {
					$term['end'] = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime"));
				}
				$term['term'] = $id;
				continue;
			}
			if($res->isType("http://id.southampton.ac.uk/ns/AcademicSessionSemester"))
			{
				$id = preg_replace("|^(.+)#semester([0-9]+)$|", "$2", "" . $res);
				if(strcmp($id, "" . $res) == 0) { continue; }
				if($res->has("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime")) {
					$semester['start'] = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"));
				}
				if($res->has("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime")) {
					$semester['end'] = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime"));
				}
				$semester['semester'] = (int) $id;
				continue;
			}
			if($res->isType("http://id.southampton.ac.uk/ns/ExamPeriod"))
			{
				$data['exams'] = "" . $res->label();
				continue;
			}
		}

		$data['week'] = $week;
		$data['term'] = $term;
		$data['semester'] = $semester;

		$this->info = $data;
		return($data);
	}

	public function academicYearStart()
	{
		if($this->year_start > 0)
		{
			return($this->year_start);
		}

		$dt = $this->dt;
		foreach($this->g->allOfType("http://id.southampton.ac.uk/ns/AcademicSession") as $res)
		{
			if(!($res->has("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"))) { continue; }
			$dt_st = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"));
			$dt_ed = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime"));
			if(($dt < $dt_st) | ($dt > $dt_ed)) { continue; }

			$this->year_start = $dt_st;
			$this->year_end = $dt_ed;
			return($dt_st);
		}

		return 0;
	}

	public function academicYearEnd()
	{
		if($this->year_end > 0)
		{
			return($this->year_end);
		}

		$dt = $this->dt;
		foreach($this->g->allOfType("http://id.southampton.ac.uk/ns/AcademicSession") as $res)
		{
			if(!($res->has("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"))) { continue; }
			$dt_st = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"));
			$dt_ed = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime"));
			if(($dt < $dt_st) | ($dt > $dt_ed)) { continue; }

			$this->year_start = $dt_st;
			$this->year_end = $dt_ed;
			return($dt_ed);
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

	private function fillGraph()
	{
		$this->g->ns("tl", "http://purl.org/NET/c4dm/timeline.owl#");
		$this->g->ns("soton", "http://id.southampton.ac.uk/ns/");

		$dt = $this->dt;
		$uri = "http://id.southampton.ac.uk/academic-day/" . date("Y-m-d", $this->dt);
		$week_uri = "http://id.southampton.ac.uk/academic-week/" . date("Y", $this->dt) . "-" . $this->weekNumber();

		$this->g->addCompressedTriple("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], "foaf:primaryTopic", $uri);
		$this->g->addCompressedTriple($uri, "rdf:type", "http://id.southampton.ac.uk/ns/AcademicDay");
		$this->g->addTriple($uri, "http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime", date("Y-m-d\\T00:00:00P", $dt), "http://www.w3.org/2001/XMLSchema#dateTime");
		$this->g->addTriple($uri, "http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime", date("Y-m-d\\T23:59:59P", $dt), "http://www.w3.org/2001/XMLSchema#dateTime");

		$this->g->addTriple($week_uri, "http://purl.org/NET/c4dm/timeline.owl#contains", $uri);
		$this->g->addCompressedTriple($week_uri, "rdf:type", "http://id.southampton.ac.uk/ns/AcademicWeek");

		foreach($this->g->allSubjects() as $res)
		{
			if(!($res->has("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"))) { continue; }
			$dt_st = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#beginsAtDateTime"));
			$dt_ed = strtotime("" . $res->get("http://purl.org/NET/c4dm/timeline.owl#endsAtDateTime"));
			if(($dt < $dt_st) | ($dt > $dt_ed)) { continue; }
			if(strcmp("" . $res, $uri) == 0) { continue; }

			$this->g->addTriple("" . $res, "http://purl.org/NET/c4dm/timeline.owl#contains", $uri);
			$this->g->addTriple("" . $res, "http://purl.org/NET/c4dm/timeline.owl#contains", $week_uri);
		}
	}

	public function today()
	{
		$ds_day = date("Y-m-d", $this->dt);
		$ds_today = date("Y-m-d");

		return(strcmp($ds_day, $ds_today) == 0);
	}

	function __construct($f3, $date)
	{
		$this->f3 = $f3;

		$ds = trim(preg_replace("/([^0-9]+)/", "_", $date), "_");
		$dt = 0;
		if(strlen($date) == 0)
		{
			$dt = time();
		}
		if((strlen($ds) == 8) & (preg_match("/^([0-9]+)$/", $ds) > 0))
		{
			$ds = substr($ds, 0, 4) . "_" . substr($ds, 4, 2) . "_" . substr($ds, 6, 2);
		}
		if(preg_match("/^[0-9]{4}_[0-9]{2}_[0-9]{2}$/", $ds) > 0)
		{
			$dt = strtotime(str_replace("_", "-", $ds) . " 12:00:00 +0000");
		}
		$this->dt = $dt;

		$root_dir = dirname(dirname(dirname(__FILE__)));
		$this->lib_dir = $root_dir . "/lib";
		$this->var_dir = $root_dir . "/var";
		$this->etc_dir = $root_dir . "/etc";
		$this->year_start = 0;
		$this->info = array();

		$this->g = new Graphite();
		$this->g->load($this->var_dir . "/current.ttl");
		if($dt > 0)
		{
			$this->fillGraph();
		}
	}
}
