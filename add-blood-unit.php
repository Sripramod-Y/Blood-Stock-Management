<?php
session_start();
if(!isset($_SESSION['bloodbank_id'])) {
    header("Location: index.php");
    exit();
}

include "db.php";


if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $blood_group = $conn->real_escape_string($_POST['blood_group']);
    $donor_name = $conn->real_escape_string($_POST['donor_name']);
    $contact_no = $conn->real_escape_string($_POST['contact_no']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $collection_date = $conn->real_escape_string($_POST['collection_date']);
    $expiry_date = date('Y-m-d', strtotime($collection_date . ' + 42 days'));
    
    
    $donor_query = "SELECT DONORID, BLOODGROUP, LAST_DONATION_DATE FROM DONORS WHERE CONTACT_NO = '$contact_no' LIMIT 1";
    $donor_result = $conn->query($donor_query);
    
    if($donor_result->num_rows > 0) {
        $donor = $donor_result->fetch_assoc();
        $donor_id = $donor['DONORID'];
        $donor_blood_group = $donor['BLOODGROUP'];
        $last_donation = $donor['LAST_DONATION_DATE'];
        
        if(strtoupper($blood_group) !== strtoupper($donor_blood_group)) {
            $error = "$donor_name belongs to $donor_blood_group group!";
            header("Location: add-blood-unit.php?error=" . urlencode($error));
            exit();
        }
        
        $next_eligible_date = date('Y-m-d', strtotime($last_donation . ' + 3 months'));
        
        if($collection_date < $next_eligible_date) {
            $error = "$donor_name cannot donate until " . date('d/m/Y', strtotime($next_eligible_date));
            header("Location: add-blood-unit.php?error=" . urlencode($error));
            exit();
        }
        
        $update_donor = "UPDATE DONORS SET LAST_DONATION_DATE = '$collection_date' WHERE DONORID = $donor_id";
        $conn->query($update_donor);
    } else {
        // New donor
        $insert_donor = "INSERT INTO DONORS (
            BLOODBANKID, DONORNAME, CONTACT_NO, EMAIL, ADDRESS, BLOODGROUP, ISAVAILABLE, LAST_DONATION_DATE
        ) VALUES (
            {$_SESSION['bloodbank_id']}, '$donor_name', '$contact_no', '$email', '$address', '$blood_group', 1, '$collection_date'
        )";
        
        if($conn->query($insert_donor)) {
            $donor_id = $conn->insert_id;
        } else {
            die("Error creating donor: " . $conn->error);
        }
    }
    
    // Add blood unit
    $add_unit = "INSERT INTO BLOODUNITS (
        BLOODBANKID, BLOODGROUP, DONORID, STATUS, COLLECTION_DATE, EXPIRY_DATE
    ) VALUES (
        {$_SESSION['bloodbank_id']}, '$blood_group', $donor_id, 'AVAILABLE', '$collection_date', '$expiry_date'
    )";
    
    if($conn->query($add_unit)) {
        $activityText = "Added new blood unit (ID: $conn->insert_id) - Type: $blood_group";
        logActivity($conn, $_SESSION['bloodbank_id'], $activityText, 'ADD_UNIT');
        header("Location: blood-inventory.php?success=1");
    } else {
        die("Error adding blood unit: " . $conn->error);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blood Unit | Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/add-blood-unit.css">
</head>
<body>
<svg class="ecg-line" viewBox="0 0 1440 800" preserveAspectRatio="none">
        <path class="ecg-path" d="M0,400 Q100,350 200,400 T400,300 T600,450 T800,350 T1000,450 T1200,300 T1400,400 L1440,400" />
        <path class="ecg-path" d="M0,500 Q100,450 200,500 T400,400 T600,550 T800,450 T1000,550 T1200,400 T1400,500 L1440,500" />
    </svg>

    <div class="heart-container">
        <div class="heart"></div>
    </div>

    <nav class="navbar">
        <a href="dashboard.php" class="nav-logo">Blood<span>Stock</span></a>
        
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="blood-inventory.php" class="active">Blood Inventory</a>
            <a href="requests.php">Requests</a>
        </div>
        
        <div class="user-section">
            <div class="welcome-message">Welcome <strong><?php echo $_SESSION['bloodbank_name']; ?></strong></div>
            <a href="index.php" class="logout-btn">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <h1 class="page-title">Add <span>Blood Unit</span></h1>
         <?php if(isset($_GET['error'])): ?>
            <div class="error-message">
                <i>⚠️</i>
                <span><?php echo htmlspecialchars($_GET['error']); ?></span>
            </div>
        <?php endif; ?>
        <form method="POST" class="blood-unit-form">
            <div class="form-group">
                <label for="blood_group">Blood Type</label>
                <select name="blood_group" id="blood_group" required>
                    <option value="">Select Blood Type</option>
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
                <label for="donor_name">Donor Name</label>
                <input type="text" name="donor_name" id="donor_name" required>
            </div>
            
            <div class="form-group">
                <label for="contact_no">Contact Number</label>
                <input type="text" name="contact_no" id="contact_no" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email* </label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address (Optional)</label>
                <input type="text" name="address" id="address">
            </div>
            
            <div class="form-group">
                <label for="collection_date">Collection Date</label>
                <input type="date" name="collection_date" id="collection_date" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="add-btn">Add Blood Unit</button>
                <a href="blood-inventory.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>