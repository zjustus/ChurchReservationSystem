# ChurchReservationSystem
A simple reservation system to help churches during Covid-19

## About
Allows people to reserve there seats and there kids for service
Any person with an active reservation cannot reserve for another service until the active reservation is fulfilled, expired, or cancelled.
upon reserving, user will receive a text message containing a welcome and a link to view there reservation.
Greeters on a Sunday will use there phone's camera to scan reservation barcodes and grant or deny entry

# Requirements

- Web server with PHP
- MySQL database
- Twilio Texting (it's cheap!)


## Install
run databaseSetup.sql on a database

the web folder contains all files needed for the website

edit website/private/congig.ini, fill in the blanks and make it fit your church

#Key pages
https://yourURL.com - make a reservation

https://yourURL.com/checkin - the ushers checkin page (login required)

https://yourURL.com/admin - a basic page that exports the data to a spreadsheet
