<?php
session_start();
require 'config.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Hard delete if delete_id is set
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sqlDelete = "DELETE FROM user_requests WHERE id = ? AND user_id = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("ii", $delete_id, $user_id);
    $stmtDelete->execute();
    $stmtDelete->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch requests for logged in user
$sqlRequests = "SELECT id, item, status, created_at 
                FROM user_requests 
                WHERE user_id = ?
                ORDER BY created_at DESC";
$stmtRequests = $conn->prepare($sqlRequests);
$stmtRequests->bind_param("i", $user_id);
$stmtRequests->execute();
$resultRequests = $stmtRequests->get_result();

// Fetch the current profile picture
$sqlProfile = "SELECT profile_picture FROM signup_table WHERE id = ?";
$stmtProfile = $conn->prepare($sqlProfile);
$stmtProfile->bind_param("i", $user_id);
$stmtProfile->execute();
$resultProfile = $stmtProfile->get_result();

$profilePic = 'defaultprofile.png'; // Default picture
if ($resultProfile && $rowProfile = $resultProfile->fetch_assoc()) {
    if (!empty($rowProfile['profile_picture']) && file_exists($rowProfile['profile_picture'])) {
        $profilePic = $rowProfile['profile_picture'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="usercssfolder/userdashboard.css">
    <title>My Request History</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .delete-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 5px;
        }
        .delete-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<section id="sidebar" class="always-visible">
    <a href="dashboardd.php" class="logo">
        <img src="logo.jpg" alt="logo"> <span class="brand"> Navigator </span></a>

    <ul class="side-menu">
        <li><a href="dashboardd.php"><i class='bx bxs-dashboard icon'></i> Dashboard</a></li>
        <li class="divider" data-text="main"> Main </li>
        <li>
            <a href="#"><i class='bx bx-news icon'></i>  Manage Equipment <i
                    class='bx bx-chevron-right icon-right'></i></a>
            <ul class="side-dropdown">
                <li><a href="user_request.php "><i class='bx bx-chevron-right icon'></i>Equipment Request</a></li>
                <li><a href="user_history.php "><i class='bx bx-chevron-right icon'></i>History Request </a></li>
            </ul>
        <li><a href="item_monitoringg.php"><i class='bx bxs-report icon'></i> Monitoring Equipment </a></li>
    </ul>
</section>
<!-- SIDEBAR -->

<!-- NAVBAR -->
<section id="content">
    <nav>
        <i class='bx bx-menu toggle-sidebar'></i>
        <form action="#"></form>
        <span class="divider"></span>
        <div class="profile">
            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture"
                style="width: 50px; height: 50px; object-fit: cover;">
            <ul class="profile-link">
                <li><a href="userprofilee.php"><i class='bx bxs-user-circle icon'></i> Profile</a></li>
                <li><a href="userchangeinfoo.php"><i class='bx bxs-cog icon'></i> Settings</a></li>
                <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- MAIN -->
    <main>
        <h1 class="title"></h1>
        <ul class="breadcrumbs">
            <li><a href="dashboardd.php">Home</a></li>
            <li class="divider">/</li>
            <li><a href="dashboardd.php" class="active">History Request</a></li>
        </ul>

        <table>
            <tr>
                <th>Item</th>
                <th>Status</th>
                <th>Date Requested</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $resultRequests->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['item']); ?></td>
                    <td>
                        <?php 
                            if ($row['status'] == 'accepted') {
                                echo "<span style='color:green;'>Accepted</span>";
                            } elseif ($row['status'] == 'declined') {
                                echo "<span style='color:red;'>Declined</span>";
                            } else {
                                echo "<span style='color:orange;'>Pending</span>";
                            }
                        ?>
                    </td>
                    <td><?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></td>
                    <td>
                        <a href="?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this request? if yes, it will delete your request also.')">
                            <button class="delete-btn">Delete</button>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </main>
</section>

<script src="dashscript2.js"></script>
</body>
</html>
