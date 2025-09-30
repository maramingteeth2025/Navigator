<?php
require 'config.php';

// Get and sanitize POST data
$tag_uid = trim($_POST['tag_uid'] ?? '');
$scanner_location = trim($_POST['scanner_location'] ?? '');

if (empty($tag_uid) || empty($scanner_location)) {
    echo "Missing tag UID or scanner location.";
    exit;
}

// Check if tag exists in the equipment table
$stmt = $conn->prepare("SELECT * FROM equipment WHERE tag_uid = ?");
$stmt->bind_param("s", $tag_uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Tag not found in equipment table.";
    exit;
}

$equipment = $result->fetch_assoc();
$action = ($scanner_location === 'return') ? 'returned' : 'borrowed';
$name = $equipment['name']; // Optional, for future use
$room = $scanner_location;

// Function to insert notification
function insertNotification($conn, $tag_uid, $action, $room) {
    $insert = $conn->prepare("INSERT INTO notifications (tag_uid, action, room) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $tag_uid, $action, $room);
    if (!$insert->execute()) {
        error_log("Error inserting notification: " . $insert->error);
    }
}

// Logic based on scanner location
if ($scanner_location === 'return') {
    if ($equipment['status'] === 'returned') {
        insertNotification($conn, $tag_uid, $action, $room); // still insert notification
        echo "ℹ️ Item is already returned. Notification still saved.";
        exit;
    }

    // Update status to returned and clear floor
    $update = $conn->prepare("UPDATE equipment SET status = 'returned', floor = NULL WHERE tag_uid = ?");
    $update->bind_param("s", $tag_uid);
    if (!$update->execute()) {
        echo "Error updating item: " . $update->error;
        exit;
    }

    insertNotification($conn, $tag_uid, $action, $room);
    echo "✅ Item successfully returned.";

} elseif ($scanner_location === 'room 3-12' || $scanner_location === 'room 3-10') {
    // Update status and assign floor (even if already borrowed)
    $update = $conn->prepare("UPDATE equipment SET status = 'borrowed', floor = ? WHERE tag_uid = ?");
    $update->bind_param("ss", $scanner_location, $tag_uid);
    if (!$update->execute()) {
        echo "Error updating item: " . $update->error;
        exit;
    }

    insertNotification($conn, $tag_uid, $action, $room);

    if ($equipment['status'] === 'borrowed') {
        echo "ℹ️ Item is already borrowed. Floor updated to $scanner_location.";
    } else {
        echo "✅ Item marked as borrowed in $scanner_location.";
    }

} else {
    echo "Invalid scanner location.";
}
?>
