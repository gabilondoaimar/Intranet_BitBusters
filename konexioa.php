<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "proba_erronka1";

    $conn = new mysqli($servername,$username,$password,$dbname);

    if($conn->connect_error){
        die("errorea konexioan: ".$conn->connect_error);
    }
?>