<?php
session_start();
if(!isset($_SESSION['bloodbank_id'])) {
    header("Location: index.php");
    exit();
}

include "db.php";

if(isset($_GET['id']) && isset($_GET['status'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $status = $conn->real_escape_string($_GET['status']);
    
    $bloodbankId=$_SESSION['bloodbank_id'];
    
    $query = "UPDATE BLOODUNITS SET STATUS = '$status' WHERE BLOODUNITID = '$id' AND BLOODBANKID = ".$_SESSION['bloodbank_id'];
    
    if($conn->query($query)) {
        $activityText = "Updated blood unit $id to status $status";
        logActivity($conn, $bloodbankId, $activityText, 'USE_UNIT');
        header("Location: blood-inventory.php");
        exit();
    } else {
        die("Update failed: " . $conn->error);
    }
}
?>