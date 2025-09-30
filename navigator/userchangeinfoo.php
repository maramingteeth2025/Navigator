<?php
require 'config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$user_id = $_SESSION['user_id'];

// Fetch current user data (only email now)
$sql = "SELECT email FROM signup_table WHERE id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_email = $row['email'];
} else {
    echo "User not found.";
    exit;
}

// Handle password update
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (!empty($password)) {
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match');</script>";
    } elseif (strlen($password) < 6) {
        echo "<script>alert('Password must be at least 6 characters long.');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $update_password_sql = "UPDATE signup_table SET password='$hashed_password' WHERE id='$user_id'";
        if ($conn->query($update_password_sql) === TRUE) {
            echo "<script>alert('Password updated successfully!');</script>";
        } else {
            echo "<script>alert('Error updating password: " . $conn->error . "');</script>";
        }
    }
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
    <link rel="stylesheet" href="usercssfolder/userupdateprofile.css">
    <link rel="stylesheet" href="usercssfolder/userdashboard.css">
    <title>Settings</title>
</head>

<body>

    <!-- SIDEBAR -->
    <section id="sidebar" class="always-visible">
        <a href="dashboardd.php" class="logo">
            <img src="logo.jpg" alt="logo"> <span class="brand"> Navigator </span>
        </a>

        <ul class="side-menu">
            <li><a href="dashboardd.php"><i class='bx bxs-dashboard icon'></i> Dashboard</a></li>
            <li class="divider" data-text="main"> Main </li>
            <li>
                <a href="#"><i class='bx bx-news icon'></i> Manage Equipment <i
                        class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                    <li><a href="user_request.php "><i class='bx bx-chevron-right icon'></i> Equipment Request</a></li>
                    <li><a href="user_history.php "><i class='bx bx-chevron-right icon'></i> History Request</a>
                    </li>
                </ul>
            </li>
            <li><a href="item_monitoringg.php"><i class='bx bxs-report icon'></i> Monitoring Equipment </a></li>
            
           
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
            <h1 class="title">Settings</h1>
            <ul class="breadcrumbs">
                <li><a href="dashboardd.php">Home</a></li>
                <li class="divider">/</li>
                <li><a href="dashboardd.php" class="active">Settings</a></li>
            </ul>

            <center>

<h2>Profile Settings</h2>
<div class="container">

    <!-- Profile Picture Upload -->
    <div class="profile-pic-update">
        <form action="dashboardd.php" method="post" enctype="multipart/form-data" style="margin: 20px;">
            <label for="profilePicture">Upload Profile Picture</label>
            <input type="file" name="profilePicture" id="profilePicture" required>
            <button type="submit" name="uploadProfilePicturee">Upload</button><br>
        </form>
    </div>

    <!-- Password Update Form -->
    <div class="profile-name-update">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validatePasswordForm();">
            <label for="password">Set A New Password</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>

            <button type="submit">Update</button>
        </form>
    </div>
</div>

<!-- JavaScript validation -->
<script>
function validatePasswordForm() {
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();

    if (password.length < 6) {
        alert('Password must be at least 6 characters long.');
        return false;
    }

    if (password !== confirmPassword) {
        alert('Passwords do not match.');
        return false;
    }

    return true; // Allow form submission
}
</script>

            </center>
        </main>
        <!-- MAIN -->
    </section>
    <!-- NAVBAR -->

    <script src="dashscript2.js"></script>
    
    </script>

</body>

</body>

</html>
