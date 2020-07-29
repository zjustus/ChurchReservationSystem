<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$usersClass = $path."/../private/dataLayer/users.php";
$reservaionClass = $path."/../private/dataLayer/reservation.php";
$headder = $path."/../private/resources/headder.php";
$footer = $path."/../private/resources/footer.php";
$overlayPath = $path."/../private/resources/overlay.php";

require_once $usersClass;
require_once $reservaionClass;
require_once $headder;
require_once $overlayPath;

if($_POST['cancelReservation'] == 'true' && !empty($_GET['reservation'])){
    $reservation = new Reservation();
    if($reservation->getReservationByToken($_GET['reservation'])){
        $reservation->setStatus(0);
        $reservation->updateReservation();
    }
}

?>
<style media="screen">
    body{
        background-color: black;
    }
    .main{
        background-color: white;
        border-radius: 10pt;
    }
    #qrCode{
        width: 80%;
        margin-left: auto;
        margin-right: auto;
    }
</style>

<?php Overlay::confirmation('CancelConfirm', 'This action cannot be undone, are you sure?', 'cancelReservation', 'true'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-5">
        <?php
        $reservation = new Reservation();
        if(!empty($_GET['reservation']) && $reservation->getReservationByToken($_GET['reservation'])){
            $person = new User();
            $person->getPersonByID($reservation->getUserID());
        ?>
            <div class="card main">
                <img class="card-img-top text-center" src="https://chart.googleapis.com/chart?cht=qr&chs=500x500&chl=<?php echo urlencode($reservation->getReservationToken()); ?>" id="qrCode" alt="">
                <h1 class="text-center card-title">Luminate Church</h1>
                <h2 class="text-center card-subtitle">- <?php echo $reservation->getReservationDate()->format('m/d/Y h:i A'); ?> -</h2>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Status: <?php if($reservation->isActive()){ ?><span class="text-success">Active</span><?php }else { ?><span class="text-danger">Inactive</span><?php } ?></li>
                    <li class="list-group-item">Party Name: <?php echo $reservation->getPartyName(); ?></li>
                    <li class="list-group-item">Party size: <?php echo $reservation->getPartySize(); ?></li>
                    <li class="list-group-item"><?php echo "Phone: ".$person->getPhoneNumber(); ?></li>
                </ul>
                <div class="card-body">
                <?php if($reservation->isActive()) Overlay::showOverlay('CancelConfirm', 'Cancel Reservation', 'danger'); ?>
                </div>
            </div>

        <?php } else { ?>
            <p class="h1">Error: Reservation dosent Exist</p>
        <?php } ?>

        </div>
    </div>
</div>

<?php require_once $footer; ?>
