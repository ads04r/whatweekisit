<?php

include("/var/wwwsites/phplib/academicyear.php");

$start = getAcademicYearStart(time());
$week = getCurrentWeek();

print("It's week " . $week . " of the year that started " . date("jS F Y", $start));
