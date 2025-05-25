<?php
    session_start();
    include "db.php";
    if(isset($_POST['login'])){
        $id = $_POST['id'];
        $license = $_POST['license'];

        $sql = "SELECT LICENSEID,BLOODBANKNAME FROM BLOODBANK WHERE BLOODBANKID = $id";

        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            if($row['LICENSEID'] == $license){
                $_SESSION['bloodbank_id'] = $id;
                $_SESSION['bloodbank_name'] = $row['BLOODBANKNAME'];
                header("Location: dashboard.php");
                exit();
            }else{
                echo '<div class="message error">Invalid Login Credentials!</div>';
            }
        } else {
            echo '<div class="message error">Blood Bank ID not found!</div>';
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel=stylesheet href="./styles/login.css">
</head>
<body>
   
    <svg class="ecg-line" viewBox="0 0 1440 800" preserveAspectRatio="none">
        <path class="ecg-path" d="M0,400 Q100,350 200,400 T400,300 T600,450 T800,350 T1000,450 T1200,300 T1400,400 L1440,400" />
        <path class="ecg-path" d="M0,500 Q100,450 200,500 T400,400 T600,550 T800,450 T1000,550 T1200,400 T1400,500 L1440,500" />
    </svg>

    <div class="heart-container">
        <div class="heart"></div>
    </div>


    <div class="login-container">
        <h1>BLOOD BANK LOGIN</h1>
        <form action="" method="post">
            <label for="bloodbank-id">Blood Bank ID</label>
            <input name="id" type="text" required placeholder="Enter your blood bank ID">
            
            <label for="license-id">License ID</label>
            <input name="license" type="text" required placeholder="Enter your license ID">
            
            <button name="login">LOGIN</button>

            <div class="register-link">
                Need access to our system? <a href="register.php">Register your blood bank</a>
            </div>

           
        </form>
    </div>

   
    <div class="guidelines-container">
        <h2>BLOOD BANK COMPLIANCE GUIDELINES</h2>
        <ul class="guidelines-list">
            <li>All blood banks must obtain proper licensing from national health authorities</li>
            <li>Maintain strict temperature control (2-6°C for whole blood components)</li>
            <li>Implement comprehensive donor screening protocols</li>
            <li>Conduct regular equipment calibration and validation</li>
            <li>Ensure proper blood grouping and cross-matching procedures</li>
            <li>Maintain complete and accurate records for all blood units</li>
            <li>Report adverse events within 24 hours to regulatory bodies</li>
            <li>Follow standardized testing for transfusion-transmissible infections</li>
        </ul>
    </div>
</body>
</html>