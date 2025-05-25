<?php
session_start();
if(!isset($_SESSION['bloodbank_id'])) {
    header("Location: index.php");
    exit();
}

include "db.php";

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $bloodbankId = $_SESSION['bloodbank_id']; 
    
    $query = "DELETE FROM BLOODUNITS WHERE BLOODUNITID = $id AND BLOODBANKID = $bloodbankId";
    
    if($conn->query($query)) {
        $activityText = "Deleted blood unit $id";
        $logResult = logActivity($conn, $bloodbankId, $activityText, 'DELETE_UNIT');
        
        header("Location: blood-inventory.php");
    exit();
    } else {
        error_log("Delete failed: " . $conn->error);
    }
}
header("Location: blood-inventory.php");
?>