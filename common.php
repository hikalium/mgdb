<?php
require_once("config.php");
require_once("dbfunc.php");

define("UUID_ServerResponse",			"3b56defa-eddd-4ad3-86d1-6dcbe3d6731c");
define("UUID_ServerTimestamp",			"3f5f1aee-40e9-48f3-b047-b9e51f12015c");

function elementListBegin(){
	echo("[\n");
	$ts = new MGDBAtom(microtime(), UUID_ServerTimestamp);
	$ts->echoElement();
}

function elementListEnd(){
	echo("]");
}

class MGDBAtom
{
	public $eid;
	public $contents;
	public $createDate;
	public $lastModifiedDate;
	//
	function __construct($contents, $eid, $cDate = false, $mDate = false){
		$this->eid = $eid;
		$this->contents = $contents;
		$this->createDate = $cDate ? $cDate : date(DATE_ATOM);
		$this->lastModifiedDate = $mDate ? $mDate : date(DATE_ATOM);
	}
	public function echoElement() {
        echo('["' . $this->eid . '", "' . rawurlencode($this->contents) . '"],' . "\n");
    }
}

class MGDBRelation
{
	public $eid;
	public $relid;
	public $e0id;
	public $e1id;
	public $createDate;
	public $lastModifiedDate;
	//
	function __construct($e0id, $relid, $e1id, $eid, $cDate = false, $mDate = false){
		$this->eid = $eid;
		$this->relid = $relid;
		$this->e0id = $e0id;
		$this->e1id = $e1id;
		$this->createDate = $cDate ? $cDate : date(DATE_ATOM);
		$this->lastModifiedDate = $mDate ? $mDate : date(DATE_ATOM);
	}
	public function echoElement() {
        echo('["' . $this->eid . '", "' . $this->relid . '", "' . $this->e0id . '", "' . $this->e1id . '"],' . "\n");
    }
}

//
// UUID
//
function getFormedUUIDString($str){
	$str = strtolower($str);
	return (
	substr($str, 0, 8) . "-" . 
	substr($str, 8, 4) . "-" . 
	substr($str, 12, 4) . "-" . 
	substr($str, 16, 4) . "-" . 
	substr($str, 20, 12)
	);
}

function uuidv4(){
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

function verifyUUIDString($idStr){
	// retv: valid UUID string
	$idStr = strtolower($idStr);
	$idStr = trim($idStr);
	$regex = "/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89AB][0-9a-f]{3}-[0-9a-f]{12}$/i";
	if(preg_match($regex, $idStr) !== 1){
		return false;
	}
	return $idStr;
}

//
// Error reporting
//

define("HTTP_STATUS_OK",					"200");
define("HTTP_STATUS_CREATED",				"201");
define("HTTP_STATUS_BAD_REQUEST",			"400");
define("HTTP_STATUS_NOT_FOUND",				"404");
define("HTTP_STATUS_METHOD_NOT_ARROWED",	"405");
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