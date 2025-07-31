<?php
require 'db_configuration.php'; 

$tags = file('abcd_tags.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($tags as $tag) {
    $tag = trim($tag);
    if (!empty($tag)) {
        $stmt = $db->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
        $stmt->bind_param("s", $tag);
        $stmt->execute();
        $stmt->close();
    }
}

echo "Tags synced successfully.";
?>