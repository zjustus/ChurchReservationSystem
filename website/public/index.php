<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$usersClass = $path."/../private/dataLayer/users.php";
$reservaionClass = $path."/../private/dataLayer/reservation.php";
$headder = $path."/../private/resources/headder.php";
$footer = $path."/../private/resources/footer.php";
$twilioPath = $path."/../private/resources/twilio-php-master/src/Twilio/autoload.php";
$configFile = $path.'/../private/config.ini';
$overlayPath = $path."/../private/resources/overlay.php";

require_once $headder;
require_once $usersClass;
require_once $reservaionClass;
require_once $twilioPath;
require_once $overlayPath;

$config = parse_ini_file($configFile, true);

/* TODO
DONE: make webform
DONE: generate QR code?
DONE: save QR to database
Removed: connect to email?
DONE: connect to Twilio!
DONE: make service day and times dynamic
add feedback overlays, error or otherwise

*/
$reservationExists = false;
$allGoodInfo = true;
$partySizeTooLarge=false;
$reservationCreated = false;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    //if(!empty($_POST['phone']) && !empty($_POST['party']) && !empty($_POST['date']) && !empty($_POST['partyName'])){
	if(!empty($_POST['phone']) && !empty($_POST['adultCount']) && !empty($_POST['date']) && !empty($_POST['partyName'])){
		if(validatePhone($_POST['phone']) && $sunday = validateReservationDate($_POST['date'])){
			if(validatePartySize($_POST['adultCount'] + $_POST['kidCount']) && validateAdultCount($_POST['adultCount']) && validateKidCount($_POST['kidCount'])){
				$phoneNumber = validatePhone($_POST['phone']);
				
				if(empty($_POST['kidCount'])) $kidCount = 0;
				else $kidCount = $_POST['kidCount'];

				$takenSeats = $config['venue']['maxCapasity'] - Reservation::takenSeats($sunday);

				if($takenSeats >= $_POST['party']){
					//check if person exists, if not make him
					$person = new User();
					if(empty($person->getPersonByPhone($phoneNumber))){ //if phone dosent exist
						$person->setPhoneNumber($phoneNumber);
						$person->createPerson();
					}
					//chekc if an active regestration exists
					$reservations = $person->getReservations();
					if(empty($reservations)){
						$reservation = new Reservation();
						$reservation->setUserID($person->getUserID());
						$reservation->setPartyName($_POST['partyName']);
						$reservation->setReservationDate($sunday);
						//$reservation->setPartySize($_POST['party']);
						$reservation->setAdultCount($_POST['adultCount']);
						$reservation->setKidCount($kidCount);

						$reservation->createReservation();
						$twilio = new \Twilio\Rest\Client($config['twilio']['ssid'], $config['twilio']['authToken']);
						$reservationCreated = true;
						$messageBody = $config['twilio']['reservationMessage'].' '.'https://reserve.luminate.church/reservation.php?reservation='.$reservation->getReservationToken();
						try {
							$twilio->messages->create(
								'+1'.$person->getPhoneNumber(),
								[
									'from' => $config['twilio']['phoneNumber'],
									'body' => $config['twilio']['reservationMessage'].' '.'https://reserve.luminate.church/reservation.php?reservation='.$reservation->getReservationToken(),
								]
							);
						} catch (Exception $e) {
							//echo 'Caught exception: ',  $e->getMessage(), "\n";
							echo "an error occured";
						}
					}
					//TODO: if the reservation already exists
					else {
						$reservationExists = true;
						//echo 'Error, you already have an active reservation going<br>';
						//echo '<a href="https://reserve.luminate.church/reservation.php?reservation='.$reservations[0]['reservation_token'].'">Click here to view reservation</a><br>';
					}

				}
			} else $partySizeTooLarge = true;
        }
    }
	else{
		$allGoodInfo = false;
	}
}


function validatePhone($phoneNumber){
    if(is_string($phoneNumber)){
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber); //cut out the fat
		//echo $phoneNumber;
		if(strlen($phoneNumber) == 10) return $phoneNumber;
		else return false;
    } else return false;
}
function validatePartySize($partySize){
    global $config;
    if(is_numeric($partySize)){
        if($partySize >= 1 && $partySize <= $config['venue']['maxPartySize']){
            return true;
        } else return false;
    } else return false;
}
function validateAdultCount($partySize){
    global $config;
    if(is_numeric($partySize)){
        if($partySize >= 1 && $partySize <= $config['venue']['maxAdults']){
            return true;
        } else return false;
    } else return false;
}
function validateKidCount($partySize){
    global $config;
    if(is_numeric($partySize)){
        if($partySize >= 0 && $partySize <= $config['venue']['maxKids']){
            return true;
        } else return false;
    } else return false;
}
function validateReservationDate($reservationDate){ //validates the service is good
    global $config;
    if(is_string($reservationDate)){
        $sunday = DateTime::createFromFormat('Y-m-d H:i:s', $reservationDate);
        if($sunday && DateTime::getLastErrors()['warning_count'] == 0 && DateTime::getLastErrors()['error_count'] == 0){
            if($sunday->format('w') == $config['service']['dayOfWeek']){
                $today = new DateTime();
                $today->sub(new DateInterval('PT2H')); //time zone correction (-2H)
                $dayDifference = $today->diff($sunday);
                if($dayDifference->d <= ($config['service']['reserveWeeksBefore']*7) && $dayDifference->d >= $config['service']['reserveDaysAfter']){
                    $valid = false;
                    foreach($config['service']['serviceTime'] as $serviceTime){
                        if($sunday->format('H:i') == $serviceTime){
                            $valid = true;
                            break;
                        }
                    }
                    if($valid) return $sunday;
                    else return false;

                    //old code, new code needs testing
                    // if($sunday->format('H:i') == '09:30') return $sunday;
                    // else if($sunday->format('H:i') == '11:00') return $sunday;
                    // else if($sunday->format('H:i') == '13:30') return $sunday;
                    // else return false;
                } else return false;
            } else return false;
        } else return false;
    } else return false;
}

 ?>

 <style media="screen">
 	body{
 		background-color: black;
 		margin: auto;
 	}
 	#reservationForm{
 		background-color: white;
 		border-radius: 10pt;
		padding-bottom: 10pt;
		padding-top: 10pt;
 	}
 	#headding{
 		color: white;
 	}
 </style>

<?php if($reservationExists) Overlay::message('exists-overlay', '<h2>Reservation already exists</h2><br><a href="https://reserve.luminate.church/reservation.php?reservation='.$reservations[0]['reservation_token'].'"><button type="button" class="btn btn-info btn-block" name="button">View reservation</button></a><br>'); ?>
<?php if(!$allGoodInfo) Overlay::message('bad-input-overlay', '<h2>Invalid Input</h2><br>');?>
<?php if($partySizeTooLarge) Overlay::message('bad-input-overlay', '<h2>Party is too large</h2><br><p>You cannot have more than '.$config['venue']['maxPartySize'].' members in your party<br>You cannot have more than '.$config['venue']['maxAdults'].' Adults<br>You cannot have more than '.$config['venue']['maxKids'].' Children<br></p>');?>
<?php if($reservationCreated) Overlay::message('valid-input-overlay', '<h2>Thank you!</h2><br><p>We sent a text to your phone with a link to the reservation</p><br>');?>

<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col text-center" id="headding">
			<img src="/resources/LogoWhite.gif" alt="Luminate Curch" width="200pt">
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="col-lg-5" id="reservationForm">
			<form method="post">
				<div class="form-group">
					<label for="phoneInput">Phone Number</label>
					<input class="form-control" id="phoneInput" type="text" placeholder="(555) 123-4567" name="phone" required onkeydown="javascript:backspacerDOWN(this,event);" onkeyup="javascript:backspacerUP(this,event);">
					<!-- <div class="invalid-feedback">Number is Invalid</div> -->
				</div>
                <div class="form-group">
                    <label for="partyNameInput">Party Name</label>
                    <input class="form-control" id="partyNameInput" type="text" name="partyName" placeholder="Ex: John Doe" required>
					<!-- <div class="invalid-feedback">Party Name is Invalid</div> -->
                </div>
				<div class="form-group">
					<label for="adultCountInput">Adults</label>
					<input class="form-control" id="adultCountInput" type="number" name="adultCount" min="1" max="<?php echo $config['venue']['maxAdults']; ?>" required>
					<!-- <div class="invalid-feedback">Party size must be at least 1 and no larger than <?php echo $config['venue']['maxAdults']; ?></div> -->
				</div>
				<div class="form-group">
					<label for="kidCountInput">Kids</label>
					<input class="form-control" id="kidCountInput" type="number" name="kidCount" min="0" value="0" max="<?php echo $config['venue']['maxKids']; ?>" required>
					<!-- <div class="invalid-feedback">You cant have more than <?php echo $config['venue']['maxKids']; ?> kids in your party</div> -->
				</div>
				<div class="form-group">
					<div id="dateAccordion">
						<?php
						/*This block finds the next service day */
						$sunday = new DateTime();
						$sunday->sub(new DateInterval('PT2H')); //time zone correction (-2H)

                        $daysTillSunday = 7+$config['service']['dayOfWeek']-$sunday->format('w');
                        if($daysTillSunday > 7) $daysTillSunday %= 7;
						$sunday->add(new DateInterval('P'.$daysTillSunday.'D'));

						for ($i=0; $i < $config['service']['reserveWeeksBefore']; $i++) { ?>
						<div class="card">
							<div class="card-header" id="<?php echo 'dateHeading'.$i; ?>">
								<h5 class="mb-0">
									<button type="button" class="btn btn-link" data-toggle="collapse" data-target="<?php echo '#dateCollapse'.$i; ?>" artia-expanded="false" aria-controls="<?php echo 'dateCollapse'.$i; ?>">
										<?php
										echo $sunday->format('l, M d');
										?>
									</button>
								</h5>
							</div>
							<div id="<?php echo 'dateCollapse'.$i; ?>" class="collapse" aria-labellebdy="<?php echo '#dateHeading'.$i; ?>" data-parent="#dateAccordion">
								<div class="card-body">
                                    <?php foreach ($config['service']['serviceTime'] as $j => $time) { ?>
									<div class="form-check">
										<input class="form-check-input" id="<?php echo 'dateRatio'.$i.'-'.$j; ?>" type="radio" name="date" value="<?php
		                                echo $sunday->format('Y-m-d').' '.$time.':00';
										?>" checked="<?php echo (i == 0 && j == 0? 'true' : 'false') ?>">
										<label class="form-check-label" for="<?php echo 'dateRatio'.$i.'-'.$j; ?>">
											<?php
                                            $service = new DateTime($sunday->format('Y-m-d').' '.$time.':00');
                                            echo $service->format('g:i A');
											 ?>
										</label>
									</div>
									<?php } ?>
								</div>
							</div>
						</div>
						<?php
						$sunday->add(new DateInterval('P7D')); //increment to next service day
						} ?>
					</div>
				</div>
				<button type="submit" class="btn btn-primary btn-block">Submit</button>
			</form>
		</div>

	</div>

</div>
<script type="text/javascript" src="resources/phoneFormat.js"></script>
<?php require_once $footer; ?>
