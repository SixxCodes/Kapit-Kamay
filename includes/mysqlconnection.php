<?php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "kapit_kamay";
    $connection = new mysqli($host, $user, $pass, $db);
    if($connection->connect_error){
        echo "Failed to connect DB".$connection->connect_error;
    }
?>