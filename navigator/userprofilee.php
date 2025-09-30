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

// Prevent caching to avoid displaying outdated data
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT  email, profile_picture FROM signup_table WHERE id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $email = $row['email'];
   
    $profilePic = $row['profile_picture'];
} else {
    echo "User not found.";
    exit;
}

// Fetch the current profile picture
$sql = "SELECT profile_picture FROM signup_table WHERE id = '$user_id'";
$result = $conn->query($sql);
$profilePic = 'defaultprofile.png'; // Default picture
if ($result && $row = $result->fetch_assoc()) {
    if (!empty($row['profile_picture']) && file_exists($row['profile_picture'])) {
        $profilePic = $row['profile_picture'];
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="usercssfolder/userprofile.css">
    <link rel="stylesheet" href="usercssfolder/userdashboard.css">
    <title>Profile</title>
</head>

<body>

    <!-- SIDEBAR -->
    <section id="sidebar" class="always-visible">
        <a href="dashboardd.php" class="logo">
            <img src="logo.jpg" alt="logo"> <span class="brand"> Navigator</span>
        </a>

        <ul class="side-menu">
            <li><a href="dashboardd.php"><i class='bx bxs-dashboard icon'></i> Dashboard</a></li>
            <li class="divider" data-text="main"> Main </li>
            <li>
                <a href="#"><i class='bx bx-news icon'></i> Manage Equipment <i
                        class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                    <li><a href="user_request.php "><i class='bx bx-chevron-right icon'></i> Equipment Request </a></li>
                    <li><a href="user_history.php "><i class='bx bx-chevron-right icon'></i> History Request </a>
                    </li>
                </ul>
            </li>
            <li><a href="item_monitoringg.php"><i class='bx bxs-report icon'></i> Monitoring Equipment</a></li>
            
            <
        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- NAVBAR -->
    <section id="content">
        <!-- NAVBAR -->
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
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <h1 class="title">Profile</h1>
            <ul class="breadcrumbs">
                <li><a href="dashboardd.php">Home</a></li>
                <li class="divider">/</li>
                <li><a href="dashboardd.php" class="active">Profile</a></li>
            </ul>
            <center>
                <div class="profile-container">
                    <div class="profile-header">
                        <h1>Profile</h1>
                    </div>

                    <div class="profile-content">
                        <div class="profile-picture">
                            <img src="<?php echo htmlspecialchars($profilePic); ?>">
                        </div>

                        <label>Profile Picture </label>

                        <div class="profile-details">
                          <br>
                          <br>
                            <div class="form-group">
                                <label for="email">Username</label>
                                <input type="text" id="first-name" placeholder="Email Address" readonly value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                           
                        </div>
                    </div>
                </div>
            </center>
        </main>
        <!-- MAIN -->
    </section>
    <!-- NAVBAR -->

    <script src="dashscript2.js"></script>
    

</body>

</html>