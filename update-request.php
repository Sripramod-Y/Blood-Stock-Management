<?php
session_start();
if(!isset($_SESSION['bloodbank_id'])) {
    header("Location: index.php");
    exit();
}

include "db.php";
require_once 'notify-donors.php';

try {
    if(isset($_GET['id']) && isset($_GET['status'])) {
        $request_id = $conn->real_escape_string($_GET['id']);
        $status = $conn->real_escape_string($_GET['status']);
        $bloodbank_id = $_SESSION['bloodbank_id'];

        $request_query = "SELECT r.*, h.LOCATION as HOSPITAL_LOCATION 
                         FROM REQUESTS r
                         JOIN HOSPITALS h ON r.HOSPITALID = h.HOSPITALID
                         WHERE r.REQUESTID = '$request_id' 
                         AND r.BLOODBANKID = '$bloodbank_id'";
        $request_result = $conn->query($request_query);
        
        if($request_result->num_rows > 0) {
            $request = $request_result->fetch_assoc();
            
            if($status == 'Fulfilled') {

                $check_stock = "SELECT COUNT(*) as available FROM BLOODUNITS 
                               WHERE BLOODGROUP = '{$request['BLOODGROUP']}' 
                               AND STATUS = 'AVAILABLE'
                               AND BLOODBANKID = '$bloodbank_id'";
                $stock_result = $conn->query($check_stock);
                $available_units = $stock_result->fetch_assoc()['available'];
                
                if($available_units >= $request['UNITS']) {
                    $conn->autocommit(FALSE); 
                    
                    try {
                        
                        $get_unit_ids = "SELECT BLOODUNITID FROM BLOODUNITS
                                       WHERE BLOODGROUP = '{$request['BLOODGROUP']}'
                                       AND STATUS = 'AVAILABLE'
                                       AND BLOODBANKID = '$bloodbank_id'
                                       ORDER BY EXPIRY_DATE ASC
                                       LIMIT {$request['UNITS']}";
                        $ids_result = $conn->query($get_unit_ids);
                        
                        $unit_ids = array_column($ids_result->fetch_all(MYSQLI_ASSOC), 'BLOODUNITID');
                        
                        if(count($unit_ids) == $request['UNITS']) {
                            
                            $unit_ids_str = implode(",", $unit_ids);
                            $conn->query("UPDATE BLOODUNITS SET STATUS = 'USED' WHERE BLOODUNITID IN ($unit_ids_str)");
                            
                            
                            $conn->query("UPDATE REQUESTS SET STATUS = 'FULFILLED', TIME = NOW() WHERE REQUESTID = '$request_id'");
                            
                            $log_text = "Fulfilled request {$request_id} for {$request['UNITS']} units of {$request['BLOODGROUP']}";
                            logActivity($conn, $bloodbank_id, $log_text, 'USE_UNIT');
                            $conn->commit();
                            
                            header("Location: requests.php?success=fulfilled");
                            exit();
                        }
                    } catch (Exception $e) {
                        $conn->rollback();
                        header("Location: requests.php?success=fulfilled"); 
                        exit();
                    }
                }
                
                
                $conn->query("UPDATE REQUESTS SET STATUS = 'INSUFFICIENT_STOCK', TIME = NOW() WHERE REQUESTID = '$request_id'");
                header('Content-Type: application/json');
                echo json_encode(['action' => 'contact_donors']);
                exit();
                
            } else {
                
                $conn->query("UPDATE REQUESTS SET STATUS = 'REJECTED' WHERE REQUESTID = '$request_id'");
                logActivity($conn, $bloodbank_id, "Rejected request $request_id", 'USE_UNIT');
                header("Location: requests.php?success=rejected");
                exit();
            }
        }
    }
    header("Location: requests.php");
    exit();
    
} catch (Exception $e) {
    if(isset($conn)) {
        $conn->rollback();
    }
    header("Location: requests.php?success=fulfilled"); 
    exit();
}
?>