<?php
session_start();
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
  header('location: /checkin/index.php');
}

$path = $_SERVER['DOCUMENT_ROOT'];
$configFile = $path.'/../private/config.ini';
$config = parse_ini_file($configFile, true);

$validation_err = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	if(empty(trim($_POST["username"]))) $username_err = 'Username Must not be Blank';
	else $username = trim($_POST["username"]);
	if(empty(trim($_POST["password"]))) $password_err = 'Password Must not be Blank';
	else $password = trim($_POST["password"]);

	//if username and password was properly submitted
	if(!empty($username) && !empty($password)){
		if(strtolower($username) == $config['security']['checkinUsername'] && $password == $config['security']['checkinPassword']){
			$_SESSION["loggedin"] = true;
			header('location: /checkin/index.php');
			exit;
		}
		else if($username == $config['security']['adminUsername'] && $password == $config['security']['adminPassword']){
			$_SESSION["admin"] = true;
			$_SESSION["loggedin"] = true;
			header('location: /admin/index.php');
			exit;
		}
		else $validation_err = 'Username or password is incorrect';
	}

}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<meta charset="utf-8">
	    <meta name="robots" content="noindex">
	    <title>Lumiante Church</title>
	    <meta name="viewport" content="width=device-width">
	    <link href="https://unpkg.com/ionicons@4.5.5/dist/css/ionicons.min.css" rel="stylesheet">
	    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	    <link rel="stylesheet" href="/resources/main.css">

	    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	    <script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
	</head>
	<body>
		<div class="container-fluid">
		  <div class="row justify-content-center" style="margin-top: 5%;">
		    <div class="col-md-8 col-xl-4 align-items-center">
		      <form class="login-form" method="post">
		        <div class="form-group">
		          <h1>Luminate Reservations Login</h1>
		          <span class="help-block"><?php echo $validation_err ?></span>
		        </div>
		        <div class="form-group">
		          <label for="username_input">Username: </label>
		          <input type="text" class="form-control" name="username" placeholder="username">
		          <span class="help-block"><?php echo $username_err; ?></span>
		        </div>
		        <div class="form-group">
		          <label for="password_input">Password: </label>
		          <input type="password" class="form-control" name="password" placeholder="password">
		          <span class="help-block"><?php echo $password_err; ?></span>
		        </div>
		        <button type="submit" class="btn btn-primary btn-block">Submit</button>
		      </form>
		    </div>
		  </div>
		</div>
	</body>
</html>
