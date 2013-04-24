<?php

include("/var/wwwsites/phplib/academicyear.php");

$start = getAcademicYearStart(time());
$week = getCurrentWeek();

?><html>
<head>
<title>What week is it?</title>
</head>
<body>
<p>It's week <? print($week); ?> of the year that started <? print(date("jS F Y", $start)); ?>.</p>
</body>
</html>
