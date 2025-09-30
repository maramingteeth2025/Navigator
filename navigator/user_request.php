<?php
session_start();
require 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id']; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item = trim($_POST['item']);
    $room = trim($_POST['room']);
    $details = trim($_POST['details']);

    $stmt = $conn->prepare("INSERT INTO user_requests (user_id, item, room, details, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isss", $user_id, $item, $room, $details);

    if ($stmt->execute()) {
        echo "<script>alert('Request sent successfully!'); window.location='user_request.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}

// Fetch the current profile picture securely
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
$stmtProfile->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="usercssfolder/userdashboard.css">
<title>Request Item</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .container {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .profile-pic {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }

        .profile-pic img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3498db;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
            color: #444;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
            transition: border 0.3s ease;
        }

        input[type="text"]:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #3498db;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #2980b9;
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
                    <li><a href="user_history.php "><i class='bx bx-chevron-right icon'></i>History Request </a>
                    </li>
                </ul>
            <li><a href="item_monitoringg.php"><i class='bx bxs-report icon'></i> Monitoring Equipment </a></li>
      
            
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
                    <li><a href="userprofilee.php"><i class='bx bxs-user-circle icon'></i> Profile</a></li>
                    <li><a href="userchangeinfoo.php"><i class='bx bxs-cog icon'></i> Settings</a></li>
                    <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
                </ul>
            </div>


        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
         <main>
            <h1 class="title"> </h1>
            <ul class="breadcrumbs">
                <li><a href="dashboardd.php">Home</a></li>
                <li class="divider">/</li>
                <li><a href="dashboardd.php" class="active">Equipment Request</a></li>
            </ul>

            
<div class="container">
    <!-- Profile Picture Display -->
    <div class="profile-pic">
        <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture">
    </div>

    <h2>Request an Item</h2>
    <form method="POST" action="">
        <label>Item: <span style="color:red"></span></label>
        <input type="text" name="item" required>

        <label>Room: <span style="color:red"></span></label>
        <input type="text" name="room" required>

        <label>Details:</label>
        <textarea name="details" rows="4"></textarea>

        <button type="submit">Send Request</button>
    </form>
</div>


        </main>
        <!-- MAIN -->
    </section>

    <!-- NAVBAR -->


    <script src="dashscript2.js"></script>
    
</body>

</html>