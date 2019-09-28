<?php

# URL blocking filter

function urlblock($message){
	$matches = [];
	preg_match('#((https?|ftp)://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)#i', $message, $matches); // thanks internet 
	if(!empty($matches)){
		return true;
	}
	return false;
}
