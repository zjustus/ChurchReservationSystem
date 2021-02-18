<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$usersClass = $path."/../private/dataLayer/users.php";
$reservaionClass = $path."/../private/dataLayer/reservation.php";
$headder = $path."/../private/resources/headder.php";
$footer = $path."/../private/resources/footer.php";

require_once $usersClass;
require_once $headder;

// this starts the session and check if loged in
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header('location: /login.php');
  exit;
}





$sunday = new DateTime();
$sunday->sub(new DateInterval('PT2H'));

?>

<h1>Administration Page</h1>
<p>This page does 1 thing and I hope it does it well</p>

<form action="/admin/attendance.php" target="_blank" method="GET">
<p>Start Date</p>
<input type="date" name="startDate" id="">
<p>End Date</p>
<input type="date" name="endDate" id="">
<button type="submit">Get Data</button>

</form>




<?php require_once $footer; ?>
