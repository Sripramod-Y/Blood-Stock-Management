<?php
session_start();
include "db.php";

if(isset($_POST['login'])) {
    $hospital_id = $conn->real_escape_string($_POST['hospital_id']);
    $license_key = $conn->real_escape_string($_POST['license_key']);
    
    $query = "SELECT * FROM HOSPITALS WHERE HOSPITALID = '$hospital_id' AND LICENSEID = '$license_key'";
    $result = $conn->query($query);
    
    if($result->num_rows > 0) {
        $_SESSION['hospital_id'] = $hospital_id;
        $_SESSION['hospital_name'] = $result->fetch_assoc()['LOCATION'];
        header("Location: hospital-dashboard.php");
        exit();
    } else {
        $login_error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Login | Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/hospital-login.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="page-title">Hospital <span>Login</span></h2>
            
            <?php if(isset($login_error)): ?>
                <div class="message error"><?php echo $login_error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="hospital_id">Hospital ID</label>
                    <input type="text" name="hospital_id" id="hospital_id" required>
                </div>
                
                <div class="form-group">
                    <label for="license_key">License Key</label>
                    <input type="password" name="license_key" id="license_key" required>
                </div>
                
                <button type="submit" name="login" class="submit-btn">Login</button>
            </form>
        </div>
    </div>
</body>
</html>