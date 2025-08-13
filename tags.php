<?php
require 'db_configuration.php';
include('header.php');

// Load static tags
$tags = file('abcd_tags.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Handle tag assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dress_id = $_POST['dress_id'];
    $selected_tags = $_POST['tags'] ?? [];

    // Clear previous tags
    $stmt = $db->prepare("DELETE FROM dresses_tags_tbl WHERE dress_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $dress_id);
        $stmt->execute();
        $stmt->close();
    }

    // Insert new tags
    $stmt = $db->prepare("INSERT INTO dresses_tags_tbl (dress_id, tag) VALUES (?, ?)");
    if ($stmt) {
        foreach ($selected_tags as $tag) {
            $stmt->bind_param("is", $dress_id, $tag);
            $stmt->execute();
        }
        $stmt->close();
    }
}

// Get list of dresses
$dresses = $db->query("SELECT id, name FROM dresses");

// Generate tag frequency report
$report_query = "SELECT tag, COUNT(*) as count FROM dresses_tags_tbl GROUP BY tag ORDER BY count DESC";
$report = $db->query($report_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tag Management</title>
    <style>
        .tag-box {
            margin-bottom: 1rem;
            padding: 1rem;
            border: 1px solid #ccc;
            border-radius: .5rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Assign Tags to Characters</h2>

    <?php if ($dresses && $dresses->num_rows > 0): ?>
        <?php while ($dress = $dresses->fetch_assoc()): ?>
            <?php
            // Get existing tags
            $existing = [];
            $tagQ = $db->query("SELECT tag FROM dresses_tags_tbl WHERE dress_id = {$dress['id']}");
            if ($tagQ) {
                while ($row = $tagQ->fetch_assoc()) {
                    $existing[] = $row['tag'];
                }
            }
            ?>
            <form method="POST" class="tag-box">
                <h4><?= htmlspecialchars($dress['name']) ?></h4>
                <input type="hidden" name="dress_id" value="<?= $dress['id'] ?>">
                <?php foreach ($tags as $tag): ?>
                    <label>
                        <input type="checkbox" name="tags[]" value="<?= $tag ?>" <?= in_array($tag, $existing) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($tag) ?>
                    </label><br>
                <?php endforeach; ?>
                <button type="submit">Save Tags</button>
            </form>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No dresses found.</p>
    <?php endif; ?>

    <h3> Tag Frequency Report</h3>
    <ul>
        <?php
        if ($report && $report->num_rows > 0) {
            while ($row = $report->fetch_assoc()) {
                echo "<li><strong>" . htmlspecialchars($row['tag']) . ":</strong> " . $row['count'] . " character(s)</li>";
            }
        } else {
            echo "<li>No tags found or report failed. Error: " . htmlspecialchars($db->error) . "</li>";
        }
        ?>
    </ul>
</div>
</body>
</html>
