<?php
require 'config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['uploadProfilePicturee'])) {
    $targetDir = "uploads/userprofile_pictures/";
    $fileName = basename($_FILES["profilePicture"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        if (file_exists($targetFilePath)) {
            $targetFilePath = $targetDir . time() . '_' . $fileName;
        }

        if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $targetFilePath)) {
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
        echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.');</script>";
    }
}

$sql = "SELECT profile_picture FROM signup_table WHERE id = '$user_id'";
$result = $conn->query($sql);
$profilePic = 'defaultprofile.png';
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>School Map</title>
  <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="usercssfolder/usermap.css">
  <style>
    #map-container {
      width: 100%;
      max-width: 1536px;
      margin: 0 auto;
      overflow: hidden;
    }

    #map {
      width: 100%;
      aspect-ratio: 3 / 2;
      height: auto;
    }

    .leaflet-container {
      width: 100% !important;
      height: auto !important;
      aspect-ratio: 3 / 2;
    }

    @media (max-width: 600px) {
      #map-container {
        max-width: 100%;
      }
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
    <li><a href="usermap.php" class="active"><i class='bx bx-map icon'></i> Map</a></li>
  </ul>
</section>

<!-- MAIN CONTENT -->
<section id="content">
  <nav>
    <i class='bx bx-menu toggle-sidebar'></i>
    <form action="#"></form>
    <span class="divider"></span>
    <div class="profile">
      <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" style="width: 50px; height: 50px; object-fit: cover;">
      <ul class="profile-link">
        <li><a href="userprofile.php"><i class='bx bxs-user-circle icon'></i> Profile</a></li>
        <li><a href="userchangeinfo.php"><i class='bx bxs-cog icon'></i> Settings</a></li>
        <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
      </ul>
    </div>
  </nav>

  <main>
    <h1 class="title">Map</h1>
<ul class="breadcrumbs">
  <li><a href="dashboard.php">Home</a></li>
  <li class="divider">/</li>
  <li><a href="#" class="active">Map</a></li>
</ul>

<!-- Building and Floor Selector -->
<div style="margin: 15px 0;">
  <label for="building">Select Building:</label>
  <select id="building" required>
    <option value="">-- Choose Building --</option>
    <option value="shs">College Building</option>
    <option value="hs">High School Building</option>
  </select>

  <label for="floor" style="margin-left: 10px;">Select Floor:</label>
  <select id="floor" disabled required>
    <option value="">-- Choose Floor --</option>
  </select>
</div>

<script>
  const buildingSelect = document.getElementById('building');
  const floorSelect = document.getElementById('floor');

  buildingSelect.addEventListener('change', function () {
    floorSelect.innerHTML = '<option value="">-- Choose Floor --</option>'; 
    floorSelect.disabled = true; 

    if (this.value === 'shs') {
      floorSelect.disabled = false;
      floorSelect.innerHTML += '<option value="college_1stf.php">1st Floor</option>';
      floorSelect.innerHTML += '<option value="college_2ndf.php">2nd Floor</option>';
      floorSelect.innerHTML += '<option value="college_3rdf.php">3rd Floor</option>';
      floorSelect.innerHTML += '<option value="college_4thf.php">4th Floor</option>';
    } else if (this.value === 'hs') {
      floorSelect.disabled = false;
      floorSelect.innerHTML += '<option value="hs_1stf.php">1st Floor</option>';
      floorSelect.innerHTML += '<option value="hs_2ndf.php">2nd Floor</option>';
      floorSelect.innerHTML += '<option value="hs_3rdf.php">3rd Floor</option>';
      floorSelect.innerHTML += '<option value="hs_4thf.php">4th Floor</option>';
    }
  });

  floorSelect.addEventListener('change', function () {
    if (this.value) {
      window.location.href = this.value;
    }
  });
</script>

    <h3 class="mapoverview">Map Overview</h3>

    <div id="map-container">
      <div id="map"></div>
    </div>
</main>
</section>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
  var map = L.map('map', {
    crs: L.CRS.Simple,
    zoomControl: false,
    dragging: false,
    touchZoom: false,
    scrollWheelZoom: false,
    doubleClickZoom: false,
    boxZoom: false,
    keyboard: false,
    minZoom: -1,
    maxZoom: 0
  });

  var bounds = [[0, 0], [1024, 1536]];
  L.imageOverlay('college3rdf (1).png', bounds).addTo(map);
  map.setView([512, 768], -1); 

  // i-flip natin ang y coordinates (1024 - y)
  const roomAreas = {
    "Faculty Room": {
      topLeft: [1024 - 447, 21],
      bottomRight: [1024 - 332, 149]
    },
    "Room 3-12": {
      topLeft: [1024 - 446, 300],
      bottomRight: [1024 - 329, 595]
    },
    "Room 3-10": {
      topLeft: [1024 - 446, 598],
      bottomRight: [1024 - 329, 725]
    }
  };

  let markers = [];
  // draw rectangles para makita areas
  Object.keys(roomAreas).forEach(roomName => {
    const area = roomAreas[roomName];
    L.rectangle([area.topLeft, area.bottomRight], {
      color: "blue",
      weight: 1,
      fillOpacity: 0.1
    }).addTo(map);
  });

  function loadMarkers() {
    // clear old markers
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];

    // dynamic na groups base sa roomAreas
    const roomGroups = {};
    Object.keys(roomAreas).forEach(r => roomGroups[r] = []);

    fetch('get_item_locations.php')
      .then(response => response.json())
      .then(data => {
        console.log("Data received:", data);

        data.forEach(item => {
          let roomName = null;

          if (item.status && item.status.toLowerCase() === "returned") {
            roomName = "Faculty Room";
          } else if (item.floor) {
            let floor = item.floor.trim().toLowerCase();
            Object.keys(roomAreas).forEach(r => {
              if (r.toLowerCase() === floor) {
                roomName = r;
              }
            });
          }

          if (roomName && roomGroups[roomName]) {
            roomGroups[roomName].push(item);
          }
        });

        // place markers
        Object.keys(roomGroups).forEach(roomName => {
          const items = roomGroups[roomName];
          const area = roomAreas[roomName];

          if (items.length > 0 && area) {
            const yStart = area.topLeft[0];
            const xStart = area.topLeft[1];
            const yEnd = area.bottomRight[0];
            const xEnd = area.bottomRight[1];

            const rows = Math.ceil(Math.sqrt(items.length));
            const cols = rows;
            const yStep = (yEnd - yStart) / rows;
            const xStep = (xEnd - xStart) / cols;

            let i = 0;
            items.forEach(item => {
              const row = Math.floor(i / cols);
              const col = i % cols;
              const y = yStart + row * yStep + yStep / 2;
              const x = xStart + col * xStep + xStep / 2;
              i++;

              const marker = L.marker([y, x]).addTo(map)
                .bindPopup(`<b>${item.item_name}</b><br>${roomName}`);
              markers.push(marker);
            });
          }
        });
      })
      .catch(err => console.error("Error fetching data:", err));
  }

  loadMarkers();
  setInterval(loadMarkers, 1000);

  setTimeout(() => {
    map.invalidateSize();
  }, 500);
</script>


<script src="dashscript2.js"></script>
</body>
</html>
