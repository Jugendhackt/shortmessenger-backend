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
					case "send":
						# Chat ID and Message via Post!
						if(!empty($_POST["chatId"])){
							$chatId = $_POST["chatId"];
							$chat = json_decode(file_get_contents("chats/".$chatId.".json"), true);
							if(strlen($_POST["message"]) < 120){
								$message = [
									"content" => $_POST["message"],
									"time" => time(),
									"sender" => $user["username"]
								];
								# Save message
								$chat["messages"][] = $message;
								file_put_contents("chats/".$chatId.".json", json_encode($chat));
								$output = $message;
							} else {
								$output["errormsg"] = "Chat error.";
							}
						} else {
							$output["errormsg"] = "Message empty or too long.";
						}

						break;
					case 'read':
						$chats = $user["chats"];
						$response = [];
						foreach($chats as $chat){
							$chatfile = json_decode(file_get_contents("chats/".$chat.".json"), true);
							if(!empty($chatfile["messages"])){
								$chatfile["last"] = end($chatfile["messages"])["time"];
							} else {
								$chatfile["last"] = 0;
							}
							$response[] = $chatfile;
						}
						$output = $response;
						break;
					case 'diff':
						$chats = $user["chats"];
						if(!empty($_GET["timestamp"])){
							unset($output["error"]);
							$output["result"] = false;
							foreach($chats as $chat){
								$chatfile = json_decode(file_get_contents("chats/".$chat.".json"), true);
								foreach($chatfile["messages"] as $message){
									if($message["time"] > $_GET["timestamp"]){
										$output["result"] = true;
									}
								}
							}
						} else {
							$output["errormsg"] = "No timestamp supplied.";
						}
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
