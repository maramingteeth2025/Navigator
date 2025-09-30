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

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['uploadProfilePicturee'])) {
    $targetDir = "uploads/userprofile_pictures/";
    $fileName = basename($_FILES["profilePicture"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Check if the directory exists, if not, create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Allow certain file formats
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
    if (in_array($fileType, $allowedTypes)) {
        // Check if the file already exists and if so, rename it
        if (file_exists($targetFilePath)) {
            $targetFilePath = $targetDir . time() . '_' . $fileName;
        }
        // Upload the file to the server
        if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $targetFilePath)) {
            // Update the user's profile picture in the database
            $filePathToSave = $conn->real_escape_string($targetFilePath);
            $sql = "UPDATE signup_table SET profile_picture = '$filePathToSave' WHERE id = '$user_id'";

            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Profile picture uploaded successfully.');</script>";
            } else {
                echo "<script>alert('Error: " . $conn->error . "');</script>";
            }
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
        }
    } else {
        echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
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
    <link rel="stylesheet" href="usercssfolder/userdashboard.css">
    <title> Dashboard</title>
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
            <h1 class="title">Dashboard</h1>
            <ul class="breadcrumbs">
                <li><a href="dashboard.php">Home</a></li>
                <li class="divider">/</li>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
            </ul>

          

            <div class="data">
                <div class="content-data">
                    <div class="head">
                        <a href="notifications.php">
                            <h2>Notification</h2>
                        </a>

                    </div>
                </div>
            </div>


  
            <div class="data">
                <div class="content-data">
                    <div class="head">
                        <a href="list_request.php">
                            <h2>Equipment Request List</h2>
                        </a>

                    </div>
                </div>
            </div>



        </main>
        <!-- MAIN -->
    </section>

    <!-- NAVBAR -->


    <script src="dashscript2.js"></script>
    
</body>

</html>