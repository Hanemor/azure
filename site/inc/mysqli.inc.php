<?php

function dbConnect(){   //Returnerer mysqli

    $user = 'root';     
    $password = '';
    $db = 'klubbdb';

    $db = new mysqli('localhost', $user, $password, $db) or die("Tilkobling misslykket");
    mysqli_set_charset($db, 'utf8mb4');
    return $db;
}

?>