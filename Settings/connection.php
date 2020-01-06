<?php

function getConnection() {
    // for localhost
    
    $dbhost="127.0.0.1";
    $dbuser="root";
    $dbpass="";
    $dbname="slim-hello";

    // for remote connection
    // $dbhost="optimalfire.com";
    // $dbuser="optimalf_touqeer";
    // $dbpass="influx8009";
    // $dbname="optimalf_touqeer";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

function getAuthToken(){
   return "MamaI'mInLove$#@!";
}
