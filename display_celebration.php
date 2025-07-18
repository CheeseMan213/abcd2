<?php
include('header.php');
include_once 'db_configuration.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Your Celebration Title</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/display_the_dress.css">
    <link rel="stylesheet" href="./css/responsive_style.css">
</head>
<body>
<?php
// Get the celebration ID from URL or set false if missing
$id = false;

if (isset($_GET['id'])) {
  $id = mysqli_real_escape_string($db, $_GET['id']);
}

if ($id) {
  // Get min and max celebration IDs for navigation
  $minMaxSql = "SELECT MIN(id) as min_id, MAX(id) as max_id FROM `celebrations_tbl`";
  $minMaxResult = mysqli_query($db, $minMaxSql);
  $minMaxRow = $minMaxResult->fetch_assoc();
  $min_id = $minMaxRow["min_id"];
  $max_id = $minMaxRow["max_id"];

  // Get previous and next celebration IDs for navigation
  $prevSql = "SELECT id FROM `celebrations_tbl` WHERE id < $id ORDER BY id DESC LIMIT 1";
  $nextSql = "SELECT id FROM `celebrations_tbl` WHERE id > $id ORDER BY id ASC LIMIT 1";

  $prevResult = mysqli_query($db, $prevSql);
  $nextResult = mysqli_query($db, $nextSql);

  $prev_id = ($prevResult->num_rows > 0) ? $prevResult->fetch_assoc()["id"] : $max_id;
  $next_id = ($nextResult->num_rows > 0) ? $nextResult->fetch_assoc()["id"] : $min_id;

  // Query celebration details
  $sql = "SELECT * FROM `celebrations_tbl` WHERE id = $id";
  $row_data = mysqli_query($db, $sql);

  if ($row_data->num_rows > 0) {
    // Fetch and display the celebration info
    while($row = $row_data->fetch_assoc()) { ?>
      <div class="containerTitle">
        <h2 class="headTwo"><?php echo htmlspecialchars($row["title"]); ?></h2>
      </div>

      <div class="pageNavContainer">
        <tr class="pageNav">
          <td><a class="pageLink pageButton" href="display_celebration.php?id=<?php echo $min_id; ?>"><< First</a></td>
          <td><a class="pageLink pageButton" href="display_celebration.php?id=<?php echo $prev_id; ?>">Prev</a></td>
          <td><a class="pageLink pageButton" href="display_celebration.php?id=<?php echo $next_id; ?>">Next</a></td>
          <td><a class="pageLink pageButton" href="display_celebration.php?id=<?php echo $max_id; ?>">Last >></a></td>
        </tr>
      </div>

      <div class="container">
        <div class="containerImage">
          <img class="image" src="images/celebration_images/<?php echo htmlspecialchars($row["img_url"]); ?>" alt="<?php echo htmlspecialchars($row["title"]); ?>">
        </div>
        <div class="containerText">
          <h3 class="title"><strong>Description:</strong></h3>
          <p class="words"><?php echo nl2br(htmlspecialchars($row["description"])); ?></p>

          <?php if (!empty($row['resource_url'])): ?>
            <h3 class="title"><strong>Resource Link:</strong></h3>
            <p class="words">
              <a href="<?php echo htmlspecialchars($row['resource_url']); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo htmlspecialchars($row['resource_url']); ?>
              </a>
            </p>
          <?php endif; ?>
          
          <h3 class="title"><strong>Celebration Date:</strong></h3>
          <p class="words"><?php echo htmlspecialchars($row["celebration_date"]); ?></p>

          <h3 class="title"><strong>Type:</strong></h3>
          <p class="words"><?php echo htmlspecialchars($row["celebration_type"]); ?></p>

          <h3 class="title"><strong>Resource Type:</strong></h3>
          <p class="words"><?php echo htmlspecialchars($row["resource_type"]); ?></p>

          <h3 class="title"><strong>Tags:</strong></h3>
          <p class="words"><?php echo htmlspecialchars($row["tags"]); ?></p>
        </div>
      </div>

    <?php }
  } else {
    echo "No data found for this celebration.";
  }
} else {
  echo "No celebration ID provided.";
}
?>
</body>
</html>