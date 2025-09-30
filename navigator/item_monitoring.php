<?php

require 'config.php';

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Prevent caching to avoid displaying outdated data
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Fetch current profile picture
$sql = "SELECT profile_picture FROM signup_table WHERE id = '$user_id'";
$result = $conn->query($sql);
$profilePic = 'defaultprofile.png';
if ($result && $row = $result->fetch_assoc()) {
    if (!empty($row['profile_picture']) && file_exists($row['profile_picture'])) {
        $profilePic = $row['profile_picture'];
    }
}


// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT name AS name, tag_uid, status,  floor FROM equipment WHERE name LIKE ? ORDER BY created_at DESC");
    $likeSearch = "%$search%";
    $stmt->bind_param("s", $likeSearch);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT name AS name, tag_uid, status,  floor FROM equipment ORDER BY created_at DESC");
}

$rows = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Monitoring Equipment</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="usercssfolder/userdashboard.css">
    <style>
       
    main {
        position: relative;
        padding: 20px;
    }

    table {
        width: 100%; /* Use 100% for full width */
        border-collapse: collapse;
        margin: 20px auto;
    }

    th, td {
        padding: 10px;
        border: 1px solid #aaa;
        text-align: center;
    }

    th {
        background-color: #f0f0f0;
    }

    .returned {
        color: green;
        font-weight: bold;
    }

    .borrowed {
        color: red;
        font-weight: bold;
    }

    .search-container {
        position: relative; /* Change to relative for better positioning */
        margin: 10px 0; /* Add margin for spacing */
    }

    .search-input {
        padding: 8px;
        font-size: 16px;
        width: 100%; /* Make input full width */
        max-width: 250px; /* Limit max width */
        border: 1px solid #ccc;
        border-radius: 4px;
        background: transparent;
        color: #333;
    }

    .search-button {
        padding: 8px 12px;
        font-size: 16px;
        cursor: pointer;
        background-color: #1976d2;
        color: white;
        border: none;
        border-radius: 4px;
    }

    /* Media Queries for Responsiveness */
    @media (max-width: 768px) {
        .search-container {
            text-align: center; /* Center search on smaller screens */
        }

        table {
            font-size: 14px; /* Adjust font size for smaller screens */
        }

        th, td {
            padding: 8px; /* Reduce padding */
        }
    }

    @media (max-width: 480px) {
        .search-input {
            font-size: 14px; /* Smaller font size for mobile */
        }

        .search-button {
            width: 100%; /* Full width button on mobile */
        }

        .profile img {
            width: 40px; /* Smaller profile image */
            height: 40px;
        }
    }
</style>
</head>

<body>

<!-- SIDEBAR -->
<section id="sidebar" class="always-visible">
    <a href="dashboard.php" class="logo">
        <img src="logo.jpg" alt="logo"> <span class="brand"> Navigator </span>
    </a>
    <ul class="side-menu">
        <li><a href="dashboard.php"><i class='bx bxs-dashboard icon'></i> Dashboard</a></li>
        <li class="divider" data-text="main"> Main </li>
        <li>
            <a href="#"><i class='bx bx-news icon'></i> Manage Equipment<i class='bx bx-chevron-right icon-right'></i></a>
            <ul class="side-dropdown">
                <li><a href="add.php"><i class='bx bx-chevron-right icon'></i> Add Equipment</a></li>
                <li><a href="delete.php"><i class='bx bx-chevron-right icon'></i> Delete Equipment</a></li>
            </ul>
        </li>
        <li><a href="item_monitoring.php"class="active"><i class='bx bxs-report icon'></i> Monitoring Equipment </a></li>
        <li><a href="usermap.php"><i class='bx bx-map icon'></i> Map</a></li>
    </ul>
</section>

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
                <li><a href="userprofile.php"><i class='bx bxs-user-circle icon'></i> Profile</a></li>
                <li><a href="userchangeinfo.php"><i class='bx bxs-cog icon'></i> Settings</a></li>
                <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- MAIN -->
    <main>
        <h1 class="title"></h1>
        <ul class="breadcrumbs">
            <li><a href="dashboard.php">Home</a></li>
            <li class="divider">/</li>
            <li><a href="dashboard.php">Equipment Monitoring</a></li>
        </ul>

        <div class="search-container">
            <form method="GET" action="item_monitoring.php">
                <input type="text" name="search" placeholder="Search item name..." class="search-input" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="search-button">Search</button>
            </form>
        </div>

        <center>
            <h2>📋 Monitoring </h2>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>UID</th>
                        <th>Status</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)): ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['tag_uid']) ?></td>
                                <td class="<?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </td>
                                <td><?= htmlspecialchars($row['floor']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </center>

    </main>
</section>

<script src="dashscript2.js"></script>

</body>
</html>
