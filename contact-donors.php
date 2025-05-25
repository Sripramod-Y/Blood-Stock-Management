<?php
session_start();
if (!isset($_SESSION['bloodbank_id'])) {
    header("Location: index.php");
    exit();
}
$_SESSION['contacted_groups'] = $_SESSION['contacted_groups'] ?? [];


if ($success_count > 0) {
    $_SESSION['contacted_groups'][$blood_group] = time(); 
    header("Location: dashboard.php?emails_sent=1&group=" . urlencode($blood_group) . "&success_count=$success_count");
}

include "db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if (isset($_GET['group'])) {
    $blood_group = $conn->real_escape_string($_GET['group']);
    $bloodbank_id = $_SESSION['bloodbank_id'];

    $query = "SELECT DONORNAME, EMAIL FROM DONORS 
              WHERE BLOODGROUP = '$blood_group' AND BLOODBANKID = $bloodbank_id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $success_count = 0;
        $failed_emails = [];

        while ($donor = $result->fetch_assoc()) {
            $mail = new PHPMailer(true);

            try {
                
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';               
                $mail->SMTPAuth = true;
                $mail->Username = ' '; //Your email    
                $mail->Password = ' '; //Your Password            
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('yourbloodbank@example.com', 'Blood Bank System');
                $mail->addAddress($donor['EMAIL'], $donor['DONORNAME']);

                
                $mail->isHTML(true);
                $mail->Subject = "Urgent Blood Donation Request";
                $mail->Body = "
                    <html>
                    <body>
                        <h2>Dear {$donor['DONORNAME']},</h2>
                        <p>Our blood bank currently has <strong>low stock</strong> of <strong>$blood_group</strong> blood.</p>
                        <p>We urgently request your donation. Please visit the blood bank at your earliest convenience.</p>
                        <p>Thank you for being a lifesaver!</p>
                    </body>
                    </html>
                ";

                if ($mail->send()) {
                    $success_count++;

                    
                    $log_query = "INSERT INTO ACTIVITYLOGS 
                                 (BLOODBANKID, ACTIVITY_TYPE, ACTIVITY_TEXT) 
                                 VALUES 
                                 ($bloodbank_id, 'SEND_NOTIFICATION', 'Sent request to {$donor['EMAIL']}')";
                    $conn->query($log_query);
                } else {
                    $failed_emails[] = $donor['EMAIL'];

                    
                    $log_query = "INSERT INTO ACTIVITYLOGS 
                                 (BLOODBANKID, ACTIVITY_TYPE, ACTIVITY_TEXT) 
                                 VALUES 
                                 ($bloodbank_id, 'SEND_NOTIFICATION', 'Failed to send to {$donor['EMAIL']}')";
                    $conn->query($log_query);
                }
            } catch (Exception $e) {
                $failed_emails[] = $donor['EMAIL'];

                
                $log_query = "INSERT INTO ACTIVITYLOGS 
                             (BLOODBANKID, ACTIVITY_TYPE, ACTIVITY_TEXT) 
                             VALUES 
                             ($bloodbank_id, 'SEND_NOTIFICATION', 'Mailer Error for {$donor['EMAIL']}: {$mail->ErrorInfo}')";
                $conn->query($log_query);
            }
        }

        
        if ($success_count > 0) {
            $_SESSION['contacted_groups'][$blood_group] = time();
            header("Location: dashboard.php?emails_sent=1&group=" . urlencode($blood_group) . "&success_count=$success_count");
            exit();
        }else {
            header("Location: dashboard.php?email_error=1&failed_count=" . count($failed_emails));
        }
        exit();
    } else {
        header("Location: dashboard.php?no_donors=1&group=" . urlencode($blood_group));
        exit();
    }
}
?>
