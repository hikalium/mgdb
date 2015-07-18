<?php
// POST atom/add?contents=contentsStr

require_once("../common.php");
if($_SERVER["REQUEST_METHOD"] !== "POST"){
	reportError(HTTP_STATUS_NOT_IMPLEMENTED, "You should use POST method to use this API.");
}
if(!isset($_REQUEST["contents"])){
	reportError(HTTP_STATUS_BAD_REQUEST, "Argument 'contents' is not passed.");
}

$db = connectDB();
$retv = db_addAtomElement($db, $_REQUEST["contents"]);
if($retv[0] != 0){
	reportError(HTTP_STATUS_INTERNAL_SERVER_ERROR, $retv[1]);
}

http_response_code(HTTP_STATUS_CREATED);
elementListBegin();
echoAtomElement(UUID_ServerResponse, $retv[1]);
echoAtomElement($retv[1], $_REQUEST["contents"]);
elementListEnd();

?>