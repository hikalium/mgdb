<?php
// GET atom

require_once("../common.php");
if($_SERVER["REQUEST_METHOD"] !== "GET"){
	reportError(HTTP_STATUS_METHOD_NOT_ARROWED, "You should use GET method to use this API.");
}

$db = connectDB();
$retv = db_getAllAtomElement($db);
if($retv[0] != 0){
	reportError(HTTP_STATUS_INTERNAL_SERVER_ERROR, $retv[1]);
}

http_response_code(HTTP_STATUS_OK);
elementListBegin();
echoAtomElement(UUID_ServerResponse, (count($retv) - 1) . " elements.");
for($i = 1; $i < count($retv); $i++){
	echoAtomElement($retv[$i][0], $retv[$i][1]);
}
elementListEnd();

?>