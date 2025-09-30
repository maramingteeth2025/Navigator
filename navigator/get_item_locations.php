<?php
require 'config.php';

// Fetch borrowed and returned items from the equipment table
$sql = "SELECT tag_uid, name AS item_name, floor, status FROM equipment";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
?>
