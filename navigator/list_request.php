<?php
require 'config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Accept / Decline
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];

    if (in_array($status, ['accepted', 'declined'])) {
        $stmt = $conn->prepare("UPDATE user_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }

    header("Location: list_request.php");
    exit();
}

// Soft delete (mark as read=1)
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE user_requests SET `read` = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: list_request.php");
    exit();
}

// Show all read=0 plus earliest read=1
$sqlRequests = "
    (SELECT * FROM user_requests WHERE `read` = 0)
    UNION
    (SELECT * FROM user_requests WHERE `read` = 1 AND created_at < NOW() - INTERVAL 30 DAY ORDER BY created_at ASC LIMIT 5)
    ORDER BY created_at DESC
";

$resultRequests = $conn->query($sqlRequests);

// Profile picture
$sqlProfile = "SELECT profile_picture FROM signup_table WHERE id = '$user_id'";
$resultProfile = $conn->query($sqlProfile);
$profilePic = 'defaultprofile.png';
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
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .table-container {
            overflow-x: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            white-space: nowrap;
        }
        th {
            background-color: #007bff;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e6f0ff;
        }
        a {
            padding: 6px 12px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }
        .accept-btn {
            background-color: #28a745;
        }
        .accept-btn:hover {
            background-color: #218838;
        }
        .decline-btn {
            background-color: #dc3545;
        }
        .decline-btn:hover {
            background-color: #c82333;
        }
        .delete-btn {
            background-color: #6c757d;
        }
        .delete-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<section id="sidebar" class="always-visible">
  <a href="dashboard.php" class="logo">
    <img src="logo.jpg" alt="logo"> <span class="brand"> Navigator</span>
  </a>
  <ul class="side-menu">
    <li><a href="dashboard.php"><i class='bx bxs-dashboard icon'></i> Dashboard</a></li>
    <li class="divider" data-text="main"> Main </li>
    <li>
      <a href="#"><i class='bx bx-news icon'></i> Manage Equipment <i class='bx bx-chevron-right icon-right'></i></a>
      <ul class="side-dropdown">
        <li><a href="add.php"><i class='bx bx-chevron-right icon'></i>Add Equipment</a></li>
        <li><a href="delete.php"><i class='bx bx-chevron-right icon'></i>Delete Equipment</a></li>
      </ul>
    </li>
    <li><a href="item_monitoring.php"><i class='bx bxs-report icon'></i> Monitoring Equipment</a></li>
    <li><a href="usermap.php"><i class='bx bx-map icon'></i> Map</a></li>
  </ul>
</section>

<section id="content">
    <nav>
        <i class='bx bx-menu toggle-sidebar'></i>
        <form action="#"></form>
        <span class="divider"></span>
        <div class="profile">
            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture"
                style="width: 50px; height: 50px; object-fit: cover;">
            <ul class="profile-link">
                <li><a href="userprofile.php"><i class='bx bxs-user-circle icon'></i> Profile</a></li>
                <li><a href="userchangeinfo.php"><i class='bx bxs-cog icon'></i> Settings</a></li>
                <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <main>
        <h1 class="title"></h1>
        <ul class="breadcrumbs">
            <li><a href="dashboard.php">Home</a></li>
            <li class="divider">/</li>
            <li><a href="dashboard.php" class="active">List of Requests</a></li>
        </ul>

        <h2>List of Requests</h2>
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Item</th>
                    <th>Room</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
                <?php while($row = $resultRequests->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['item']) ?></td>
                    <td><?= htmlspecialchars($row['room']) ?></td>
                    <td><?= htmlspecialchars($row['details']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
    <a class="accept-btn" 
       href="?id=<?= $row['id'] ?>&status=accepted" 
       onclick="return confirm('Are you sure you want to accept this request?')">
       Accept
    </a>

    <a class="decline-btn" 
       href="?id=<?= $row['id'] ?>&status=declined" 
       onclick="return confirm('Are you sure you want to decline this request?')">
       Decline
    </a>

    <a class="delete-btn" 
       href="?id=<?= $row['id'] ?>&action=delete" 
       onclick="return confirm('Mark this request as deleted?')">
       Delete
    </a>
</td>

                </tr>
                <?php } ?>
            </table>
        </div>
    </main>
</section>

<script src="dashscript2.js"></script>
</body>
</html>
