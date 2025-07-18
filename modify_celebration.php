<?php
require 'db_configuration.php';  // Include database connection config

$upload_dir = 'images/celebration_images/';  // Directory to store uploaded images

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form submission for updating celebration

    // Collect form data
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $resource_type = $_POST['resource_type'];
    $celebration_type = $_POST['celebration_type'];
    $celebration_date = $_POST['celebration_date'];
    $tags = $_POST['tags'];
    $resource_url = $_POST['resource_url'];
    $img_url = $_POST['current_img'];  // Current image filename (hidden field)

    // Handle image upload if a new file is provided
    if (isset($_FILES['img_url']) && $_FILES['img_url']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['img_url']['tmp_name'];  // Temporary file path
        $filename = basename($_FILES['img_url']['name']);  // Original file name
        $target_path = $upload_dir . $filename;  // Destination path

        // Move uploaded file to target directory
        if (move_uploaded_file($tmp_name, $target_path)) {
            $img_url = $filename;  // Update image URL to new file name
        }
        // Note: you may want to add validation for file type and size here
    }

    // Prepare SQL update statement with placeholders to prevent SQL injection
    $sql = "UPDATE celebrations_tbl SET 
                title = ?, 
                description = ?, 
                resource_type = ?, 
                celebration_type = ?, 
                celebration_date = ?, 
                tags = ?, 
                resource_url = ?, 
                img_url = ? 
            WHERE id = ?";

    $stmt = $db->prepare($sql);  // Prepare statement
    if ($stmt) {
        // Bind parameters to statement (s = string, i = integer)
        $stmt->bind_param("ssssssssi", $title, $description, $resource_type, $celebration_type, $celebration_date, $tags, $resource_url, $img_url, $id);
        $stmt->execute();  // Execute update
        $stmt->close();

        // Redirect back to admin celebrations list after successful update
        header("Location: admin_celebrations.php");
        exit();
    } else {
        // If statement preparation failed, show error
        die("Error preparing statement: " . $db->error);
    }
} else {
    // GET request - show the form pre-filled with existing celebration data

    if (!isset($_GET['id'])) {
        die('No celebration ID provided.');
    }

    $id = intval($_GET['id']);  // Sanitize id

    // Retrieve celebration record by ID
    $query = "SELECT * FROM celebrations_tbl WHERE id = $id";
    $result = mysqli_query($db, $query);

    if (!$result || mysqli_num_rows($result) == 0) {
        die('Celebration not found.');
    }

    $row = mysqli_fetch_assoc($result);  // Fetch the row data

    include('header.php');  // Include page header
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Modify Celebration</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/responsive_style.css">
    <style>
        /* Style for page title */
        #title {
            text-align: center;
            color: darkgoldenrod;
        }

        /* Limit form width and center it */
        form {
            max-width: 600px;
            margin: auto;
        }

        /* Style for image preview */
        .image-preview {
            max-height: 250px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <h1 id="title">Modify Celebration</h1>
    <br>
    <!-- Form for editing celebration details -->
    <form method="post" action="modify_celebration.php" enctype="multipart/form-data">
        <!-- Hidden fields for ID and current image filename -->
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <input type="hidden" name="current_img" value="<?php echo htmlspecialchars($row['img_url']); ?>">

        <!-- Title input -->
        <div class="form-group">
            <label>Title</label>
            <input name="title" class="form-control" required value="<?php echo htmlspecialchars($row['title']); ?>">
        </div>

        <!-- Description textarea -->
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control"><?php echo htmlspecialchars($row['description']); ?></textarea>
        </div>

        <!-- Resource Type select dropdown -->
        <div class="form-group">
            <label>Resource Type</label>
            <select name="resource_type" class="form-control" required>
                <?php
                // Populate options, select current value
                $types = ['PDF', 'PPT', 'HTML', 'Image', 'Video', 'Audio'];
                foreach ($types as $type) {
                    $selected = $row['resource_type'] == $type ? 'selected' : '';
                    echo "<option value=\"$type\" $selected>$type</option>";
                }
                ?>
            </select>
        </div>

        <!-- Celebration Type select dropdown -->
        <div class="form-group">
            <label>Celebration Type</label>
            <select name="celebration_type" class="form-control" required>
                <option value="Person-based" <?php if ($row['celebration_type'] == 'Person-based') echo 'selected'; ?>>Person-based</option>
                <option value="Event-based" <?php if ($row['celebration_type'] == 'Event-based') echo 'selected'; ?>>Event-based</option>
            </select>

        </div>

        <!-- Celebration Date input -->
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="celebration_date" class="form-control" required value="<?php echo $row['celebration_date']; ?>">
        </div>

        <!-- Tags input (comma separated) -->
        <div class="form-group">
            <label>Tags (comma separated)</label>
            <input name="tags" class="form-control" value="<?php echo htmlspecialchars($row['tags']); ?>">
        </div>

        <!-- Resource URL input -->
        <div class="form-group">
            <label>Resource URL</label>
            <input name="resource_url" class="form-control" value="<?php echo htmlspecialchars($row['resource_url']); ?>">
        </div>

        <!-- Current Image display and upload new image -->
        <div class="form-group">
            <label>Current Image:</label><br>
            <?php if (!empty($row['img_url'])): ?>
                <!-- Show current image if exists -->
                <img src="images/celebration_images/<?php echo htmlspecialchars($row['img_url']); ?>" class="image-preview" alt="Current Image"><br>
            <?php else: ?>
                <!-- No image uploaded message -->
                <em>No image uploaded</em><br>
            <?php endif; ?>

            <label>Upload New Image:</label>
            <input type="file" name="img_url" id="imgInput" class="form-control-file" accept="image/*"><br>

            <!-- Preview label, hidden by default -->
            <label id="previewLabel" style="display:none;">New Image:</label><br>
            <!-- Preview image, hidden by default -->
            <img id="imgPreview" src="#" alt="Image Preview" class="image-preview" style="display:none;">
        </div>

        <br>
        <!-- Submit and Cancel buttons -->
        <div style="text-align:center;">
            <button type="submit" class="btn btn-success">Update Celebration</button>
            <a href="admin_celebrations.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <!-- JavaScript for live image preview -->
    <script>
        const imgInput = document.getElementById('imgInput');
        const preview = document.getElementById('imgPreview');
        const previewLabel = document.getElementById('previewLabel');

        imgInput.addEventListener('change', function(event) {
            const [file] = event.target.files;

            // Show preview only if file is an image
            if (file && file.type.startsWith('image/')) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'inline-block';
                previewLabel.style.display = 'inline';

                // Revoke the object URL to free memory when loaded
                preview.onload = () => {
                    URL.revokeObjectURL(preview.src);
                };
            } else {
                // Hide preview and label if no valid image selected
                preview.src = '#';
                preview.style.display = 'none';
                previewLabel.style.display = 'none';
            }
        });
    </script>
</body>

</html>