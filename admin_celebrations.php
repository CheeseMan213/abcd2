<?php
require 'bin/functions.php';
require 'db_configuration.php';
include('header.php');

$query = "
SELECT c.id, c.title, c.description, c.resource_type, c.celebration_type, 
       c.celebration_date, c.resource_url, c.img_url,
       GROUP_CONCAT(t.name SEPARATOR ', ') AS tags
FROM celebrations_tbl c
LEFT JOIN celebration_tags_tbl ct ON c.id = ct.celebration_id
LEFT JOIN tags t ON ct.tag_id = t.id
GROUP BY c.id, c.title, c.description, c.resource_type, 
         c.celebration_type, c.celebration_date, c.resource_url, c.img_url
";

$GLOBALS['data'] = mysqli_query($db, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Celebrations Management</title>
    <style>
        #title {
            text-align: center;
            color: darkgoldenrod;
        }

        thead input {
            width: 100%;
        }

        .thumbnailSize {
            height: 100px;
            width: 100px;
            transition: transform 0.25s ease;
            cursor: pointer;
            position: relative;
            z-index: 1;
        }

        .thumbnailSize:hover {
            transform: scale(3.5);
            position: relative;
            z-index: 9999;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        #celebrationsTable tbody td.tagsCell {
            white-space: normal;
            max-width: 150px;
            word-wrap: break-word;
        }

        #celebrationsTable {
            table-layout: fixed;
            width: 100%;
        }

        #celebrationsTable th,
        #celebrationsTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }

        #celebrationsTable td:nth-child(3),
        #celebrationsTable th:nth-child(3) {
            width: 20%;
            max-width: 20%;
            white-space: normal;
            overflow-wrap: break-word;
            text-overflow: clip;
        }

        #celebrationsTable td:nth-child(9),
        #celebrationsTable th:nth-child(9) {
            width: 100px;
            max-width: 100px;
            overflow: visible !important;
            white-space: nowrap;
        }

        #celebrationsTable tbody tr {
            overflow: visible !important;
        }

        #celebrationsTable td:nth-child(2),
        #celebrationsTable th:nth-child(2) {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            max-width: none !important;
        }
    </style>
</head>

<body>
    <br><br>
    <div class="container-fluid">
        <h2 id="title">Celebrations Management</h2><br>

        <button style="margin-bottom: 15px;"><a href="create_celebration.php" class="btn btn-sm">Create New Celebration</a></button>

        <div id="celebrationsTableView">
            <table class="display" id="celebrationsTable" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Resource Type</th>
                        <th>Celebration Type</th>
                        <th>Date</th>
                        <th>Tags</th>
                        <th>Resource URL</th>
                        <th>Image</th>
                        <th>Modify</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($data->num_rows > 0) {
                        while ($row = $data->fetch_assoc()) {
                            echo '<tr>
                                <td>' . $row["id"] . '</td>
                                <td>' . htmlspecialchars($row["title"]) . '</td>
                                <td>' . htmlspecialchars(substr($row["description"], 0, 100)) . '...</td>
                                <td>' . htmlspecialchars($row["resource_type"]) . '</td>
                                <td>' . htmlspecialchars($row["celebration_type"]) . '</td>
                                <td>' . htmlspecialchars($row["celebration_date"]) . '</td> 
                                <td class="tagsCell">' . str_replace(',', '<br>', htmlspecialchars($row["tags"])) . '</td>
                                <td><a href="' . htmlspecialchars($row["resource_url"]) . '" target="_blank">View</a></td>
                                <td><img src="images/celebration_images/' . $row["img_url"] . '" class="thumbnailSize"></td>
                                <td><a class="btn btn-warning btn-sm" href="modify_celebration.php?id=' . $row["id"] . '">Modify</a></td>
                                <td>
                                    <a class="btn btn-danger btn-sm" 
                                       href="delete_celebration.php?id=' . $row["id"] . '" 
                                       onclick="return confirm(\'Are you sure you want to delete this celebration?\')">
                                       Delete
                                    </a>
                                </td>
                            </tr>';
                        }
                    } else {
                        echo "<tr><td colspan='11'>No results found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

<footer class="page-footer text-center">
    <p>Created for SILC CS Class #3 PHP</p>
</footer>

<!-- JQuery & DataTables Scripts -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/fixedheader/3.1.5/js/dataTables.fixedHeader.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#celebrationsTable thead tr').clone(true).appendTo('#celebrationsTable thead');
        $('#celebrationsTable thead tr:eq(1) th').each(function (i) {
            var title = $(this).text();
            $(this).html('<input type="text" placeholder="Search ' + title + '" />');

            $('input', this).on('keyup change', function () {
                if (table.column(i).search() !== this.value) {
                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        var table = $('#celebrationsTable').DataTable({
            dom: 'lfrtBip',
            buttons: ['copy', 'excel', 'csv', 'pdf'],
            orderCellsTop: true,
            fixedHeader: true,
            retrieve: true
        });
    });
</script>