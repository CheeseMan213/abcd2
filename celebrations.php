<?php
require 'db_configuration.php';
include('header.php');

// --------------------
// Calendar Parameters
// --------------------
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year  = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$monthName = date('F', $firstDay);
$dayOfWeek = date('w', $firstDay); // 0 = Sunday

// Celebration counts per day
$celebration_counts = [];
$sql_counts = "SELECT celebration_date, COUNT(*) as count
               FROM celebrations_tbl
               WHERE MONTH(celebration_date) = $month AND YEAR(celebration_date) = $year
               GROUP BY celebration_date";
$result_counts = mysqli_query($db, $sql_counts);
while ($row = mysqli_fetch_assoc($result_counts)) {
    $celebration_counts[$row['celebration_date']] = $row['count'];
}

// Navigation
$prev_month = $month - 1; $prev_year = $year;
if ($prev_month < 1) { $prev_month = 12; $prev_year--; }
$next_month = $month + 1; $next_year = $year;
if ($next_month > 12) { $next_month = 1; $next_year++; }

// --------------------
// Filters / Search
// --------------------
$q = isset($_GET['q']) ? trim(mysqli_real_escape_string($db, $_GET['q'])) : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$celebration_type = isset($_GET['celebration_type']) ? $_GET['celebration_type'] : '';
$tags = isset($_GET['tags']) ? $_GET['tags'] : []; // array of tag IDs
$resource_type = isset($_GET['resource_type']) ? $_GET['resource_type'] : []; // array of resource types
$sort_options = ['title','celebration_date'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $sort_options) ? $_GET['sort'] : 'title';

// Build WHERE clauses
$where = [];

// Date filter: leave input empty, but default to today in SQL if no filters
if (!empty($date)) {
    $where[] = "celebration_date = '$date'";
} elseif (!$q && !$celebration_type && empty($tags) && empty($resource_type)) {
    $today = date('Y-m-d');
    $where[] = "celebration_date = '$today'";
}

// Search by title (matches any word)
if ($q) {
    $words = explode(' ', $q);
    $search_clauses = [];
    foreach ($words as $word) {
        $word = mysqli_real_escape_string($db, $word);
        $search_clauses[] = "(title LIKE '%$word%' OR description LIKE '%$word%')";
    }
    if (!empty($search_clauses)) $where[] = '(' . implode(' OR ', $search_clauses) . ')';
}

// Celebration type filter
if ($celebration_type) { 
    $celebration_type = mysqli_real_escape_string($db, $celebration_type);
    $where[] = "celebration_type = '$celebration_type'"; 
}

// Resource Type filter (hard-coded values)
$allowedResourceTypes = ['PDF','PPT','HTML','Image','Video','Audio'];
if (!empty($resource_type)) {
    $validTypes = array_intersect($resource_type, $allowedResourceTypes);
    if (!empty($validTypes)) {
        $escaped = array_map(function($r) use ($db) {
            return "'" . mysqli_real_escape_string($db, $r) . "'";
        }, $validTypes);
        $where[] = "celebrations_tbl.resource_type IN (" . implode(',', $escaped) . ")";
    }
}

// Tag filtering
if (!empty($tags)) {
    $tagIds = array_map('intval', $tags);
    $tagIdsStr = implode(',', $tagIds);
    $where[] = "celebrations_tbl.id IN (
        SELECT celebration_id FROM celebration_tags_tbl 
        WHERE tag_id IN ($tagIdsStr)
        GROUP BY celebration_id
        HAVING COUNT(DISTINCT tag_id) = " . count($tagIds) . "
    )";
}

$whereSQL = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT * FROM celebrations_tbl $whereSQL ORDER BY $sort ASC";
$result = mysqli_query($db, $sql);

// Image size
$image_height = 250;
$image_width = 200;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Celebrations</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/responsive_style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .calendar { width: 100%; max-width: 900px; margin: 20px auto; border-collapse: collapse; }
        .calendar th, .calendar td { border: 1px solid #ccc; width: 14.28%; height: 100px; vertical-align: top; text-align: left; padding: 5px; }
        .calendar th { background-color: #f4f4f4; }
        .date-number { font-weight: bold; }
        .celebration-count { margin-top: 5px; font-size: 0.9em; color: #d9534f; }
        #table_2 td { text-align:center; vertical-align:top; padding:20px; width:25%; }
        #title { margin-top:6px; font-weight:bold; font-size:1.1em; color:#333; }
        a:hover #title { color:#4285f4; text-decoration:underline; }
        .filterForm { margin-bottom:20px; max-width:900px; margin-left:auto; margin-right:auto; text-align:center; }
        .filterForm input, .filterForm select { margin:5px; padding:6px; font-size:1em; }
        .filterForm button { padding:6px 12px; font-size:1em; cursor:pointer; }
    </style>
</head>
<body>
<h1 class="mainTitle">Celebrations</h1>
<h2 class="selectTitle">Select a celebration to learn more about it</h2>

<form class="filterForm" method="get" action="">
    <input type="text" name="q" placeholder="Search" value="<?php echo htmlspecialchars($q); ?>" />
    <input type="date" name="date" value="<?php echo htmlspecialchars($date ?? ''); ?>" />

    <!-- Multi-select Resource Type -->
    <select name="resource_type[]" multiple id="resourceSelect" style="width:200px;">
        <?php
        foreach ($allowedResourceTypes as $type) {
            $selected = in_array($type, $resource_type) ? 'selected' : '';
            echo "<option value='$type' $selected>$type</option>";
        }
        ?>
    </select>

    <select name="celebration_type">
        <option value="">All Celebration Types</option>
        <option value="Person-based" <?php if($celebration_type==='Person-based') echo 'selected'; ?>>Person</option>
        <option value="Event-based" <?php if($celebration_type==='Event-based') echo 'selected'; ?>>Event</option>
    </select>

    <!-- Multi-select Tags -->
    <select name="tags[]" multiple id="tagsSelect" style="width:200px;">
        <?php
        $tagResult = mysqli_query($db,"SELECT * FROM tags ORDER BY tag_name");
        while($t = mysqli_fetch_assoc($tagResult)){
            $selected = in_array($t['id'],$tags) ? 'selected' : '';
            echo "<option value='{$t['id']}' $selected>{$t['tag_name']}</option>";
        }
        ?>
    </select>

    <select name="sort">
        <option value="title" <?php if($sort==='title') echo 'selected'; ?>>Sort by Title</option>
        <option value="celebration_date" <?php if($sort==='celebration_date') echo 'selected'; ?>>Sort by Date</option>
    </select>
    <button type="submit">Search / Filter</button>
    <button type="button" onclick="window.location='celebrations.php'">Reset</button>
</form>

<script>
$(document).ready(function() {
    $('#tagsSelect').select2({ placeholder: 'Select Tags' });
    $('#resourceSelect').select2({ placeholder: 'Select Resource Types' });
});
</script>

<!-- Celebration Grid -->
<div class="table-responsive-lg" id="responsive_table_2">
<?php if(mysqli_num_rows($result)===0): ?>
    <p style="text-align:center; font-size:1.2em; color:#555; margin-top:40px;">
        <?php 
        if(!$q&&!$celebration_type&&empty($tags)&&empty($resource_type)&&empty($date)) 
            echo "No celebrations found for today (" . date('F j, Y') . ").";
        else 
            echo "No celebrations found matching your criteria.";
        ?>
    </p>
<?php else: ?>
    <table id="table_2"><tr>
    <?php
    $counter=0;
    while($row=mysqli_fetch_assoc($result)){
        if($counter%4===0&&$counter!==0) echo "</tr><tr>";
        $id=$row['id']; $title=htmlspecialchars($row['title']);
        $image=!empty($row['img_url'])?htmlspecialchars($row['img_url']):'default_image.png';
        $img_path="images/celebration_images/".$image;
        echo "<td><a href='display_celebration.php?id=$id&month=$month&year=$year' title='$title'>
              <img src='$img_path' width='200' height='250' alt='$title'><br>
              <div id='title'>$title</div></a></td>";
        $counter++;
    }
    while($counter%4!==0){ echo "<td></td>"; $counter++; }
    ?>
    </tr></table>
<?php endif; ?>
</div>

<!-- Calendar -->
<h2 style="text-align:center;"><?php echo $monthName . ' ' . $year; ?></h2>
<div style="text-align:center; margin-bottom:10px;">
    <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>">« Previous</a> |
    <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>">Next »</a>
</div>
<table class="calendar">
    <tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>
    <tr>
    <?php
    for ($blank=0;$blank<$dayOfWeek;$blank++) echo "<td></td>";
    $dayCounter = 1;
    $currentCell = $dayOfWeek;
    while ($dayCounter <= $daysInMonth) {
        $dateStr = sprintf('%04d-%02d-%02d',$year,$month,$dayCounter);
        $count = $celebration_counts[$dateStr] ?? 0;
        echo "<td><div class='date-number'>$dayCounter</div>";
        if($count>0) echo "<div class='celebration-count'>🎉 x$count</div><a href='celebrations.php?date=$dateStr&month=$month&year=$year'>View</a>";
        echo "</td>";
        $dayCounter++; $currentCell++;
        if($currentCell%7==0 && $dayCounter<=$daysInMonth) echo "</tr><tr>";
    }
    while($currentCell%7!=0){ echo "<td></td>"; $currentCell++; }
    ?>
    </tr>
</table>

</body>
</html>