<?php
require 'db_configuration.php';

$upload_dir = 'images/celebration_images/';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $resource_type = $_POST['resource_type'];
    $celebration_type = $_POST['celebration_type'];
    $celebration_date = $_POST['celebration_date'];
    $tags = $_POST['tags'];
    $resource_url = $_POST['resource_url'];

    $img_url = ''; // default no image uploaded

    // Handle image upload if exists
    if (isset($_FILES['img_url']) && $_FILES['img_url']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['img_url']['tmp_name'];
        $filename = basename($_FILES['img_url']['name']);
        $target_path = $upload_dir . $filename;

        // Move uploaded file to the target directory
        if (move_uploaded_file($tmp_name, $target_path)) {
            $img_url = $filename;
        }
        // You can add file type and size validation here
    }

    // Insert query including img_url column
    $sql = "INSERT INTO celebrations_tbl 
        (title, description, resource_type, celebration_type, celebration_date, tags, resource_url, img_url) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssssss", $title, $description, $resource_type, $celebration_type, $celebration_date, $tags, $resource_url, $img_url);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_celebrations.php");
        exit();
    } else {
        die("Error preparing statement: " . $db->error);
    }
}

include('header.php');
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Celebration</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/responsive_style.css">
    <style>
        #title {
            text-align: center;
            color: darkgoldenrod;
        }

        form {
            max-width: 600px;
            margin: auto;
        }

        .image-preview {
            max-height: 250px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>

<body>
    <h1 id="title">Create New Celebration</h1>
    <br>
    <!-- Note enctype for file upload -->
    <form method="post" action="create_celebration.php" enctype="multipart/form-data">
        <div class="form-group">
            <label>Title</label>
            <input name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label>Resource Type</label>
            <select name="resource_type" class="form-control" required>
                <option value="PDF">PDF</option>
                <option value="PPT">PPT</option>
                <option value="HTML">HTML</option>
                <option value="Image">Image</option>
                <option value="Video">Video</option>
                <option value="Audio">Audio</option>
            </select>
        </div>
        <div class="form-group">
            <label>Celebration Type</label>
            <select name="celebration_type" class="form-control" required>
                <option value="Person">Person</option>
                <option value="Event">Event</option>
            </select>
        </div>
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="celebration_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Tags (comma separated)</label>
            <input name="tags" class="form-control">
        </div>
        <div class="form-group">
            <label>Resource URL</label>
            <input name="resource_url" class="form-control">
        </div>

        <div class="form-group">
            <label>Upload Image</label>
            <input type="file" name="img_url" id="imgInput" class="form-control-file" accept="image/*">

            <label id="previewLabel" style="display:none; margin-top: 10px;">New Image Preview:</label><br>
            <img id="imgPreview" src="#" alt="Image Preview" class="image-preview" style="display:none;">
        </div>

        <br>
        <div style="text-align:center;">
            <button type="submit" class="btn btn-success">Create Celebration</button>
            <a href="admin_celebrations.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <script>
        const imgInput = document.getElementById('imgInput');
        const preview = document.getElementById('imgPreview');
        const previewLabel = document.getElementById('previewLabel');

        imgInput.addEventListener('change', function(event) {
            const [file] = event.target.files;
            if (file && file.type.startsWith('image/')) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
                previewLabel.style.display = 'inline-block'; // show label when image selected
                preview.onload = () => URL.revokeObjectURL(preview.src); // free memory
            } else {
                preview.src = '#';
                preview.style.display = 'none';
                previewLabel.style.display = 'none'; // hide label when no image
            }
        });
    </script>
</body>

</html>