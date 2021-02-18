<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/../private/database.php";
require_once $path;

class Reservation{
	private $reservationID;
	private $reservationToken;
	private $userID;
	private $reservationDate;
	private $adultCount;
	private $kidCount;
	private $status; //0 - cancled, 1 - open, 2 - checked-in
	private $partyName;

	//STATIC FUNCTIONS
	public static function takenSeats($service){
		if($service instanceof DateTime){
			$mysqli = db_connect();
			$sql = 'SELECT SUM(adult_count + kid_count) as takenSeats from reservations where reservation_date = ? AND status <= 1';
			if($stmnt = $mysqli->prepare($sql)){
				$stmnt->bind_param("s", $service->format('Y-m-d H:i:s'));
				$stmnt->execute();
				$result = $stmnt->get_result();
				$row = $result->fetch_assoc();
				$output = $row['takenSeats'];

				$stmnt->free_result();
				$stmnt->close();
				$mysqli->close();
				if(empty($output)) $output = 0;
				return $output;

			} else{$mysqli->close(); return false;}
		} else return false;
	}

	public function reservationDump($startDate, $endDate){
		if($startDate instanceof DateTime && $endDate instanceof DateTime){
			$mysqli = db_connect();
			$sql = 'SELECT reservation_id, reservation_date, status, adult_count, kid_count FROM reservations WHERE reservation_date >= ? AND reservation_date <= ?';
			if($stmnt = $mysqli->prepare($sql)){
				$stmnt->bind_param("ss", $startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'));
				$stmnt->execute();
				$result = $stmnt->get_result();
				while($row = $result->fetch_assoc()){
					$output[] = $row;
				}

				$stmnt->free_result();
				$stmnt->close();
				$mysqli->close();

				return $output;

			} else{ $mysqli->close(); return false; }
		} else return false;
	}

	//GETS N SETS
	public function getReservationID(){
		return $this->reservationID;
	}
	public function getReservationToken(){
		return $this->reservationToken;
	}
	public function getUserID(){
		return $this->userID;
	}
	public function setUserID($userID){ //check if the userID exists?
		if(is_numeric($userID)) {
			$this->userID = $userID;
			return true;
		} else return false;
	}
	public function getReservationDate(){
		return $this->reservationDate;
	}
	public function setReservationDate($reservationDate){
		if($reservationDate instanceof DateTime) {
			$this->reservationDate = $reservationDate;
			return true;
		} else return false;
	}
	public function getStatus(){ //0 - cancled, 1 - open, 2 - checked-in
		return $this->status;
	}
	public function setStatus($status){
		if($status >= 0 && $status <= 2){
			$this->status = $status;
			return true;
		} else return false;
	}
	public function getPartyName(){
		return $this->partyName;
	}
	public function setPartyName($partyName){
		if(!empty($partyName)){
			$this->partyName = $partyName;
			return true;
		} else return false;
	}
	public function getAdultCount(){
		return $this->adultCount;
	}
	public function setAdultCount($adultCount){
		if(is_numeric($adultCount)) {
			$this->adultCount = $adultCount;
			return true;
		} else return false;
	}
	public function getKidCount(){
		return (empty($this->kidCount) ? 0 : $this->kidCount);
	}
	public function setKidCount($kidCount){
		if(empty($kidCount)){
			$this->kidCount = 0;
			return true;
		}
		else if(is_numeric($kidCount)) {
			$this->kidCount = $kidCount;
			return true;
		} else return false;
	}

	/**
	 * UPDATE: removed partySize
	 * UPDATE: added adultCount & kidCount
	 * UPDATE: getPartySize is now a legacy command that adds adultCount and kidCount together
	 */
	public function getPartySize(){
		return $this->adultCount + $this->kidCount;
	}
	// public function setPartySize($partySize){
	// 	if(is_numeric($partySize)) {
	// 		$this->partySize = $partySize;
	// 		return true;
	// 	} else return false;
	// }

	//CONSTRUCTORS AND UPDATERS
	public function createReservation(){
		if(empty($this->reservationID) && empty($this->reservationToken) && !empty($this->userID) && !empty($this->reservationDate) && !empty($this->adultCount) && !empty($this->partyName) && (!empty($this->kidCount) || $this->kidCount == 0))
		{
			$mysqli = db_connect();
			//create reservation token here!
			$this->reservationToken = bin2hex(random_bytes(16));
			$this->status = 1;
			$sql = 'INSERT INTO reservations(reservation_token, user_id, reservation_date, status, party_name, adult_count, kid_count) VALUES(?, ?, ?, 1, ?, ?, ?)';
			if($stmnt = $mysqli->prepare($sql)){
				$stmnt->bind_param("sissii", $this->reservationToken, $this->userID, $this->reservationDate->format('Y-m-d H:i:s'), $this->partyName, $this->adultCount, $this->kidCount);
				$stmnt->execute();
				$stmnt->free_result();
				$stmnt->close();
				$sql = 'SELECT LAST_INSERT_ID()';
				if($stmnt = $mysqli->prepare($sql)){
					$stmnt->execute();
					$result = $stmnt->get_result();
					$reservationQuery = $result->fetch_assoc();
					$stmnt->free_result();
					$stmnt->close();
					$mysqli->close();
					//sets and returns the ID
					$this->reservationID = $reservationQuery["LAST_INSERT_ID()"];
					return $this->reservationID;
				} else{$mysqli->close(); return false;}
			}else{$mysqli->close(); return false;}
		} else return false;
	}
	public function updateReservation(){
		if(!empty($this->reservationID)){
			$mysqli = db_connect();
			$sql = 'UPDATE reservations set user_id = ?, reservation_date = ?, status = ?, party_name = ?, adult_count = ?, kid_count = ? where reservation_id = ?';
			if($stmnt = $mysqli->prepare($sql)){
				$stmnt->bind_param('isisiii', $this->userID, $this->reservationDate->format("Y-m-d H:i:s"), $this->status, $this->partyName, $this->adultCount, $this->kidCount, $this->reservationID);
				$stmnt->execute();
				$stmnt->close();
				$mysqli->close();
				return true;
			} else{$mysqli->close(); return false;}
		} else return false;
	}

	public function getReservationByID($reservationID){
		$mysqli = db_connect();
		$sql = 'SELECT reservation_token, user_id, reservation_date, status, party_name, adult_count, kid_count FROM reservations where reservation_id = ?';
		if($stmnt = $mysqli->prepare($sql)){
			$stmnt->bind_param('s', $reservationID);
			$stmnt->execute();
			$result = $stmnt->get_result();
			$reservationQuery = $result->fetch_assoc();
			$stmnt->free_result();
			$stmnt->close();
			$mysqli->close();
			if(empty($reservationQuery['reservation_token'])) return false;
			else{
				$this->reservationID = $reservationID;
				$this->reservationToken = $reservationQuery['reservation_token'];
				$this->userID = $reservationQuery['user_id'];
				$this->reservationDate = $reservationQuery['reservation_date'];
				$this->status = $reservationQuery['status'];
				$this->partyName = $reservationQuery['party_name'];
				$this->adultCount = $reservationQuery['adult_count'];
				$this->kidCount = $reservationQuery['kid_count'];
				return $this->reservationID;
			}
		} else{$mysqli->close(); return false;}
	}
	public function getReservationByToken($reservationToken){
		$mysqli = db_connect();
		$sql = 'SELECT reservation_id, user_id, reservation_date, status, party_name, adult_count, kid_count FROM reservations where reservation_token = ?';
		if($stmnt = $mysqli->prepare($sql)){
			$stmnt->bind_param('s', $reservationToken);
			$stmnt->execute();
			$result = $stmnt->get_result();
			$reservationQuery = $result->fetch_assoc();
			$stmnt->free_result();
			$stmnt->close();
			$mysqli->close();
			if(empty($reservationQuery['reservation_id'])) return false;
			else{
				$this->reservationToken = $reservationToken;
				$this->reservationID = $reservationQuery['reservation_id'];
				$this->userID = $reservationQuery['user_id'];
				$this->reservationDate =  DateTime::createFromFormat('Y-m-d H:i:s', $reservationQuery['reservation_date']);
				$this->status = $reservationQuery['status'];
				$this->partyName = $reservationQuery['party_name'];
				$this->adultCount = $reservationQuery['adult_count'];
				$this->kidCount = $reservationQuery['kid_count'];
				return $this->reservationID;
			}
		} else{$mysqli->close(); return false;}
	}

	public function isActive(){
		$today = new DateTime();
		$today->sub(new DateInterval('PT2H')); //time zone correction (-2H)
		if(!empty($this->reservationID) && $this->status == 1 && $today < $this->reservationDate) return true;
		else return false;
	}

	public function checkinAvailable(){
		if($this->isActive()){

			$configFile = $_SERVER['DOCUMENT_ROOT'].'/../private/config.ini';
			$config = parse_ini_file($configFile, true);

			//get the time interval
			$today = new DateTime();
			$today->sub(new DateInterval('PT2H')); //time zone correction (-2H)
			$interval = $today->diff($this->reservationDate);

			//get the total minuts till service
			$minutsLeft = 0;
			$minutsLeft = $minutsLeft + ($interval->format('%r%a') * 720); //days
			$minutsLeft = $minutsLeft + ($interval->format('%r%h') * 60); //hours
			$minutsLeft = $minutsLeft + $interval->format('%r%i'); //minuts

			//check if current time is within check-in interval
			if($minutsLeft <= $config['service']['checkinEarly'] && $minutsLeft >= -$config['service']['checkinLate']) return true;
			else return false;
		} else return false;

	}

}


 ?>
