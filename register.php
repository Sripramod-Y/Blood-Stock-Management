<?php
include "db.php";
if(isset($_POST['register'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $license = $_POST['license'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    $sql = "SELECT BLOODBANKID FROM BLOODBANK WHERE BLOODBANKID = $id";
    $result = $conn->query($sql);
    
    if($result->num_rows > 0) {
        $error_message = "Blood Bank ID already exists!";
    } else {

        $sql = "INSERT INTO BLOODBANK(BLOODBANKID, LICENSEID, BLOODBANKNAME, CONTACT, LOCATION) VALUES('$id', '$license', '$name', '$contact', '$address')";
        
        if($conn->query($sql) === TRUE) {
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Registration Failed: ".$conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel=stylesheet href="./styles/register.css">
</head>
<body>
    <div class="register-container">
        <h1>BLOOD BANK REGISTRATION</h1>
        <form action="" method="post">
            <label for="bloodbank-id">Blood Bank ID</label>
            <input type="text" name="id" required placeholder="Enter unique blood bank ID">
            
            <label for="bloodbank-name">Blood Bank Name</label>
            <input type="text" name="name" required placeholder="Enter blood bank name">
            
            <label for="license-id">License ID</label>
            <input type="text" name="license" required placeholder="Enter government license ID">

            <label for="contact">Contact Number</label>
            <input type="text" name="contact" required placeholder="If Landline format 044 xxxx..">

            <label for="address">Address</label>
            <textarea name="address" required></textarea>
            
            <button name="register">REGISTER</button>

            <div class="login-link">
                Already registered? <a href="index.php">Login to your account</a>
            </div>

            <?php if(isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>