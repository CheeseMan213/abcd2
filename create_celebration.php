<?php
require 'db_configuration.php';

$upload_dir = 'images/celebration_images/';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $resource_type = $_POST['resource_type'];
    $celebration_type = $_POST['celebration_type'];
    $celebration_date = $_POST['celebration_date'];
    $resource_url = $_POST['resource_url'];
    $tags_input = $_POST['tags'] ?? [];

    $img_url = '';

    // Handle image upload
    if (isset($_FILES['img_url']) && $_FILES['img_url']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['img_url']['tmp_name'];
        $filename = basename($_FILES['img_url']['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($tmp_name, $target_path)) {
            $img_url = $filename;
        }
    }

    // Insert celebration
    $sql = "INSERT INTO celebrations_tbl 
        (title, description, resource_type, celebration_type, celebration_date, resource_url, img_url) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssssss", $title, $description, $resource_type, $celebration_type, $celebration_date, $resource_url, $img_url);
        $stmt->execute();
        $celebration_id = $stmt->insert_id;
        $stmt->close();

        // Handle tags
        foreach ($tags_input as $tag_value) {
            $tag_value = trim($tag_value);

            if (is_numeric($tag_value)) {
                $tag_id = (int)$tag_value;
            } else {
                // Check if tag already exists
                $check = $db->prepare("SELECT id FROM tags WHERE tag_name = ?");
                $check->bind_param("s", $tag_value);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $check->bind_result($tag_id);
                    $check->fetch();
                } else {
                    // Insert new tag
                    $insert = $db->prepare("INSERT INTO tags (tag_name) VALUES (?)");
                    $insert->bind_param("s", $tag_value);
                    $insert->execute();
                    $tag_id = $insert->insert_id;
                    $insert->close();
                }
                $check->close();
            }

            // Link tag to celebration
            $link = $db->prepare("INSERT INTO celebration_tags_tbl (celebration_id, tag_id) VALUES (?, ?)");
            $link->bind_param("ii", $celebration_id, $tag_id);
            $link->execute();
            $link->close();
        }

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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
            <label>Select or Add Tags</label>
            <select name="tags[]" class="form-control" multiple id="tags-select" required>
                <?php
                $result = $db->query("SELECT id, tag_name FROM tags ORDER BY tag_name ASC");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['tag_name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Resource URL</label>
            <input name="resource_url" class="form-control">
        </div>

        <div class="form-group">
            <label>Upload Image</label>
            <input type="file" name="img_url" id="imgInput" class="form-control-file" accept="image/*">
            <label id="previewLabel" style="display:none; margin-top: 10px;">Image Preview:</label><br>
            <img id="imgPreview" src="#" alt="Image Preview" class="image-preview">
        </div>

        <br>
        <div style="text-align:center;">
            <button type="submit" class="btn btn-success">Create Celebration</button>
            <a href="admin_celebrations.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tags-select').select2({
                tags: true,
                placeholder: "Select or add tags",
                width: '100%'
            });
        });

        // Image preview
        const imgInput = document.getElementById('imgInput');
        const preview = document.getElementById('imgPreview');
        const previewLabel = document.getElementById('previewLabel');

        imgInput.addEventListener('change', function(event) {
            const [file] = event.target.files;
            if (file && file.type.startsWith('image/')) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
                previewLabel.style.display = 'inline-block';
                preview.onload = () => URL.revokeObjectURL(preview.src);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
                previewLabel.style.display = 'none';
            }
        });
    </script>
</body>
</html>