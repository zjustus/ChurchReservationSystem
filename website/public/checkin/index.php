<?php
/* TODO
check if the time is near service start
*/

$path = $_SERVER['DOCUMENT_ROOT'];
$usersClass = $path."/../private/dataLayer/users.php";
$reservaionClass = $path."/../private/dataLayer/reservation.php";
$headder = $path."/../private/resources/headder.php";
$footer = $path."/../private/resources/footer.php";
$overlayPath = $path."/../private/resources/overlay.php";

$checkinPage = $path."/../private/checkin/main.php";

require_once $usersClass;
require_once $reservaionClass;
require_once $overlayPath;
require_once $headder;


// this starts the session and check if loged in
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header('location: /login.php');
  exit;
}




?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
	<meta charset="utf-8">
	<title>Luminate Church</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
	<script src="https://rawgit.com/sitepoint-editors/jsqrcode/master/src/qr_packed.js"></script>

	<script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>

	<style media="screen">
        body{
            background-color: black;
        }
		#qr-canvas {
			margin: auto;
			width: calc(100% - 20px);
			max-width: 400px;
		}

		#btn-scan-qr {
			cursor: pointer;
		}
        .card{
            background-color: white;
            border-radius: 10pt;
        }
	</style>
	</head>
	<body>
        <?php Overlay::confirmation('CancelConfirm', 'This action cannot be undone, are you sure?', 'checkinAction', '0'); ?>
		<div class="container-fluid">
		<?php if(empty($_GET['reservationToken']) && empty($_GET['phone'])){ ?>
			<div class="row" id="scan-container">
				<div class="col-lg-5">
					<div class="card">
						<h1 style="text-align: center;">Check-In</h1>
						<div class="card-body">
							<button class="btn btn-primary btn-lg btn-block" id="btn-scan-qr">Scan QR</button>
							<canvas hidden="" id="qr-canvas"></canvas>
							<button type="button" class="btn btn-danger btn-lg btn-block fixed-bottom" style="display:none" id="qr-cancel">Cancel</button>
							<div id="inputForm-container">
								<div class="pt-3">
									<p style="text-align: center;">- OR -</p>
								</div>
								<form id="qrInputForm"><input type="text" name="reservationToken" id="tokenInput" hidden="true"></form>
								<form method="get" class="form-inline">
									<div class="input-group">
										<input id="phoneInput" type="number" class="form-control" placeholder="Phone Number" name="phone">
										<div class="input-group-append">
											<button class="btn btn-primary" type="submit">Submit</button>
										</div>
									</div>
								</form>
							</div>

						</div>
					</div>
				</div>
			</div>
		<?php
        } else { //if a reservation ID or phone was given ?>
			<div class="row justify-content-center" id="results">
				<div class="col-lg-5 ">
                    <div class="card">
                        <div class="card-body">
                        <?php
                        $reservation = new Reservation;

						//this grabs the reservation provided a phone number or reservation token
						$reservationToken = NULL;
                        if(empty($_GET['reservationToken']) && !empty($_GET['phone'])){
							$user = new User;
							if(!empty($user->getPersonByPhone($_GET['phone']))){
								$reservationToken = $user->getReservations();
								$reservationToken = $reservationToken[0]["reservation_token"];							
							}
						} else {
							$reservationToken = $_GET['reservationToken'];
						}


						if($reservation->getReservationByToken($reservationToken)){ //if the reservation exists

							if($_SERVER['REQUEST_METHOD'] === 'POST' && $reservation->getStatus() == 1){
								if($_POST['checkinAction'] == 1) $reservation->setStatus(2); //2 is checked in
								else if($_POST['checkinAction'] == 0) $reservation->setStatus(0); //0 -- cancled
								$reservation->updateReservation();
							}


						?>
                            <h1 class="card-title text-center"><?php echo $reservation->getPartyName(); ?></h1>
                            <h6 class="card-subtitle text-muted text-center">- <?php echo $reservation->getReservationDate()->format('m/d/Y h:i A'); ?> -</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <span class="text-muted">Status: </span>
									<?php
									if($reservation->isActive()){ ?><span class="text-success">Active</span><?php }
									else if($reservation->getStatus() == 2){ ?><span class="text-warning">Checked-In</span><?php }
									else if($reservation->getStatus() == 0){ ?><span class="text-danger">Canceled</span><?php }
									else{ ?><span class="text-danger">Expired</span><?php }
									?>
								</li>
                                <li class="list-group-item">
                                    <span class="text-muted">Adults: </span>
                                    <span><?php echo $reservation->getAdultCount(); ?></span>
                                </li>
								<li class="list-group-item">
                                    <span class="text-muted">Kids: </span>
                                    <span><?php echo $reservation->getKidCount(); ?></span>
                                </li>
                            </ul>
                            <div class="card-body">
							<?php if($reservation->checkinAvailable()){ ?>
                                <form method="post">
                                    <div class="row no-gutters">
                                        <div class="col">
                                            <button class="btn btn-primary btn-block btn-lg" type="submit" name="checkinAction" value="1">Check-In</button>
                                        </div>
                                        <div class="col">
                                            <?php if($reservation->isActive()) Overlay::showOverlay('CancelConfirm', 'Deny', 'danger btn-lg'); ?>
                                        </div>
                                    </div>
                                </form>
							<?php } else { ?>
								<h2 class="text-center">Check-In Unavailable</h2>
							<?php } ?>
                                <br>
                                <a href="https://reserve.luminate.church/checkin/"><button class="btn btn-block btn-secondary btn-lg">Scan Again</button></a>
                            </div>
                        <?php
                        } else { //if the reservation doesent exist
                        ?>
                            <h1 class="card-title">No Reservation Exists</h1>
                            <h6 class="card-subtitle text-muted">Please scann again</h6>
                            <div class="card-body">
                                <a href="https://reserve.luminate.church/checkin/"><button class="btn btn-block btn-primary btn-lg">Scan Again</button></a>
                            </div>
                        <?php
                        } ?>
                        </div>
                    </div>


				</div>
			</div>
		<?php } ?>
		</div>
		<script type="text/javascript" src="/../resources/qrCodeScanner.js">

		</script>
	</body>
</html>
