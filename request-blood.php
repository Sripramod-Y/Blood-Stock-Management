<?php
    session_start();
    include "db.php";


    if(!isset($_SESSION['hospital_id'])) {
        header("Location: hospital-login.php");
        exit();
    }

    
    if(isset($_POST['request_blood'])) {
        $bloodbank_id = $conn->real_escape_string($_POST['bloodbank_id']);
        $blood_group = $conn->real_escape_string($_POST['blood_group']);
        $units = (int)$_POST['units'];
        
        $query = "INSERT INTO REQUESTS (HOSPITALID, BLOODBANKID, BLOODGROUP, UNITS, STATUS) 
                VALUES ('{$_SESSION['hospital_id']}', '$bloodbank_id', '$blood_group', $units, 'PENDING')";
        
        if($conn->query($query)) {
            header("Location: hospital-dashboard.php?success=1");
            exit();
        } else {
            $request_error = "Failed to submit request: " . $conn->error;
        }
    }


    $bloodbanks_query = "SELECT BLOODBANKID, BLOODBANKNAME FROM BLOODBANK";
    $bloodbanks = $conn->query($bloodbanks_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Blood | Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/hospital-dashboard.css">
</head>
<body>
    <div class="container">
        <h2 class="page-title">Request Blood</h2>
        
        <a href="hospital-dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="request-container">
            <?php if(isset($request_error)): ?>
                <div class="message error"><?php echo $request_error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="bloodbank_id">Blood Bank</label>
                    <select name="bloodbank_id" id="bloodbank_id" required>
                        <option value="">Select Blood Bank</option>
                        <?php while($bank = $bloodbanks->fetch_assoc()): ?>
                            <option value="<?php echo $bank['BLOODBANKID']; ?>">
                                <?php echo htmlspecialchars($bank['BLOODBANKNAME']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="blood_group">Blood Group</label>
                    <select name="blood_group" id="blood_group" required>
                        <option value="">Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="units">Units Required</label>
                    <input type="number" name="units" id="units" min="1" required>
                </div>
                
                <button type="submit" name="request_blood" class="submit-btn">Submit Request</button>
            </form>
        </div>
    </div>
</body>
</html>