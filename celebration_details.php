<?php
require 'bin/functions.php';
require 'db_configuration.php';
include('chatbot.php');
include('header.php');


// Get the id from the URL, e.g., ?id=3
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get details about this celebration
$sql = "SELECT * FROM celebrations_tbl WHERE id = $id";
$result = mysqli_query($db, $sql);
$celebration = mysqli_fetch_assoc($result);
?>

<html>
<head>
    <title><?php echo htmlspecialchars($celebration['title']); ?></title>
    <style>
        .details-container {
            width: 80%;
            max-width: 700px;
            margin: 20px auto;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
            background: #f9f9f9;
            box-shadow: 2px 2px 8px rgba(0,0,0,0.2);
        }
        .details-container img, video, audio {
            width: 100%;
            margin-bottom: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="details-container">
    <h2><?php echo htmlspecialchars($celebration['title']); ?></h2>

    <?php
    // Show the resource, depending on type
    if ($celebration['resource_type'] == 'Image') {
        echo '<img src="' . htmlspecialchars($celebration['resource_url']) . '" alt="Celebration">';
    } elseif ($celebration['resource_type'] == 'Video') {
        echo '<video controls src="' . htmlspecialchars($celebration['resource_url']) . '"></video>';
    } elseif ($celebration['resource_type'] == 'Audio') {
        echo '<audio controls src="' . htmlspecialchars($celebration['resource_url']) . '"></audio>';
    } else {
        // For PDF, PPT, HTML etc.
        echo '<p><a href="' . htmlspecialchars($celebration['resource_url']) . '" target="_blank">View Resource</a></p>';
    }
    ?>

    <p><strong>Type:</strong> <?php echo htmlspecialchars($celebration['celebration_type']); ?></p>
    <p><strong>Date:</strong> <?php echo htmlspecialchars($celebration['celebration_date']); ?></p>
    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($celebration['description'])); ?></p>
    <p><strong>Tags:</strong> <?php echo htmlspecialchars($celebration['tags']); ?></p>
</div>

</body>
</html>
