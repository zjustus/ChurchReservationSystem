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

?>

<h1>Administration Page</h1>
<h2>class testing for now</h2>




<?php require_once $footer; ?>
