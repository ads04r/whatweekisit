<?php

$lib_dir = dirname(dirname(__FILE__)) . "/lib";
$var_dir = dirname(dirname(__FILE__)) . "/var";
$etc_dir = dirname(dirname(__FILE__)) . "/etc";

$template_file = "../etc/wwii.html";
$intro_file = "../etc/intro.html";
$about_file = "../etc/data.html";

$f3_base = $lib_dir . "/fatfree/lib/base.php";

include_once(dirname(__FILE__) . "/core.php");
