<?php
session_start();
if(!isset($_SESSION['bloodbank_id'])) {
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

include "db.php";
require_once 'notify-donors.php';

header('Content-Type: application/json');

if(isset($_GET['request_id'])) {
    $request_id = $conn->real_escape_string($_GET['request_id']);
    $bloodbank_id = $_SESSION['bloodbank_id'];

    error_log("Processing request_id: $request_id");

    $request_query = "SELECT r.*, h.LOCATION 
                     FROM REQUESTS r
                     JOIN HOSPITALS h ON r.HOSPITALID = h.HOSPITALID
                     WHERE r.REQUESTID = '$request_id' 
                     AND r.BLOODBANKID = '$bloodbank_id'";
    
    $request_result = $conn->query($request_query);
    
    if($request_result && $request_result->num_rows > 0) {
        $request = $request_result->fetch_assoc();
  
        error_log("Found request: " . print_r($request, true));
        
        $result = contactDonors(
            $conn, 
            $bloodbank_id, 
            $request['BLOODGROUP'], 
            $request['UNITS'], 
            $request['LOCATION']
        );
        
        if($result['success_count'] > 0) {
            $update_request = "UPDATE REQUESTS 
                             SET STATUS = 'CONTACTED DONORS', 
                             TIME = NOW() 
                             WHERE REQUESTID = '$request_id'";
            $conn->query($update_request);
            
            echo json_encode([
                'success' => true,
                'count' => $result['success_count'], 
                'message' => $result['success_count'] . ' donors contacted successfully'
            ]);
        } else {
            $update_request = "UPDATE REQUESTS 
                             SET STATUS = 'NO DONORS AVAILABLE', 
                             TIME = NOW() 
                             WHERE REQUESTID = '$request_id'";
            $conn->query($update_request);
            
            echo json_encode([
                'success' => false,
                'error' => 'no_donors',
                'message' => 'No available donors found'
            ]);
        }
    } else {
        error_log("Request not found or query failed");
        echo json_encode([
            'success' => false,
            'error' => 'request_not_found',
            'query_error' => $conn->error ?? null,
            'message' => 'Request not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'missing_request_id',
        'message' => 'Request ID is missing'
    ]);
}
?>