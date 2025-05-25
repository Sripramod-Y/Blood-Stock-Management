<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function contactDonors($conn, $bloodbank_id, $blood_group, $units_needed, $hospital_location) {
    $success_count = 0;
    $failed_emails = [];
    
    $query = "SELECT d.DONORNAME, d.EMAIL 
             FROM DONORS d
             WHERE d.BLOODGROUP = '$blood_group' 
             AND d.BLOODBANKID = $bloodbank_id
             ORDER BY d.LAST_DONATION_DATE ASC
             LIMIT 10";
    
    $result = $conn->query($query);
    
    if($result->num_rows > 0) {
        while($donor = $result->fetch_assoc()) {
            $mail = new PHPMailer(true);
            
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = ' '; //Your email
                $mail->Password = ' '; //Your password
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
                        <p>We urgently need <strong>$units_needed units</strong> of <strong>$blood_group</strong> blood.</p>
                        <p>Location: $hospital_location</p>
                        <p>Please visit us at your earliest convenience to help save lives.</p>
                        <p>Thank you for your support!</p>
                    </body>
                    </html>
                ";
                
                if($mail->send()) {
                    $success_count++;
                    $conn->query("UPDATE DONORS SET LAST_CONTACTED = NOW() WHERE EMAIL = '{$donor['EMAIL']}'");
                    
                    logActivity($conn, $bloodbank_id, "Sent request to {$donor['EMAIL']}", 'SEND_NOTIFICATION');
                } else {
                    $failed_emails[] = $donor['EMAIL'];
                    logActivity($conn, $bloodbank_id, "Failed to send to {$donor['EMAIL']}", 'SEND_NOTIFICATION');
                }
            } catch (Exception $e) {
                $failed_emails[] = $donor['EMAIL'];
                logActivity($conn, $bloodbank_id, "Mailer Error for {$donor['EMAIL']}: {$mail->ErrorInfo}", 'SEND_NOTIFICATION');
            }
        }
        
        return [
            'success_count' => $success_count,
            'failed_emails' => $failed_emails
        ];
    }
    
    return [
        'success_count' => 0,
        'failed_emails' => []
    ];
}
?>
