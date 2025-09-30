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

$sql = "SELECT profile_picture FROM signup_table WHERE id = '$user_id'";
$result = $conn->query($sql);
$profilePic = 'defaultprofile.png';
if ($result && $row = $result->fetch_assoc()) {
    if (!empty($row['profile_picture']) && file_exists($row['profile_picture'])) {
        $profilePic = $row['profile_picture'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $details = $_POST['details'] ?? '';
    $tag_uid = $_POST['tag_uid'] ?? '';
    $status = 'available'; // Set default status to 'available'
    $floor = ''; // You may want to set this based on your logic
    $image_path = '';

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);

        $filename = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . time() . "_" . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Insert the item into the database
    $stmt = $conn->prepare("INSERT INTO equipment (name, details, image_path, tag_uid, floor, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $details, $image_path, $tag_uid, $floor, $status);
    $stmt->execute();

    echo "<script>alert('✅ Equipment added successfully.'); window.location.href='add.php';</script>";
    exit;
}

$search = $_GET['search'] ?? '';
$query = "SELECT name, details, tag_uid, image_path FROM equipment WHERE name LIKE ? OR tag_uid LIKE ? ORDER BY id DESC";
$stmt = $conn->prepare($query);
$search_param = "%$search%";
$stmt->bind_param("ss", $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Equipment</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="usercssfolder/userprofile.css">
    <link rel="stylesheet" href="usercssfolder/userdashboard.css">

    <style>
        form {
            width: 100%;
            max-width: 500px;
            margin: 30px auto;
            padding: 20px;
            border: 1px solid #ccc;
            background: #fff;
            border-radius: 10px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }

        input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }

        input[type="submit"] {
            margin-top: 20px;
            padding: 10px 20px;
            background: #1565c0;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background: #0d47a1;
        }

        .search-bar {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            margin: 10px 40px 0 auto;
        }

        .search-bar form {
            display: flex;
            align-items: center;
            background: none;
            border: 1px solid #ccc;
            padding: 5px 10px;
            border-radius: 8px;
        }

        .search-bar input[type="text"] {
            border: none;
            background: transparent;
            outline: none;
            padding: 5px;
            font-size: 14px;
            color: #333;
        }

        .search-bar button {
            background: none;
            border: none;
            cursor: pointer;
            color: #1565c0;
            font-size: 18px;
            padding: 5px;
        }

        h1.table-title {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 24px;
            color: #333;
        }

        table {
            width: 95%;
            margin: 0 auto 30px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
    </style>
</head>
<body>
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
          <li><a href="add.php"><i class='bx bx-chevron-right icon'></i> Add Equipment</a></li>
          <li><a href="delete.php"><i class='bx bx-chevron-right icon'></i> Delete Equipment</a></li>
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
                <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" style="width: 50px; height: 50px; object-fit: cover;">
                <ul class="profile-link">
                    <li><a href="userprofile.php"><i class='bx bxs-user-circle icon'></i> Profile</a></li>
                    <li><a href="userchangeinfo.php"><i class='bx bxs-cog icon'></i> Settings</a></li>
                    <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
                </ul>
            </div>
        </nav>

        <main>
            <h1 class="title">Add Equipment</h1>
            <ul class="breadcrumbs">
                <li><a href="dashboard.php">Home</a></li>
                <li class="divider">/</li>
                <li><a href="#" class="active">Add Equipment</a></li>
            </ul>

            <form action="add.php" method="POST" enctype="multipart/form-data">
                <label>Item Name:</label>
                <input type="text" name="name" required>

                <label>Item Details:</label>
                <textarea name="details" rows="4" required></textarea>

                <label>RFID Tag UID:</label>
                <input type="text" name="tag_uid" required>

                <label>Upload Image:</label>
                <input type="file" name="image" accept="image/*" required>

                <input type="submit" value="Add Equipment">
            </form>

            <div class="search-bar">
                <form method="GET" action="add.php">
                    <input type="text" name="search" placeholder="Search equipment name..." value="<?= htmlspecialchars($search ?? '') ?>">
                    <button type="submit"><i class='bx bx-search'></i></button>
                </form>
            </div>

            <h1 class="table-title">List of Equipment</h1>

            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Details</th>
                        <th>Tag UID</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)): ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['details']) ?></td>
                                <td><?= htmlspecialchars($row['tag_uid']) ?></td>
                                <td>
                                    <?php if (!empty($row['image_path'])): ?>
                                        <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="Item Image" style="width: 60px; height: 60px; object-fit: cover;">
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No equipment found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </section>

    <script src="dashscript2.js"></script>
</body>
</html>
