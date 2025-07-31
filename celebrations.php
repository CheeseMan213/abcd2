<?php
// Include database configuration and page header
require 'db_configuration.php';
include('header.php');

// Default image dimensions
$image_height = 250;
$image_width = 200;

// Get search and filter parameters from GET request
$q = isset($_GET['q']) ? trim(mysqli_real_escape_string($db, $_GET['q'])) : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$resource_type = isset($_GET['resource_type']) ? $_GET['resource_type'] : '';
$celebration_type = isset($_GET['celebration_type']) ? $_GET['celebration_type'] : '';
$tags = isset($_GET['tags']) ? trim(mysqli_real_escape_string($db, $_GET['tags'])) : '';

// Allowed sort options
$sort_options = ['title', 'celebration_date'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $sort_options) ? $_GET['sort'] : 'title';

// If no filters provided, default to today's date
if (!$q && !$date && !$resource_type && !$celebration_type && !$tags) {
    $date = date('Y-m-d');
}

// Build WHERE conditions based on filters
$where = [];

if ($date) {
    $where[] = "celebration_date = '$date'";
}

if ($q) {
    $q_like = "%$q%";
    $where[] = "(title LIKE '$q_like' OR description LIKE '$q_like')";
}

if ($resource_type) {
    $where[] = "resource_type = '$resource_type'";
}

if ($celebration_type) {
    $where[] = "celebration_type = '$celebration_type'";
}

if ($tags) {
    // Handle multiple comma-separated tags
    $tagArray = array_filter(array_map('trim', explode(',', $tags)));
    if (count($tagArray) > 0) {
        $tagClauses = [];
        foreach ($tagArray as $tag) {
            $tagEscaped = mysqli_real_escape_string($db, $tag);
            $tagClauses[] = "tags LIKE '%$tagEscaped%'";
        }
        $where[] = '(' . implode(' OR ', $tagClauses) . ')';
    }
}

// Combine WHERE clauses
$whereSQL = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Final query with sorting
$sql = "SELECT * FROM celebrations_tbl $whereSQL ORDER BY $sort ASC";
$result = mysqli_query($db, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Today's Celebrations</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/responsive_style.css">
    <style>
        /* Style the container and table */
        #responsive_table_2 {
            margin: 0 auto;
            max-width: 1000px;
            text-align: center;
        }

        #table_2 {
            margin: 0 auto;
            border-collapse: collapse;
            width: 900px;
        }

        #table_2 td {
            text-align: center;
            vertical-align: top;
            padding: 20px;
        }

        #title {
            margin-top: 6px;
            font-weight: bold;
            font-size: 1.1em;
            color: #333;
        }

        a:hover #title {
            color: #4285f4;
            text-decoration: underline;
        }

        /* Filter form styling */
        .filterForm {
            margin-bottom: 20px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        .filterForm input, .filterForm select {
            margin: 5px;
            padding: 6px;
            font-size: 1em;
        }

        .filterForm button {
            padding: 6px 12px;
            font-size: 1em;
            cursor: pointer;
        }
    </style>
</head>
<body>

<!-- Page Title -->
<h1 class="mainTitle">Celebrations</h1>
<h2 class="selectTitle">Select a celebration to learn more about it</h2>

<!-- Filter/Search Form -->
<form class="filterForm" method="get" action="">

    <input type="text" name="q" placeholder="Search" value="<?php echo htmlspecialchars($q); ?>" />

    <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" />

    <!-- Resource Type Dropdown -->
    <select name="resource_type">
        <option value="">All Resource Types</option>
        <option value="PDF" <?php if ($resource_type === 'PDF') echo 'selected'; ?>>PDF</option>
        <option value="PPT" <?php if ($resource_type === 'PPT') echo 'selected'; ?>>PPT</option>
        <option value="HTML" <?php if ($resource_type === 'HTML') echo 'selected'; ?>>HTML</option>
        <option value="Image" <?php if ($resource_type === 'Image') echo 'selected'; ?>>Image</option>
        <option value="Video" <?php if ($resource_type === 'Video') echo 'selected'; ?>>Video</option>
        <option value="Audio" <?php if ($resource_type === 'Audio') echo 'selected'; ?>>Audio</option>
    </select>

    <!-- Celebration Type Dropdown -->
    <select name="celebration_type">
        <option value="">All Celebration Types</option>
        <option value="Person" <?php if ($celebration_type === 'Person') echo 'selected'; ?>>Person</option>
        <option value="Event" <?php if ($celebration_type === 'Event') echo 'selected'; ?>>Event</option>
    </select>

    <!-- Tags -->
    <input type="text" name="tags" placeholder="Tags (comma separated)" value="<?php echo htmlspecialchars($tags); ?>" />

    <!-- Sort Option -->
    <select name="sort">
        <option value="title" <?php if ($sort === 'title') echo 'selected'; ?>>Sort by Title</option>
        <option value="celebration_date" <?php if ($sort === 'celebration_date') echo 'selected'; ?>>Sort by Date</option>
    </select>

    <!-- Form Buttons -->
    <button type="submit">Search / Filter</button>
    <button type="button" onclick="window.location='celebrations.php'">Reset</button>
</form>

<!-- Celebration Grid Display -->
<div class="table-responsive-lg" id="responsive_table_2">
<?php if (mysqli_num_rows($result) === 0): ?>
    <!-- No results message -->
    <p style="text-align:center; font-size:1.2em; color:#555; margin-top:40px;">
        <?php 
        if (!$q && !$resource_type && !$celebration_type && !$tags && $date === date('Y-m-d')) {
            echo "No celebrations found for today (" . date('F j, Y') . ").";
        } else {
            echo "No celebrations found matching your criteria.";
        }
        ?>
    </p>
<?php else: ?>
    <table id="table_2">
        <tr>
            <?php
            $counter = 0;
            // Loop through celebrations and display them in rows of 4
            while ($row = mysqli_fetch_assoc($result)) {
                if ($counter % 4 === 0 && $counter !== 0) {
                    echo "</tr><tr>";
                }

                $id = $row['id'];
                $title = htmlspecialchars($row['title']);
                $description = htmlspecialchars($row['description']);
                $date_str = date('F j, Y', strtotime($row['celebration_date']));
                $res_type = htmlspecialchars($row['resource_type']);
                $celebr_type = htmlspecialchars($row['celebration_type']);
                $tags_str = htmlspecialchars($row['tags']);
                $resource_url = htmlspecialchars($row['resource_url']);
                $image = !empty($row['img_url']) ? htmlspecialchars($row['img_url']) : 'default_image.png';
                $img_path = "images/celebration_images/" . $image;

                // Display tile
                echo "<td style='padding:20px; vertical-align:top; width: 25%;'>
                        <a href='display_celebration.php?id=$id' title='$title'>
                            <img src='$img_path' width='$image_width' height='$image_height' alt='$title'><br>
                            <div id='title'>$title</div>
                        </a>
                      </td>";

                $counter++;
            }

            // Fill in blank columns to complete the last row
            while ($counter % 4 !== 0) {
                echo "<td></td>";
                $counter++;
            }
            ?>
        </tr>
    </table>
<?php endif; ?>
</div>

</body>
</html>