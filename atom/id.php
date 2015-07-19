<?php
// POST atom/add?contents=contentsStr

require_once("../common.php");
if($_SERVER["REQUEST_METHOD"] !== "GET"){
	reportError(HTTP_STATUS_NOT_IMPLEMENTED, "You should use POST method to use this API.");
}
$idStr = substr($_SERVER["PATH_INFO"], 1);
$idStr = verifyUUIDString($idStr);
if(!$idStr){
	reportError(HTTP_STATUS_BAD_REQUEST, "Given ElementID is not valid.");
}

$db = connectDB();
$retv = db_getAtomElementByID($db, $idStr);
if($retv[0] != 0){
	reportError(HTTP_STATUS_INTERNAL_SERVER_ERROR, $retv[1]);
}
if($retv[1] == 0){
	reportError(HTTP_STATUS_NOT_FOUND, $retv[1]);
}

http_response_code(HTTP_STATUS_OK);

elementListBegin();
echoAtomElement(UUID_ServerResponse, $retv[1]);
echoAtomElement($idStr, $retv[2]);
elementListEnd();

?>