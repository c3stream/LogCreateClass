<?php

require_once("libs/LogClass.php");
mb_internal_encoding("UTF-8");
$Log = null;
try{
    $Log = new \Service\LogClass(dirname(__FILE__),"logTest");
} catch (Exception $e){
    echo $e->getMessage();
    exit;
}

$Log->OutPutLogString("Test");