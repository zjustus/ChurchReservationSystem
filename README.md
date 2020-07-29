# ChurchReservationSystem
A simple reservation system to help churches during Covid-19

<h2>About</h2>
Allows people to reserve there seats for services.
Any person with an active reservation cannot reserve for another service until the active reservation is fulfilled, expired, or cancelled.
upon reserving, user will receive a text message containing a welcome and a link to view there reservation.
Greeters on a Sunday will use there phone's camera to scan reservation barcodes and grant or deny entry

<h2>Requirements</h2>
<ul>
<li>Web server with PHP</li>
<li>MySQL database</li>
<li>Twilio Texting (it's cheap!)</li>
</ul>

<h2>Install</h2>
run databaseSetup.sql on a database<br>
the web folder contains all files needed for the website<br>
edit website/private/congig.ini, fill in the blanks and make it fit your church<br>

<h2>Key pages</h2>
https://yourURL.com - make a reservation<br>
https://yourURL.com/checkin - the ushers checkin page (login required)<br>
