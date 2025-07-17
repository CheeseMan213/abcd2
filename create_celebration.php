<?php $page_title = 'Admin > Create New Celebration'; ?>
<?php 
    require 'bin/functions.php';
    require 'db_configuration.php';
    include('header.php'); 
    $page="celebrations_admin.php";
    //verifyLogin($page);

?>

<div class="container">
<style>

    #title {
        text-align: center; 
        color: darkgoldenrod;
}

    #guidance {
        color: grey;
        font-size: 10px;
    }

</style>
    <!--Check the CeremonyCreated and if Failed, display the error message-->
    <?php
    // if(isset($_GET['createQuestion'])){
    //     if($_GET["createQuestion"] == "fileRealFailed"){
    //         echo '<br><h3 align="center" class="bg-danger">FAILURE - Your image is not real, Please Try Again!</h3>';
    //     }
    // }
    // if(isset($_GET['createQuestion'])){
    //     if($_GET["createQuestion"] == "answerFailed"){
    //         echo '<br><h3 align="center" class="bg-danger">FAILURE - Your answer was not one of the choices, Please Try Again!</h3>';
    //     }
    // }
    // if(isset($_GET['createQuestion'])){
    //     if($_GET["createQuestion"] == "fileTypeFailed"){
    //         echo '<br><h3 align="center" class="bg-danger">FAILURE - Your image is not a valid image type (jpg,jpeg,png,gif), Please Try Again!</h3>';
    //     }
    // }
    // if(isset($_GET['createQuestion'])){
    //     if($_GET["createQuestion"] == "fileExistFailed"){
    //         echo '<br><h3 align="center" class="bg-danger">FAILURE - Your image does not exist, Please Try Again!</h3>';
    //     }
    // }
  
    ?>
    <form action="create_the_celebration.php" method="POST" enctype="multipart/form-data">
        <br>
        <h3 id="title">Create New Celebration</h3> <br>
        
        <div>
            <label>Title</label> <br>
            <input style=width:400px class="form-control" type="text" name="first_name" maxlength="100" size="50" required title="Please enter a title"></input>
        </div>
        
        <div>
            <label>Description</label> <br>
            <input style=width:400px class="form-control" type="text" name="description" maxlength="100" size="50" required title="Please enter a description"></input>
        </div>
		
        <div>
            <label>Resource Type</label> <label id="guidance"> </label><br>
            <select style=width:400px class="form-control" id="resource_type" name="resource_type">
                <option value="pdf">PDF</option>
                <option value="ppt">PPT</option>
                <option value="html">HTML</option>
                <option value="image">Image</option>
				<option value="video">Video</option>
				<option value="audio">Audio</option>
            </select>
        </div>
		
        <div>
            <label>Celebration Type</label> <br>
            <select style=width:400px class="form-control" id="celebration_type" name="celebration_type">
                <option value="person">Person</option>
                <option value="event">Event</option>
            </select>
        </div>
		
		<div>
            <label>Celebration Date</label> <br>
            <input style=width:400px class="form-control" placeholder="YYYY-MM-DD" type="text" name="celebration_date" maxlength="100" size="50" required title="Please enter a Celebration Type"></input>
        </div>
		
		<div>
            <label>Tags</label> <br>
            <input style=width:400px class="form-control" type="text" name="tags" maxlength="100" size="50" required title="Please enter tags"></input>
        </div>
		
		<div>
            <label>Resource URL</label> <br>
            <input style=width:400px class="form-control" type="text" name="resource_url" maxlength="100" size="50" required title="Please enter the Resource URL"></input>
        </div>
		
		<div>
            <label>Image URL</label> <br>
            <input style=width:400px class="form-control" type="text" name="img_url" maxlength="100" size="50" required title="Please enter the Image URL"></input>
        </div>
         
        <br><br>
        <div align="center" class="text-left">
            <button type="submit" name="submit" class="btn btn-primary btn-md align-items-center">Create Celebration</button>
        </div>
        <br> <br>

    </form>
</div>

<script>
//var loadFile = function(event) {
	//var image = document.getElementById('output');
	//image.src = URL.createObjectURL(event.target.files[0]);
//};

</script>