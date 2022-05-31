<?php

public function register(){		
	$message = '';
	if(!empty($_POST["register"]) && $_POST["email"] !='') {
		$sqlQuery = "SELECT * FROM ".$this->userTable." 
			WHERE email='".$_POST["email"]."'";
		$result = mysqli_query($this->dbConnect, $sqlQuery);
		$isUserExist = mysqli_num_rows($result);
		if($isUserExist) {
			$message = "User already exist with this email address.";
		} else {			
			$authtoken = $this->getAuthtoken($_POST["email"]);
			$insertQuery = "INSERT INTO ".$this->userTable."(first_name, last_name, email, password, authtoken) 
			VALUES ('".$_POST["firstname"]."', '".$_POST["lastname"]."', '".$_POST["email"]."', '".md5($_POST["passwd"])."', '".$authtoken."')";
			$userSaved = mysqli_query($this->dbConnect, $insertQuery);
			if($userSaved) {				
				$link = "<a href='http://example.com/user-management-system/verify.php?authtoken=".$authtoken."'>Verify Email</a>";			
				$toEmail = $_POST["email"];
				$subject = "Verify email to complete registration";
				$msg = "Hi there, click on this ".$link." to verify email to complete registration.";
				$msg = wordwrap($msg,70);
				$headers = "From: info@webdamn.com";
				if(mail($toEmail, $subject, $msg, $headers)) {
					$message = "Verification email send to your email address. Please check email and verify to complete registration.";
				}
			} else {
				$message = "User register request failed.";
			}
		}
	}
	return $message;
}	

public function verifyRegister(){
	$verifyStatus = 0;
	if(!empty($_GET["authtoken"]) && $_GET["authtoken"] != '') {			
		$sqlQuery = "SELECT * FROM ".$this->userTable." 
			WHERE authtoken='".$_GET["authtoken"]."'";
		$resultSet = mysqli_query($this->dbConnect, $sqlQuery);
		$isValid = mysqli_num_rows($resultSet);	
		if($isValid){
			$userDetails = mysqli_fetch_assoc($resultSet);
			$authtoken = $this->getAuthtoken($userDetails['email']);
			if($authtoken == $_GET["authtoken"]) {					
				$updateQuery = "UPDATE ".$this->userTable." SET status = 'active'
					WHERE id='".$userDetails['id']."'";
				$isUpdated = mysqli_query($this->dbConnect, $updateQuery);					
				if($isUpdated) {
					$verifyStatus = 1;
				}
			}
		}
	}
	return $verifyStatus;
}	


public function login(){		
	$errorMessage = '';
	if(!empty($_POST["login"]) && $_POST["loginId"]!=''&& $_POST["loginPass"]!='') {	
		$loginId = $_POST['loginId'];
		$password = $_POST['loginPass'];
		if(isset($_COOKIE["loginPass"]) && $_COOKIE["loginPass"] == $password) {
			$password = $_COOKIE["loginPass"];
		} else {
			$password = md5($password);
		}	
		$sqlQuery = "SELECT * FROM ".$this->userTable." 
			WHERE email='".$loginId."' AND password='".$password."' AND status = 'active'";
		$resultSet = mysqli_query($this->dbConnect, $sqlQuery);
		$isValidLogin = mysqli_num_rows($resultSet);	
		if($isValidLogin){
			if(!empty($_POST["remember"]) && $_POST["remember"] != '') {
				setcookie ("loginId", $loginId, time()+ (10 * 365 * 24 * 60 * 60));  
				setcookie ("loginPass",	$password,	time()+ (10 * 365 * 24 * 60 * 60));
			} else {
				$_COOKIE['loginId' ]='';
				$_COOKIE['loginPass'] = '';
			}
			$userDetails = mysqli_fetch_assoc($resultSet);
			$_SESSION["userid"] = $userDetails['id'];
			$_SESSION["name"] = $userDetails['first_name']." ".$userDetails['last_name'];
			header("location: index.php"); 		
		} else {		
			$errorMessage = "Invalid login!";		 
		}
	} else if(!empty($_POST["loginId"])){
		$errorMessage = "Enter Both user and password!";	
	}
	return $errorMessage; 		
}

include('class/User.php');
$user = new User();
$message =  $user->login();


?>