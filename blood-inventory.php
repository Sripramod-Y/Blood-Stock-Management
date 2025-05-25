<?php
session_start();
if(!isset($_SESSION['bloodbank_id'])) {
    header("Location: index.php");
    exit();
}
include "db.php";


$search = '';
$blood_group = '';
$status = '';
$error = '';


if(isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}
if(isset($_GET['blood_group'])) {
    $blood_group = $conn->real_escape_string($_GET['blood_group']);
}
if(isset($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
}


$query = "SELECT b.*, d.DONORNAME, d.CONTACT_NO
          FROM BLOODUNITS b
          LEFT JOIN DONORS d ON b.DONORID = d.DONORID
          WHERE b.BLOODBANKID=".$_SESSION['bloodbank_id'];


if(!empty($search)) {
    $query .= " AND (d.DONORNAME LIKE '%$search%' OR b.BLOODUNITID LIKE '%$search%')";
}
if(!empty($blood_group)) {
    $query .= " AND b.BLOODGROUP = '$blood_group'";
}
if(!empty($status)) {
    $query .= " AND b.STATUS = '$status'";
}


$query .= " ORDER BY b.EXPIRY_DATE ASC";


$result = $conn->query($query);
if($result === false) {
    $error = "Database error: " . $conn->error;
}

$blood_counts = [];
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
foreach($blood_groups as $group) {
    $count_query = "SELECT COUNT(*) as count FROM BLOODUNITS 
                   WHERE BLOODGROUP = '$group' AND STATUS = 'Available' AND BLOODBANKID=".$_SESSION['bloodbank_id']."";
    $count_result = $conn->query($count_query);
    if($count_result !== false) {
        $blood_counts[$group] = $count_result->fetch_assoc()['count'];
    } else {
        $blood_counts[$group] = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory | Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/blood-inventory.css">
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
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>


    <div class="container">
        <h1 class="page-title">Blood <span>Inventory</span></h1>
        

        <div class="blood-group-badges">
            <?php foreach($blood_counts as $group => $count): ?>
                <div class="blood-badge">
                    <span><?php echo $group; ?></span>
                    <span><?php echo $count; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="inventory-header">
            <form method="GET" class="search-filter-container">
                <input type="text" name="search" class="search-box" placeholder="Search by donor or unit ID..." value="<?php echo htmlspecialchars($search); ?>">
                
                <select name="blood_group" class="filter-select">
                    <option value="">All Blood Types</option>
                    <option value="A+" <?php echo $blood_group == 'A+' ? 'selected' : ''; ?>>A+</option>
                    <option value="A-" <?php echo $blood_group == 'A-' ? 'selected' : ''; ?>>A-</option>
                    <option value="B+" <?php echo $blood_group == 'B+' ? 'selected' : ''; ?>>B+</option>
                    <option value="B-" <?php echo $blood_group == 'B-' ? 'selected' : ''; ?>>B-</option>
                    <option value="AB+" <?php echo $blood_group == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                    <option value="AB-" <?php echo $blood_group == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                    <option value="O+" <?php echo $blood_group == 'O+' ? 'selected' : ''; ?>>O+</option>
                    <option value="O-" <?php echo $blood_group == 'O-' ? 'selected' : ''; ?>>O-</option>
                </select>
                
                <select name="status" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="Available" <?php echo $status == 'Available' ? 'selected' : ''; ?>>Available</option>
                    <option value="Used" <?php echo $status == 'Used' ? 'selected' : ''; ?>>Used</option>
                    <option value="Expired" <?php echo $status == 'Expired' ? 'selected' : ''; ?>>Expired</option>
                </select>
                
                <button type="submit" class="filter-btn">Apply Filters</button>
                <a href="blood-inventory.php" class="filter-btn-link">Reset</a>
            </form>
            
            <a href="add-blood-unit.php" class="add-btn">+ Add New Unit</a>
        </div>
 
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Unit ID</th>
                    <th>Blood Type</th>
                    <th>Donor</th>
                    <th>Contact</th>
                    <th>Collection Date</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['BLOODUNITID']; ?></td>
                            <td><?php echo $row['BLOODGROUP']; ?></td>
                            <td><?php echo $row['DONORNAME'] ?? 'Unknown'; ?></td>
                            <td><?php echo $row['CONTACT_NO'] ?? 'N/A'; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['COLLECTION_DATE'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['EXPIRY_DATE'])); ?></td>
                            <td class="status-<?php echo strtolower($row['STATUS']); ?>">
                                <?php echo $row['STATUS']; ?>
                            </td>
                            <td>
                                <?php if(strcasecmp($row['STATUS'], 'Available') === 0): ?>
                                    <button class="action-btn use-btn" data-id="<?php echo $row['BLOODUNITID']; ?>">Use</button>
                                <?php endif; ?>
                                <button class="action-btn delete-btn" data-id="<?php echo $row['BLOODUNITID']; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No blood units found matching your criteria</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.querySelectorAll('.use-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const unitId = this.getAttribute('data-id');
                if(confirm('Mark this blood unit as used?')) {
                    window.location.href = `update-status.php?id=${unitId}&status=Used`;
                }
            });
        });
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const unitId = this.getAttribute('data-id');
                if(confirm('Permanently delete this blood unit record?')) {
                    window.location.href = `delete-unit.php?id=${unitId}`;
                }
            });
        });
    </script>
</body>
</html>