<?php
require 'config.php';

// Check if a session is already started, if not, start a session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Function to sanitize output (prevent XSS)
$user_id = $_SESSION['user_id'];
function sanitize_output($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Fetch the current profile picture
$sql = "SELECT profile_picture FROM signup_table WHERE id = '$user_id'";
$result = $conn->query($sql);
$profilePic = 'uploads/userprofile_pictures/defaultprofile.png'; // Default picture path
if ($result && $row = $result->fetch_assoc()) {
    if (!empty($row['profile_picture']) && file_exists($row['profile_picture'])) {
        $profilePic = $row['profile_picture'];
    }
}







// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $deleteStmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
    $deleteStmt->bind_param("i", $delete_id);
    $deleteStmt->execute();
}

// Fetch notifications
$sql = "SELECT n.*, e.name AS equipment_name, e.floor
        FROM notifications n
        LEFT JOIN equipment e ON n.tag_uid = e.tag_uid
        ORDER BY n.created_at DESC";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="usercssfolder/userdashboard.css">
    <title> Notification</title>
</head>

<body>

    <!-- SIDEBAR -->
    <section id="sidebar" class="always-visible">
        <a href="dashboard.php" class="logo">
            <img src="logo.jpg" alt="logo"> <span class="brand"> Navigator </span></a>

        <ul class="side-menu">
            <li><a href="dashboard.php" class="active"><i class='bx bxs-dashboard icon'></i> Dashboard</a></li>
            <li class="divider" data-text="main"> Main </li>
            <li>
                <a href="#"><i class='bx bx-news icon'></i>  Manage Equipment <i
                        class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                    <li><a href="add.php "><i class='bx bx-chevron-right icon'></i> Add Equipment</a></li>
                    <li><a href="delete.php "><i class='bx bx-chevron-right icon'></i> Delete Equipment </a>
                    </li>
                </ul>
            <li><a href="item_monitoring.php"><i class='bx bxs-report icon'></i> Monitoring Equipment </a></li>
            <li><a href="usermap.php"><i class='bx bx-map icon'></i> Map </a></li>
            
            </li>

        </ul>

    </section>
    <!-- SIDEBAR -->

    <!-- NAVBAR -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <form action="#">
            </form>

           
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
        <!-- NAVBAR -->

        <!-- MAIN -->
         <main>
            <h1 class="title">Notification</h1>
            <ul class="breadcrumbs">
                <li><a href="dashboard.php">Home</a></li>
                <li class="divider">/</li>
                <li><a href="dashboard.php" class="active"> Notification</a></li>
            </ul>



<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <style>
        body {
            font-family: Arial;
        }
        .notif-box {
            width: 80%;
            margin: 20px auto;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
            position: relative;
        }
        .notif-box h3 {
            margin: 0 0 10px 0;
        }
        .timestamp {
            color: #888;
            font-size: 0.9em;
        }
        .delete-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ff4d4d;
            color: white;
            border: none;
            padding: 5px 8px;
            font-size: 0.8em;
            cursor: pointer;
            border-radius: 5px;
        }
        .delete-btn:hover {
            background: #e60000;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">Notifications</h2>

<?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="notif-box">
            <form method="post" style="display:inline;">
                <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                <button type="submit" class="delete-btn" onclick="return confirm('Delete this notification?')">Delete</button>
            </form>
            <h3>
                <?= htmlspecialchars($row['equipment_name']) ?>
                is being
                <?= htmlspecialchars($row['action']) ?>
                <?= $row['action'] === 'borrowed' ? 'in ' . htmlspecialchars($row['room']) : '' ?>
            </h3>
            <div class="timestamp">
                at <?= date("F j, Y, g:i a", strtotime($row['created_at'])) ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="text-align:center;">No notifications yet.</p>
<?php endif; ?>

</body>
</html>

        </main>
        <!-- MAIN -->
    </section>

    <!-- NAVBAR -->


    <script src="dashscript2.js"></script>
    
</body>

</html>