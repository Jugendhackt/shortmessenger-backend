<?php

# Simple script to generate user logins for our app
$user["username"] = getUserInput("Username");
$user["password"] = password_hash(getUserInput("Password"), PASSWORD_DEFAULT);
$user["contacts"] = ["service"];
$user["chats"] = [];

# Save the userfile
file_put_contents($user["username"].".json", json_encode($user));

# Get CLI input from user
function getUserInput($prompt){
	echo $prompt." > ";
	return trim(fgets(STDIN));
}
