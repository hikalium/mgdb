<?php
require_once("config.php");
require_once("dbfunc.php");

define("UUID_ServerResponse",			"3b56defa-eddd-4ad3-86d1-6dcbe3d6731c");
define("UUID_ServerTimestamp",			"3f5f1aee-40e9-48f3-b047-b9e51f12015c");

function elementListBegin(){
	echo("[\n");
	echoAtomElement(UUID_ServerTimestamp, microtime());
}

function elementListEnd(){
	echo("]");
}

function echoAtomElement($idStr, $contents)
{
	echo('["' . $idStr . '", "' . rawurlencode($contents) . '"],' . "\n");
}


//
// UUID
//
function getFormedUUIDString($str)
{
	$str = strtolower($str);
	return (
	substr($str, 0, 8) . "-" . 
	substr($str, 8, 4) . "-" . 
	substr($str, 12, 4) . "-" . 
	substr($str, 16, 4) . "-" . 
	substr($str, 20, 12)
	);
}

function uuidv4() {
	// from http://www.php.net/manual/en/function.uniqid.php#94959
	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	// 32 bits for "time_low"
	mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
	
	// 16 bits for "time_mid"
	mt_rand( 0, 0xffff ),
	
	// 16 bits for "time_hi_and_version",
	// four most significant bits holds version number 4
	mt_rand( 0, 0x0fff ) | 0x4000,
	
	// 16 bits, 8 bits for "clk_seq_hi_res",
	// 8 bits for "clk_seq_low",
	// two most significant bits holds zero and one for variant DCE1.1
	mt_rand( 0, 0x3fff ) | 0x8000,
	
	// 48 bits for "node"
	mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	);
}

define("HTTP_STATUS_OK",					"200");
define("HTTP_STATUS_CREATED",				"201");
define("HTTP_STATUS_BAD_REQUEST",			"400");
define("HTTP_STATUS_INTERNAL_SERVER_ERROR",	"500");
define("HTTP_STATUS_NOT_IMPLEMENTED",		"501");
function reportError($ecode, $estr){
	http_response_code($ecode);
	elementListBegin();
	echoAtomElement(UUID_ServerResponse, $estr);
	elementListEnd();
	die();
}
?>