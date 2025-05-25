<?php
session_start();
include "db.php";

if(!isset($_SESSION['hospital_id'])) {
    header("Location: hospital-login.php");
    exit();
}

$requests_query = "SELECT r.*, b.BLOODBANKNAME 
                  FROM REQUESTS r
                  JOIN BLOODBANK b ON r.BLOODBANKID = b.BLOODBANKID
                  WHERE r.HOSPITALID = '{$_SESSION['hospital_id']}'
                  ORDER BY r.TIME DESC";
$requests = $conn->query($requests_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard | Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/hospital-dashboard.css">
</head>
<body>
    <div class="container">
        <h2 class="page-title">Welcome, <span><?php echo $_SESSION['hospital_name']; ?></span></h2>
        
        
        <div class="requests-table-container">
            <h3>Your Blood Requests</h3>
            
            <a href="request-blood.php" class="request-btn">+ Request Blood</a>
            
            <?php if($requests->num_rows > 0): ?>
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Blood Bank</th>
                            <th>Blood Group</th>
                            <th>Units</th>
                            <th>Request Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($request = $requests->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $request['REQUESTID']; ?></td>
                                <td><?php echo htmlspecialchars($request['BLOODBANKNAME']); ?></td>
                                <td><?php echo $request['BLOODGROUP']; ?></td>
                                <td><?php echo $request['UNITS']; ?></td>
                                <td><?php echo date('d M Y, h:i A', strtotime($request['TIME'])); ?></td>
                                <td class="status-<?php echo strtolower($request['STATUS']); ?>">
                                    <?php echo $request['STATUS']; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-requests">No blood requests found</div>
            <?php endif; ?>
        </div>
        
        <a href="hospital-login.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>