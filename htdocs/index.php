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

function render($f3, $date="")
{
	$day = new Day($f3, $date);
	$f3->set('day', $day);
	$template = new Template();
	return($template->render("../etc/wwii.html"));
}

$f3 = require($lib_dir . "/fatfree/lib/base.php");
$f3->set("TEMP", "/tmp/");
$f3->set('DEBUG', true);

$f3->route("GET /", function($f3) { print(render($f3, "")); } );
$f3->run();
