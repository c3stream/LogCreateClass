<?php

require_once(dirname(dirname(__FILE__))."/LogClass/LogClass.php");
mb_internal_encoding("UTF-8");
$Log = null;
try{
    $Log = new Libs\Service\LogClass(dirname(__FILE__),"logTest");
} catch (Exception $e){
    echo $e->getMessage();
    exit;
}


