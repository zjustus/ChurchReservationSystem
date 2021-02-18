<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$sqlPath = $path."/../private/database.php";
require_once $sqlPath;

class User{
	private $userID;
	private $phoneNumber;

	public function getUserID(){
		return $this->userID;
	}
	public function getPhoneNumber(){
		return $this->phoneNumber;
	}
	public function setPhoneNumber($phoneNumber){
		if(is_string($phoneNumber)){
			$this->phoneNumber = $phoneNumber;
			return true;
		}
		else return false;
	}
	public function createPerson(){
		if(empty($this->userID) && !empty($this->phoneNumber)){
			$mysqli = db_connect();
			$sql = "INSERT INTO users(phone_number) VALUES (?)";
			if($stmnt = $mysqli->prepare($sql)){
				$stmnt->bind_param("s", $this->phoneNumber);
				$stmnt->execute();
				$stmnt->free_result();
				$stmnt->close();

				$sql = 'SELECT LAST_INSERT_ID()';
				if($stmnt = $mysqli->prepare($sql)){
					$stmnt->execute();
					$result = $stmnt->get_result();
					$queryPerson = $result->fetch_assoc();
					$stmnt->free_result();
					$stmnt->close();
					$mysqli->close();
					//sets and returns the ID
					$this->userID = $queryPerson["LAST_INSERT_ID()"];
					return $this->userID;
				} else{$mysqli->close(); return false;}
			} else {$mysqli->close(); return false;}
		} else return false;
	}
	public function getPersonByID($userID){
		if(is_numeric($userID)){
			$mysqli = db_connect();
			$sql = "SELECT user_id, phone_number FROM users where user_id = ?";
			if($stmnt = $mysqli->prepare($sql)){
				$stmnt->bind_param('i', $userID);
				$stmnt->execute();
				$result = $stmnt->get_result();
				$queryPerson = $result->fetch_assoc();
				if(!empty($queryPerson)){
					$this->userID = $userID;
					$this->phoneNumber = $queryPerson['phone_number'];
				}

				$stmnt->free_result();
				$stmnt->close();
	            $mysqli->close();
				return $this->userID;
			}else {$mysqli->close(); return false;}
		} else return false;
	}
	public function getPersonByPhone($phoneNumber){
		if(is_numeric($phoneNumber)){
			$mysqli = db_connect();
			$sql = "SELECT user_id, phone_number FROM users where phone_number = ?";
			if($stmnt = $mysqli->prepare($sql)){
				$stmnt->bind_param('i', $phoneNumber);
				$stmnt->execute();
				$result = $stmnt->get_result();
				$queryPerson = $result->fetch_assoc();
				if(!empty($queryPerson)){
					$this->userID = $queryPerson['user_id'];
					$this->phoneNumber = $phoneNumber;
				}

				$stmnt->free_result();
				$stmnt->close();
	            $mysqli->close();
				return $this->userID;
			} else {$mysqli->close(); return false;}
		} else return false;
	}
	public function updatePerson(){
		if(!empty($this->userID) && !empty($this->phoneNumber)){
			$mysqli = db_connect();
			$sql = 'UPDATE users SET phoneNumber = ? WHERE person_ID = ?';
			if($stmnt = $mysqli->prepare($sql)){
				$stmnt->bind_param("ii", $this->phoneNumber, $this->userID);
				$stmnt->execute();
				$stmnt->free_result();
				$stmnt->close();
				$mysqli->close();
				return true;
			} else{$mysqli->close(); return false;}
		} else return false;
	}

	public function getReservations($active = true){
		if(!empty($this->userID)){
			$today = new DateTime();
			$today->sub(new DateInterval('PT2H')); //time zone correction (-2H)
			$sql = '';
			if($active) $sql = 'SELECT reservation_id, reservation_token, reservation_date, adult_count, kid_count FROM reservations where user_id = ? AND reservation_date >= ? AND status = 1';
			else $sql = 'SELECT reservation_id, reservation_token, reservation_date, adult_count, kid_count FROM reservations where user_id = ?';
			$mysqli = db_connect();
			if($stmnt = $mysqli->prepare($sql)){
				if($active) $stmnt->bind_param('is', $this->userID, $today->format('Y-m-d H:i:s'));
				else $stmnt->bind_param('i', $this->userID);
				$stmnt->execute();
				$result = $stmnt->get_result();
				while($row = $result->fetch_assoc()){
					$output[] = $row;
				}

				$stmnt->free_result();
				$stmnt->close();
				$mysqli->close();
				return $output;
			}else {$mysqli->close(); return false;}
		} else return false;
	}

}

 ?>
