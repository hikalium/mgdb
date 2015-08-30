<?php
// GET atom

require_once("../common.php");
if($_SERVER["REQUEST_METHOD"] !== "GET"){
	reportError(HTTP_STATUS_METHOD_NOT_ARROWED, "You should use GET method to use this API.");
}

$db = connectDB();
$retv = db_getAllRelationElement($db);
if($retv[0] != 0){
	reportError(HTTP_STATUS_INTERNAL_SERVER_ERROR, $retv[1]);
}

http_response_code(HTTP_STATUS_OK);
//
elementListBegin();
$servRes = new MGDBAtom((count($retv) - 1) . " elements.", UUID_ServerResponse);
$servRes->echoElement();
for($i = 1; $i < count($retv); $i++){
	$retv[$i]->echoElement();
}
elementListEnd();

?>