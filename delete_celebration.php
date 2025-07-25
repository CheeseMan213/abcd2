<?php
require 'db_configuration.php'; // DB connection

if (!isset($_GET['id'])) {
    die("No celebration ID provided.");
}

$id = intval($_GET['id']);

// Step 1: Get the image filename (if any) to delete the image file
$query = "SELECT img_url FROM celebrations_tbl WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($img_url);
$stmt->fetch();
$stmt->close();

// Step 2: Delete from celebration_tags_tbl
$deleteTagsQuery = "DELETE FROM celebration_tags_tbl WHERE celebration_id = ?";
$deleteTagsStmt = $db->prepare($deleteTagsQuery);
$deleteTagsStmt->bind_param("i", $id);
$deleteTagsStmt->execute();
$deleteTagsStmt->close();

// Step 3: Delete the database entry
$deleteQuery = "DELETE FROM celebrations_tbl WHERE id = ?";
$deleteStmt = $db->prepare($deleteQuery);
$deleteStmt->bind_param("i", $id);

if ($deleteStmt->execute()) {
    // Step 4: Remove image file if it exists
    if (!empty($img_url)) {
        $imagePath = 'images/celebration_images/' . $img_url;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    $deleteStmt->close();
    header("Location: admin_celebrations.php"); // Redirect after deletion
    exit();
} else {
    die("Error deleting celebration: " . $db->error);
}
?>