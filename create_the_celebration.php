<?php

include_once 'db_configuration.php';

 
    $title = mysqli_real_escape_string($db, $_POST['title']);
    $description = mysqli_real_escape_string($db,$_POST['description']);
    $resource_type = mysqli_real_escape_string($db,$_POST['resource_type']);
    $celebration_type = mysqli_real_escape_string($db,$_POST['celebration_type']);
	$celebration_date = mysqli_real_escape_string($db,$_POST['celebration_date']);
	$tags = mysqli_real_escape_string($db,$_POST['tags']);
	$resource_url = mysqli_real_escape_string($db,$_POST['resource_url']);
	$img_url = mysqli_real_escape_string($db,$_POST['img_url']);
    $validate = true;

//don't need below code because no image is included in users
   // if($validate){
        

      //  $target_dir = "images/dress_images/";

       // $target_file = $target_dir . basename($_FILES["fileToUpload"]["first_name"]);
       // $uploadOk = 1;
        //$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        // Check if image file is a actual image or fake image
       // if(isset($_POST["submit"])) {
           // $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            //if($check !== false) {
              //  $uploadOk = 1;
          //  } else {
              //  header('location: create_user.php?create_user=fileRealFailed');
              //  $uploadOk = 0;
          //  }
       // }
        // Check if $uploadOk is set to 0 by an error
        //if ($uploadOk == 0) {
            
        // if everything is ok, try to upload file
        //else {
           // if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                
                $sql = "INSERT INTO `celebrations_tbl` ( `title`, `description`, `resource_type`, `celebration_type`, `celebration_date`, `tags`, `resource_url`, `img_url` )
                VALUES ('$title', '$description', '$resource_type', '$celebration_type', '$celebration_date', '$tags', '$resource_url', '$img_url')";
    
                mysqli_query($db, $sql);
                header('location: celebrations.php?create_celebrations=Success');
            
         




?>
