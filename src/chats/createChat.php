<?php

# Input chat data
$chat["users"] = explode(" ", getUserInput("Users"));
$chat["img"] = "https://privatsphaere-leipzig.org/wp-content/uploads/2016/05/jugend_hackt-300x300.png";
$chat["name"] = getUserInput("Chat name");
$chat["id"] = generateRandomIdentifier();
$chats["filters"] = [
	"allowUrls" => true,
	"allowEmoji" => false,
	"disallowMonologue" => false,
	"timeRules" = []
];
$chat["messages"] = [];

echo "Creating chat ".$chat["id"]."...\n";

# Add to useraccounts
foreach($chat["users"] as $user){
	echo "Adding user ".$user."\n";
	$userfile = json_decode(file_get_contents("../users/".$user.".json"), true);
	$userfile["chats"][] = $chat["id"];
	file_put_contents("../users/".$userfile["username"].".json", json_encode($userfile));
}

file_put_contents($chat["id"].".json", json_encode($chat));

function generateRandomIdentifier(){
	$chars = str_split("abcdefghijklmnopqrstuvwxyz0123456789");
	$string = "";
	for($i = 0; $i < 8; $i++){
		$string .= $chars[array_rand($chars)];
	}
	return $string;
}

function getUserInput($prompt){
	echo $prompt." > ";
	return trim(fgets(STDIN));
}
