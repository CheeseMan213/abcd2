<?php
// tag_frequency.php
session_start();
require 'db_configuration.php';
include('header.php');

// Make sure only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<p>You do not have permission to view this page.</p>";
    include('footer.php');
    exit;
}

// STEP 1: Load tags from text file
$tag_file = 'abcd_tags.txt';
$tags = file($tag_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// STEP 2: Ensure all static tags exist in DB
foreach ($tags as $tag) {
    $tag = trim($tag);
    if (!empty($tag)) {
        $stmt = $db->prepare("INSERT IGNORE INTO tags (tag_name) VALUES (?)");
        $stmt->bind_param("s", $tag);
        $stmt->execute();
        $stmt->close();
    }
}

// STEP 3: Fetch counts of celebrations per tag
$sql = "
    SELECT t.tag_name, COUNT(ct.celebration_id) AS tag_count
    FROM tags t
    LEFT JOIN celebration_tags_tbl ct ON t.id = ct.tag_id
    GROUP BY t.tag_name
";
$result = mysqli_query($db, $sql);

$db_counts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $db_counts[$row['tag_name']] = $row['tag_count'];
}

echo "<div style='margin-top: 60px;'></div>"; 
// STEP 4: Display Tag Frequency Report
echo "<h2>Celebration Tag Frequency Report</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Tag</th><th>Number of Celebrations</th></tr>";

foreach ($tags as $tag) {
    $tag = trim($tag);
    $count = isset($db_counts[$tag]) ? $db_counts[$tag] : 0;
    echo "<tr><td>{$tag}</td><td>{$count}</td></tr>";
}

echo "</table>";

include('footer.php');
?>