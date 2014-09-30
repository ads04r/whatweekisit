What Week Is It? - Installation Instructions
============================================


Pre-requisites
--------------

* A Linux or Windows machine
* Apache 2
* PHP 5, as both command line and as an Apache module
* git

* An open web port (usually 80)
* Access to cron (normally root)


Installation
------------

1. Create a directory in an area to which a non-root user has access.

2. cd into the directory

3. Clone the code from github
   git clone https://github.com/ads04r/whatweekisit.git .

4. Update the required submodules
   git submodule update --init

5. Now set up Apache so that the document root is the ./htdocs subdirectory
   of wherever we installed What Week Is It.

6. Try and access the host via a web browser. You should get a screen
   explaining how to download the open data. If not, check the Apache config.


Configuration
-------------

1. cd into the directory where you installed What Week Is It.

2. Download the open data from data.soton
   ./bin/download-data

3. Try and access the host via a web browser. It should now display the
   academic current week number.

4. Open your cron file in a text editor. On most systems you can do this by
   crontab -e

5. Add a line to the file that will run the download-data script daily.
   Example:
   0       2       *       *       *        /var/www/bin/download-data

6. Save the file and exit (:wq in vi).

