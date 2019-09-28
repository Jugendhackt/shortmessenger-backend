<?php

error_reporting(E_ALL);

header("Content-Type: application/json");

define("INVALID_CREDENTIALS_MSG", "Invalid credentials");

$output = [];
$output["error"] = true;

# Check whether useraccount exists and is valid
if(!empty($_GET["username"]) && !empty($_GET["password"])){
	$name = basename($_GET["username"]);
	# Does the user even exist?
	if($file = file_get_contents("users/".$name.".json")){
		$user = json_decode($file, true);
		# Verify whether password is correct
		if(password_verify($_GET["password"], $user["password"])){
			# Yes! We know it's correct!!!!! nice ;)
			# Proceed with the action
			if(!empty($_GET["action"])){
				switch($_GET["action"]){
					case "info":
						$output = $user;
						unset($output["password"]);
					break;
					default:
						$output["errormsg"] = "Unknown action";
				}
			} else {
				$output["errormsg"] = "No action specified.";
			}       
		} else {
			$output["errormsg"] = INVALID_CREDENTIALS_MSG;
		}
	} else {
		$output["errormsg"] = INVALID_CREDENTIALS_MSG;
	}
} else {
	$output["errormsg"] = INVALID_CREDENTIALS_MSG;
}

echo json_encode($output);
