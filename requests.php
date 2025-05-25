<?php
    session_start();
    if(!isset($_SESSION['bloodbank_id'])) {
        header("Location: index.php");
        exit();
    }

    include "db.php";

    $query = "SELECT r.*, h.LOCATION as HOSPITAL_LOCATION 
            FROM REQUESTS r
            JOIN HOSPITALS h ON r.HOSPITALID = h.HOSPITALID
            WHERE r.BLOODBANKID = ".$_SESSION['bloodbank_id']." AND r.STATUS= 'PENDING'
            ORDER BY r.REQUESTID DESC";
    $result = $conn->query($query);
    $message = '';
    if(isset($_GET['success'])) {
        switch($_GET['success']) {
            case 'fulfilled':
                $message = '<div class="alert success">Request fulfilled successfully!</div>';
                break;
            case 'rejected':
                $message = '<div class="alert success">Request rejected successfully!</div>';
                break;
            case 'donors_contacted':
                $donorsCount = isset($_GET['count']) ? intval($_GET['count']) : 0;
                $message = '<div class="alert success">'.$donorsCount.' donors contacted successfully!</div>';
                break;
        }
    } elseif(isset($_GET['error']) && $_GET['error'] == 'insufficient_stock') {
        $message = '<div class="alert warning">Insufficient stock to fulfill request!</div>';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Requests | Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/requests.css">
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
            <a href="blood-inventory.php">Blood Inventory</a>
            <a href="requests.php" class="active">Requests</a>
        </div>
        
        <div class="user-section">
            <div class="welcome-message">Welcome <strong><?php echo $_SESSION['bloodbank_name']; ?></strong></div>
            <a href="hospital-login.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Blood <span>Requests</span></h1>
        <?php echo $message; ?>
        <div class="requests-container">
            <?php if($result->num_rows > 0): ?>
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Hospital</th>
                            <th>Blood Group</th>
                            <th>Units</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['REQUESTID']; ?></td>
                                <td><?php echo htmlspecialchars($row['HOSPITAL_LOCATION']); ?></td>
                                <td><?php echo $row['BLOODGROUP']; ?></td>
                                <td><?php echo $row['UNITS']; ?></td>
                                <td><?php echo date('d M Y, h:i A', strtotime($row['TIME'])); ?></td>
                                <td class="status-<?php echo strtolower($row['STATUS']); ?>">
                                    <?php echo $row['STATUS']; ?>
                                </td>
                                <td>
                                    <?php if(strtolower($row['STATUS']) == 'pending'): ?>
                                        <button class="action-btn fulfill-btn" 
                                                onclick="fulfillRequest(<?php echo $row['REQUESTID']; ?>)">
                                            Fulfill
                                        </button>
                                        <button class="action-btn reject-btn" 
                                                onclick="rejectRequest(<?php echo $row['REQUESTID']; ?>)">
                                            Reject
                                        </button>
                                    <?php else: ?>
                                        <span class="action-completed">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-requests">No blood requests found</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        async function fulfillRequest(requestId) {
            try {
                const response = await fetch(`update-request.php?id=${requestId}&status=Fulfilled`);
                if(response.ok) {
                    const result = await response.json();
                    if(result.action === 'contact_donors') {
                        
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'alert warning';
                        msgDiv.textContent = 'Contacting donors...';
                        document.querySelector('.container').prepend(msgDiv);
                        
                        
                        const contactResp = await fetch(`process-contact.php?request_id=${requestId}`);
                        const contactResult = await contactResp.json();
                        
                        if(contactResult.success) {
                            window.location.href = `requests.php?success=donors_contacted&count=${contactResult.count || 0}`;
                        } else {
                            window.location.href = 'requests.php?error=insufficient_stock';
                        }
                    } else {
                        window.location.href = 'requests.php?success=fulfilled';
                    }
                } else {
                    window.location.href = 'requests.php?success=fulfilled';
                }
            } catch(error) {
                window.location.href = 'requests.php?success=fulfilled';
            }
        }

        function rejectRequest(requestId) {
            if(confirm('Are you sure you want to reject this request?')) {
                window.location.href = `update-request.php?id=${requestId}&status=Rejected`;
            }
        }
    </script>
</body>
</html>