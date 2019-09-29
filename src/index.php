<?php

# Include filters
require_once("filters/urlBlock.php");
require_once("filters/emojiBlock.php");
require_once("filters/timeBlock.php");

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
						if(!empty($_POST["chatId"]) and !empty($_POST["message"])){
							$chatId = $_POST["chatId"];
							$chat = json_decode(file_get_contents("chats/".$chatId.".json"), true);
							if(strlen($_POST["message"]) < 120){
								$message = [
									"content" => $_POST["message"],
									"time" => time(),
									"sender" => $user["username"]
								];
								# Check for filters
								if(!$chat["filters"]["allowURLs"]){
									if(urlBlock($message["content"])){
										$output["errormsg"] = "URLs are not allowed!";
										break;
									}
								} 
								if($chat["filters"]["disallowMonologue"] and $message["sender"] == end($chat["messages"])["sender"]) {
									$output["errormsg"] = "You may not send multiple messages in a row.";
									break;
								} 
								if(!$chat["filters"]["allowEmoji"] and emojiblock($message["content"])){
									$output["errormsg"] = "Emojis aren't allowed in this channel.";
									break;
								}
								if(!empty($chat["filters"]["timeRules"])){
									foreach($chat["filters"]["timeRules"] as $cronRule){
										# See if we're blocked
										if(timeblock($cronRule)){
											$output["errormsg"] = "You may not send a message now.";
											break 2;
										}
									}
								}
								# Save message
								$chat["messages"][] = $message;
								file_put_contents("chats/".$chatId.".json", json_encode($chat));
								$output = $message;
							} else {
								$output["errormsg"] = "Your message is too long.";
							}
						} else {
							$output["errormsg"] = "You cannot send no message..";
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
