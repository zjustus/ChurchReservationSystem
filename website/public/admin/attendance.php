<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$usersClass = $path."/../private/dataLayer/users.php";
$reservaionClass = $path."/../private/dataLayer/reservation.php";
$headder = $path."/../private/resources/headder.php";
$footer = $path."/../private/resources/footer.php";

require_once $usersClass;
require_once $reservaionClass;

if($_SERVER['HTTP_X_FORWARDED_PROTO']!='https') {
$redirect= 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header('Location:'.$redirect);
}

$path = $_SERVER['DOCUMENT_ROOT'];
$configFile = $path.'/../private/config.ini';
$config = parse_ini_file($configFile, true);

// this starts the session and check if loged in
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header('location: /login.php');
  exit;
}

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    if(!empty($_GET['startDate']) && !empty($_GET['endDate'])){

        $startDate = DateTime::createFromFormat('Y-m-d', $_GET['startDate']);
        $endDate = DateTime::createFromFormat('Y-m-d', $_GET['endDate']);
        $attendanceDump = Reservation::reservationDump($startDate, $endDate);
        
        header("Content-Disposition: attachment; filename=\"attendance.xls\"");
        header("Content-Type: application/vnd.ms-excel;");
        header("Pragma: no-cache");
        header("Expires: 0");

        $out = fopen("php://output", 'w');
        
        $xlsHeadders = Array('Reservation ID', 'Reservation Date', 'Status', 'Adults', 'Kids');
        fputcsv($out, $xlsHeadders,"\t");

        foreach ($attendanceDump as $data)
        {
            fputcsv($out, $data,"\t");
        }
        fclose($out);
    }
}
?>
