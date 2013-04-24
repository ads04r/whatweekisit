whatweekisit
============

Something for Southampton uni that tells you what week it is.

For an actual working example (and possibly this software's only
practical use): http://whatweekisit.ecs.soton.ac.uk/

Pat and I became rather frustrated that the uni's timetabling
system runs on a week system but real life runs on a date system
and there appears to be no quick and easy way to determine which
week of the academic year we're currently in. As I've just
released the open data for the university's academic sessions
until the year 2020, I felt this was an appropriate way of
showing it off.

Installing it is a little hasslesome, you need to have
Chris's PHP-SPARQL-Lib installed, and configure the
include paths in the PHP files appropriately.

To actually use it, go to / on the server. That's it.

If you want XML or JSON, follow the links to /?format=xml
and /?format=json respectively. If you want to know what
week a particular date is in, go to /?date=yyyymmdd

