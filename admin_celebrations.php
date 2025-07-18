<?php
require 'bin/functions.php';
require 'db_configuration.php';
include('header.php');

$query = "SELECT * FROM celebrations_tbl";
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

        /* Thumbnail image style with zoom on hover */
        .thumbnailSize {
            height: 100px;
            width: 100px;
            transition: transform 0.25s ease;
            cursor: pointer;
            position: relative;
            z-index: 1;
        }

        /* On hover, zoom in and bring to front */
        .thumbnailSize:hover {
            transform: scale(3.5);
            position: relative;
            z-index: 9999;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        /* Tags cell style - allow line breaks */
        #celebrationsTable tbody td.tagsCell {
            white-space: normal;
            max-width: 150px;
            word-wrap: break-word;
        }

        /* Fixed layout and 100% width */
        #celebrationsTable {
            table-layout: fixed;
            width: 100%;
        }

        /* All columns except Description and Image: equal widths, nowrap, ellipsis */
        #celebrationsTable th,
        #celebrationsTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }

        /* Description column (3rd) - wider and wrap text */
        #celebrationsTable td:nth-child(3),
        #celebrationsTable th:nth-child(3) {
            width: 20%;
            max-width: 20%;
            white-space: normal;
            overflow-wrap: break-word;
            text-overflow: clip;
        }

        /* Image column (9th) - allow overflow so zoomed image can extend */
        #celebrationsTable td:nth-child(9),
        #celebrationsTable th:nth-child(9) {
            width: 100px;
            /* fixed width for image */
            max-width: 100px;
            overflow: visible !important;
            /* Important to allow zoom overflow */
            white-space: nowrap;
        }

        /* Also override overflow for the entire row to visible to avoid clipping */
        #celebrationsTable tbody tr {
            overflow: visible !important;
        }

        /* Title column (2nd) - show full text, wrap if needed */
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

<!--JQuery-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<!--Data Table-->
<script type="text/javascript" charset="utf8"
    src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/fixedheader/3.1.5/js/dataTables.fixedHeader.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

<script type="text/javascript" language="javascript">
    $(document).ready(function() {

        $('#celebrationsTable').DataTable({
            dom: 'lfrtBip',
            buttons: [
                'copy', 'excel', 'csv', 'pdf'
            ]
        });

        $('#celebrationsTable thead tr').clone(true).appendTo('#celebrationsTable thead');
        $('#celebrationsTable thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" placeholder="Search ' + title + '" />');

            $('input', this).on('keyup change', function() {
                if (table.column(i).search() !== this.value) {
                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        var table = $('#celebrationsTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            retrieve: true
        });

    });
</script>