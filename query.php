<?php
require_once("common.php");

elementListBegin();
echoAtomElement(UUID_ServerResponse, json_encode($_REQUEST));
elementListEnd();

?>