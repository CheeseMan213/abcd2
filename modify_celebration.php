<?php
require 'db_configuration.php';  // Include database connection config

$upload_dir = 'images/celebration_images/';  // Directory to store uploaded images

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $resource_type = $_POST['resource_type'];
    $celebration_type = $_POST['celebration_type'];
    $celebration_date = $_POST['celebration_date'];
    $resource_url = $_POST['resource_url'];
    $img_url = $_POST['current_img'];  // Current image filename

    // Handle image upload if a new file is provided
    if (isset($_FILES['img_url']) && $_FILES['img_url']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['img_url']['tmp_name'];
        $filename = basename($_FILES['img_url']['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($tmp_name, $target_path)) {
            $img_url = $filename;
        }
    }

    // Update main celebration
    $sql = "UPDATE celebrations_tbl SET 
                title = ?, 
                description = ?, 
                resource_type = ?, 
                celebration_type = ?, 
                celebration_date = ?, 
                resource_url = ?, 
                img_url = ? 
            WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("sssssssi", $title, $description, $resource_type, $celebration_type, $celebration_date, $resource_url, $img_url, $id);
    $stmt->execute();
    $stmt->close();

    // Update tags in celebration_tags_tbl
    if (isset($_POST['tags'])) {
        $selectedTags = $_POST['tags']; // array of tag IDs

        // Delete existing tag links
        $db->query("DELETE FROM celebration_tags_tbl WHERE celebration_id = $id");

        // Insert new selected tags
        $stmtTags = $db->prepare("INSERT INTO celebration_tags_tbl (celebration_id, tag_id) VALUES (?, ?)");
        foreach ($selectedTags as $tagId) {
            $stmtTags->bind_param("ii", $id, $tagId);
            $stmtTags->execute();
        }
        $stmtTags->close();
    }

    header("Location: admin_celebrations.php");
    exit();

} else {
    // GET request - pre-fill form
    if (!isset($_GET['id'])) die('No celebration ID provided.');
    $id = intval($_GET['id']);

    $query = "SELECT * FROM celebrations_tbl WHERE id = $id";
    $result = mysqli_query($db, $query);
    if (!$result || mysqli_num_rows($result) == 0) die('Celebration not found.');
    $row = mysqli_fetch_assoc($result);

    // Fetch existing tags for this celebration
    $existingTags = [];
    $tagResult = mysqli_query($db, "SELECT tag_id FROM celebration_tags_tbl WHERE celebration_id = $id");
    while($t = mysqli_fetch_assoc($tagResult)) $existingTags[] = $t['tag_id'];

    include('header.php');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modify Celebration</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/responsive_style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        #title { text-align: center; color: darkgoldenrod; }
        form { max-width: 600px; margin:auto; }
        .image-preview { max-height: 250px; margin-bottom:10px; }
    </style>
</head>
<body>
<h1 id="title">Modify Celebration</h1>
<form method="post" action="modify_celebration.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
    <input type="hidden" name="current_img" value="<?php echo htmlspecialchars($row['img_url']); ?>">

    <div class="form-group">
        <label>Title</label>
        <input name="title" class="form-control" required value="<?php echo htmlspecialchars($row['title']); ?>">
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control"><?php echo htmlspecialchars($row['description']); ?></textarea>
    </div>

    <div class="form-group">
        <label>Resource Type</label>
        <select name="resource_type" class="form-control" required>
            <?php
            $types = ['PDF', 'PPT', 'HTML', 'Image', 'Video', 'Audio'];
            foreach ($types as $type) {
                $selected = $row['resource_type'] == $type ? 'selected' : '';
                echo "<option value=\"$type\" $selected>$type</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label>Celebration Type</label>
        <select name="celebration_type" class="form-control" required>
            <option value="Person-based" <?php if ($row['celebration_type']=='Person-based') echo 'selected'; ?>>Person-based</option>
            <option value="Event-based" <?php if ($row['celebration_type']=='Event-based') echo 'selected'; ?>>Event-based</option>
        </select>
    </div>

    <div class="form-group">
        <label>Date</label>
        <input type="date" name="celebration_date" class="form-control" required value="<?php echo $row['celebration_date']; ?>">
    </div>

    <div class="form-group">
        <label>Tags</label>
        <select name="tags[]" class="form-control" multiple id="tagsSelect">
            <?php
            $allTags = mysqli_query($db, "SELECT * FROM tags ORDER BY tag_name");
            while ($t = mysqli_fetch_assoc($allTags)) {
                $selected = in_array($t['id'], $existingTags) ? 'selected' : '';
                echo "<option value='{$t['id']}' $selected>{$t['tag_name']}</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label>Resource URL</label>
        <input name="resource_url" class="form-control" value="<?php echo htmlspecialchars($row['resource_url']); ?>">
    </div>

    <div class="form-group">
        <label>Current Image:</label><br>
        <?php if (!empty($row['img_url'])): ?>
            <img src="images/celebration_images/<?php echo htmlspecialchars($row['img_url']); ?>" class="image-preview" alt="Current Image"><br>
        <?php else: ?>
            <em>No image uploaded</em><br>
        <?php endif; ?>

        <label>Upload New Image:</label>
        <input type="file" name="img_url" id="imgInput" class="form-control-file" accept="image/*"><br>
        <label id="previewLabel" style="display:none;">New Image:</label><br>
        <img id="imgPreview" src="#" alt="Image Preview" class="image-preview" style="display:none;">
    </div>

    <div style="text-align:center;">
        <button type="submit" class="btn btn-success">Update Celebration</button>
        <a href="admin_celebrations.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#tagsSelect').select2({ placeholder: 'Select Tags' });

    const imgInput = document.getElementById('imgInput');
    const preview = document.getElementById('imgPreview');
    const previewLabel = document.getElementById('previewLabel');

    imgInput.addEventListener('change', function(event) {
        const [file] = event.target.files;
        if (file && file.type.startsWith('image/')) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'inline-block';
            previewLabel.style.display = 'inline';
            preview.onload = () => { URL.revokeObjectURL(preview.src); };
        } else {
            preview.src = '#';
            preview.style.display = 'none';
            previewLabel.style.display = 'none';
        }
    });
});
</script>
</body>
</html>