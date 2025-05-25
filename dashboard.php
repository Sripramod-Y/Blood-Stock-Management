<?php
    session_start();
    if(!isset($_SESSION['bloodbank_id'])){
        header("Location: index.php");
        exit();
    }
    include "db.php";
    foreach ($_SESSION['contacted_groups'] ?? [] as $group => $timestamp) {
        if (time() - $timestamp > 86400) { 
            unset($_SESSION['contacted_groups'][$group]);
        }
    }
    
    if(isset($_GET['emails_sent'])) {
        $success_count = (int)$_GET['success_count'];
        $message = '<div class="alert success">Successfully sent to '.$success_count.' '.htmlspecialchars($_GET['group']).' donors!</div>';
    } elseif(isset($_GET['email_error'])) {
        $failed_count = (int)$_GET['failed_count'];
        $message = '<div class="alert error">Failed to send '.$failed_count.' emails. Please check your mail server.</div>';
    } elseif(isset($_GET['no_donors'])) {
        $message = '<div class="alert error">No donors found for '.htmlspecialchars($_GET['group']).'!</div>';
    }
    
    $total_units_query = "SELECT COUNT(*) as total FROM BLOODUNITS WHERE BLOODBANKID=".$_SESSION['bloodbank_id'];
    $total_units_result = $conn->query($total_units_query);
    $total_units = $total_units_result->fetch_assoc()['total'];
    
    $available_units_query = "SELECT COUNT(*) as available FROM BLOODUNITS WHERE STATUS = 'Available' AND BLOODBANKID=".$_SESSION['bloodbank_id'];
    $available_units_result = $conn->query($available_units_query);
    $available_units = $available_units_result->fetch_assoc()['available'];
    
    $expiring_soon_query = "SELECT COUNT(*) as expiring FROM BLOODUNITS 
                           WHERE EXPIRY_DATE BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                           AND STATUS = 'Available' AND BLOODBANKID=".$_SESSION['bloodbank_id'];
    $expiring_soon_result = $conn->query($expiring_soon_query);
    $expiring_soon = $expiring_soon_result->fetch_assoc()['expiring'];
    
    
    $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    $stock_data = [];
    $low_stock_alerts = [];
    $low_stock_threshold = 2; 
    
    $potential_donors = [];
    
    foreach($blood_groups as $group) {
        $query = "SELECT COUNT(*) as count FROM BLOODUNITS 
                 WHERE BLOODGROUP = '$group' AND STATUS = 'Available' AND BLOODBANKID=".$_SESSION['bloodbank_id'];
        $result = $conn->query($query);
        $count = $result->fetch_assoc()['count'];
        $stock_data[$group] = $count;
        
        if ($count <= $low_stock_threshold || $count==0) {
            $low_stock_alerts[$group] = $count;
            
            $donor_query = "SELECT d.DONORID, d.DONORNAME, d.EMAIL, MAX(b.COLLECTION_DATE) as LAST_DONATION 
                           FROM DONORS d
                           LEFT JOIN BLOODUNITS b ON d.DONORID = b.DONORID
                           WHERE d.BLOODGROUP = '$group'
                           GROUP BY d.DONORID
                           ORDER BY LAST_DONATION ASC
                           LIMIT 3";
            $donor_result = $conn->query($donor_query);
            if ($donor_result->num_rows > 0) {
                $potential_donors[$group] = $donor_result->fetch_all(MYSQLI_ASSOC);
            }
        }
    }
    
    $usage_data = [];
    for($i = 3; $i >= 0; $i--) {
        $start_date = date('Y-m-d', strtotime('-'.($i+1).' weeks'));
        $end_date = date('Y-m-d', strtotime('-'.$i.' weeks'));
        
        $query = "SELECT COUNT(*) as used FROM BLOODUNITS 
                 WHERE STATUS = 'Used' AND BLOODBANKID=".$_SESSION['bloodbank_id']." 
                 AND COLLECTION_DATE BETWEEN '$start_date' AND '$end_date'";
        $result = $conn->query($query);
        $usage_data[] = $result->fetch_assoc()['used'];
    }
    $activity_query = "SELECT * FROM ACTIVITYLOGS 
    WHERE BLOODBANKID = ".$_SESSION['bloodbank_id']."
    ORDER BY TIMESTAMP DESC 
    LIMIT 5";
    $activity_result = $conn->query($activity_query);

    $pending_requests_query = "SELECT COUNT(*) as pending FROM REQUESTS WHERE STATUS = 'Pending' AND BLOODBANKID=".$_SESSION['bloodbank_id'];
    $pending_requests_result = $conn->query($pending_requests_query);
    $pending_requests = $pending_requests_result->fetch_assoc()['pending'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="./styles/dashboard.css">
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
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="blood-inventory.php">Blood Inventory</a>
            <a href="requests.php">Requests</a>
        </div>
        
        <div class="user-section">
            <div class="welcome-message">Welcome <strong><?php echo $_SESSION['bloodbank_name']; ?></strong></div>
            <a href="index.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Blood Bank <span>Dashboard</span></h1>
            <?php if(isset($message)) echo $message; ?>
        </div>

        <div class="cards-container">
            <div class="card">
                <h3 class="card-title">Total Blood Units</h3>
                <p class="card-value"><?php echo $total_units; ?></p>
                <div class="card-footer">
                    <i>↑</i> <span>All blood units in stock</span>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">Available Units</h3>
                <p class="card-value"><?php echo $available_units; ?></p>
                <div class="card-footer">
                    <i>↗</i> <span>Ready for transfusion</span>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">Expiring Soon</h3>
                <p class="card-value"><?php echo $expiring_soon; ?></p>
                <div class="card-footer">
                    <i>!</i> <span>Expiring within 7 days</span>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">Pending Requests</h3>
                <p class="card-value"><?php echo $pending_requests; ?></p>
                <div class="card-footer">
                    <i>↻</i> <span>Awaiting fulfillment</span>
                </div>
            </div>
        </div>
        <div class="charts-container">
            <div class="chart-card">
                <h3 class="chart-title">Blood Group Distribution</h3>
                <div class="chart-wrapper">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h3 class="chart-title">Weekly Usage Trend</h3>
                <div class="chart-wrapper">
                    <canvas id="usageChart"></canvas>
                </div>
            </div>
        </div>
        <div class="alerts-activity-container">
            <div class="alerts-container">
                <h3 class="alerts-title">
                    <i>⚠️</i> Low Stock Alerts (≤ <?php echo $low_stock_threshold; ?> units)
                </h3>
                
                <?php if (!empty($low_stock_alerts)): ?>
                    <table class="alert-table">
                        <thead>
                            <tr>
                                <th>Blood Group</th>
                                <th>Units Available</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock_alerts as $group => $count): ?>
                                <tr>
                                    <td class="alert-group"><?php echo $group; ?></td>
                                    <td class="alert-value"><?php echo $count; ?> units</td>
                                    <td>
                                        <?php if (isset($potential_donors[$group])): ?>
                                            <?php if (isset($_SESSION['contacted_groups'][$group])): ?>
                                                <span class="contacted-text">
                                                    <i class="fas fa-check-circle"></i> Donors Contacted
                                                </span>
                                            <?php else: ?>
                                                <button class="contact-btn" onclick="contactDonors('<?php echo $group; ?>')">
                                                    Contact Donors
                                                </button>
                                            <?php endif; ?>
                                            <div class="donor-list">
                                                <?php foreach($potential_donors[$group] as $donor): ?>
                                                    <div class="donor-item">
                                                        <span><?php echo htmlspecialchars($donor['DONORNAME']); ?></span>
                                                        <span><?php echo htmlspecialchars($donor['EMAIL']); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #aaa;">No recent donors found</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-alerts">All blood groups are sufficiently stocked</div>
                <?php endif; ?>
            </div>

            <div class="activity-container">
                <h3 class="activity-title">
                    <i>📝</i> Recent Activities
                </h3>
                
                <?php if($activity_result->num_rows > 0): ?>
                    <?php while($activity = $activity_result->fetch_assoc()): ?>
                        <div class="activity-item">
                            <div class="activity-message"><?php echo $activity['ACTIVITY_TEXT']; ?></div>
                            <div class="activity-meta">
                                <span><?php echo date('d M Y, h:i A', strtotime($activity['TIMESTAMP'])); ?></span>
                                <span class="activity-type type-<?php echo strtolower($activity['ACTIVITY_TYPE']); ?>">
                                    <?php echo $activity['ACTIVITY_TYPE']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-activities">No recent activities found</div>
                <?php endif; ?>
            </div>
        </div>

        
    </div>
    <script>
       const stockCtx = document.getElementById('stockChart').getContext('2d');
        const stockChart = new Chart(stockCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($blood_groups); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($stock_data)); ?>,
                    backgroundColor: [
                        '#5c0000', 
                        '#450000', 
                        '#6e0b0b', 
                        '#4a0707', 
                        '#7a0000', 
                        '#5e0000', 
                        '#8b0000', 
                        '#520000'  
                    ],
                    borderColor: 'rgba(20, 20, 20, 0.85)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#fff',
                            font: {
                                family: 'Poppins'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw} units`;
                            }
                        },
                        bodyFont: {
                            family: 'Poppins'
                        }
                    }
                }
            }
        });

        const usageCtx = document.getElementById('usageChart').getContext('2d');
        const usageChart = new Chart(usageCtx, {
            type: 'bar',
            data: {
                labels: [
                    '<?php echo date("M j", strtotime("-3 weeks")); ?> - <?php echo date("M j", strtotime("-2 weeks")); ?>',
                    '<?php echo date("M j", strtotime("-2 weeks")); ?> - <?php echo date("M j", strtotime("-1 week")); ?>',
                    '<?php echo date("M j", strtotime("-1 week")); ?> - <?php echo date("M j"); ?>',
                    'This Week'
                ],
                datasets: [{
                    label: 'Units Used',
                    data: <?php echo json_encode($usage_data); ?>,
                    backgroundColor: 'rgba(139, 0, 0, 0.7)',
                    borderColor: 'rgba(139, 0, 0, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#fff',
                            stepSize: 1,
                            font: {
                                family: 'Poppins'
                            }
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#fff',
                            font: {
                                family: 'Poppins'
                            }
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#fff',
                            font: {
                                family: 'Poppins'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        },
                        bodyFont: {
                            family: 'Poppins'
                        }
                    }
                }
            }
        });
        function contactDonors(group){
            if(confirm(`Send donation request emails to all ${group} donors?`)) {
                window.location.href = `contact-donors.php?group=${encodeURIComponent(group)}`;
            }
        }
    </script>
</html>