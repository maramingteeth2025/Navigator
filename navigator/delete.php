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

$user_id = $_SESSION['user_id']; // FIX: assign user_id from session

// Prevent caching to avoid displaying outdated data
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");



// Fetch the current profile picture
$sql = "SELECT profile_picture FROM signup_table WHERE id = '$user_id'";
$result = $conn->query($sql);
$profilePic = 'defaultprofile.png'; // Default picture
if ($result && $row = $result->fetch_assoc()) {
    if (!empty($row['profile_picture']) && file_exists($row['profile_picture'])) {
        $profilePic = $row['profile_picture'];
    }
}



$result = $conn->query("SELECT * FROM equipment");



if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Get image path before deletion
    $stmt = $conn->prepare("SELECT image_path FROM equipment WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($image_path);
    $stmt->fetch();
    $stmt->close();

    // Delete record
    $stmt = $conn->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Delete the image file if exists
    if ($image_path && file_exists($image_path)) {
        unlink($image_path);
    }

    // ✅ Stop execution after redirect
    header("Location: delete.php");
    exit;

} else {
    echo "";
}



?>

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

// Fetch the current profile picture
$sql = "SELECT profile_picture FROM signup_table WHERE id = '$user_id'";
$resultPic = $conn->query($sql);
$profilePic = 'defaultprofile.png'; // Default picture
if ($resultPic && $row = $resultPic->fetch_assoc()) {
    if (!empty($row['profile_picture']) && file_exists($row['profile_picture'])) {
        $profilePic = $row['profile_picture'];
    }
}

// SEARCH HANDLER
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT * FROM equipment WHERE name LIKE ?");
    $like = "%" . $search . "%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM equipment");
}

// DELETE HANDLER
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Get image path before deletion
    $stmt = $conn->prepare("SELECT image_path FROM equipment WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($image_path);
    $stmt->fetch();
    $stmt->close();

    // Delete record
    $stmt = $conn->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Delete the image file if exists
    if ($image_path && file_exists($image_path)) {
        unlink($image_path);
    }

    header("Location: delete.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="usercssfolder/userprofile.css">
  <link rel="stylesheet" href="usercssfolder/userdashboard.css">
  <title> Delete Equipment </title>
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    table, th, td {
      border: 1px solid #ccc;
    }

    th, td {
      padding: 10px;
      text-align: left;
    }

    img {
      border-radius: 5px;
    }

    a.delete-link {
      color: red;
      text-decoration: none;
    }

    a.delete-link:hover {
      text-decoration: underline;
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
          <li><a href="add.php"><i class='bx bx-chevron-right icon'></i> Add Equipment</a></li>
          <li><a href="delete.php"><i class='bx bx-chevron-right icon'></i> Delete Equipment</a></li>
        </ul>
      </li>
      <li><a href="item_monitoring.php"><i class='bx bxs-report icon'></i> Monitoring Equipment</a></li>
      <li><a href="usermap.php"><i class='bx bx-map icon'></i> Map</a></li>
    </ul>
  </section>
  <!-- SIDEBAR -->

  <!-- CONTENT -->
  <section id="content">
    <nav>
      <i class='bx bx-menu toggle-sidebar'></i>
      <form action="#"></form>

      <span class="divider"></span>

      <div class="profile">
        <img src="<?php echo htmlspecialchars($profilePic ?? 'default.jpg'); ?>" alt="Profile Picture"
          style="width: 50px; height: 50px; object-fit: cover;">
        <ul class="profile-link">
          <li><a href="userprofile.php"><i class='bx bxs-user-circle icon'></i> Profile</a></li>
          <li><a href="userchangeinfo.php"><i class='bx bxs-cog icon'></i> Settings</a></li>
          <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
        </ul>
      </div>
    </nav>

    <main>
      <h1 class="title">📋 Equipment List</h1>
      <ul class="breadcrumbs">
        <li><a href="dashboard.php">Home</a></li>
        <li class="divider">/</li>
        <li><a href="#" class="active">Equipment List</a></li>
      </ul>

      <!-- 🔍 Search Form -->
      <form method="GET" action="delete.php" style="margin-bottom: 20px;">
        <input type="text" name="search" placeholder="Search equipment name..." value="<?= htmlspecialchars($search) ?>" style="padding: 8px; width: 300px;">
        <input type="submit" value="Search" style="padding: 8px 15px;">
      </form>

      <table>
        <thead>
          <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Details</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td>
                  <?php if (!empty($row['image_path'])): ?>
                    <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="Item Image" width="80">
                  <?php else: ?>
                    No Image
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['details'])) ?></td>
                <td>
                  <a class="delete-link" href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                </td>
              </tr>
            <?php endwhile; ?>
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
