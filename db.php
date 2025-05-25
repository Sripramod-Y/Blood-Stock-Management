<?php
    /* Replace the username, password and db with your database details */
    $host = "localhost";
    $username="root";
    $password="pramod@2006";
    $db="BLOODSTOCKMANAGEMENT";

    $conn = new mysqli($host, $username, $password, $db);
    
    if($conn->connect_error){
        die("Connection Failed.. : ".$conn->connect_error);
    }

    /* Database schema specified in Blood-Stock.sql file */
    function logActivity($conn, $bloodbankId, $activityText, $activityType) {
        $query = "INSERT INTO ACTIVITYLOGS (BLOODBANKID, ACTIVITY_TEXT, ACTIVITY_TYPE) 
                  VALUES ($bloodbankId, '$activityText', '$activityType')";
        return $conn->query($query);
    }
?>