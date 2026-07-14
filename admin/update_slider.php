<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_POST['update_slide'])){

   $slide_id = $_POST['slide_id'];
   $sub_heading = $_POST['sub_heading'];
   $sub_heading = filter_var($sub_heading, FILTER_SANITIZE_STRING);
   $heading = $_POST['heading'];
   $heading = filter_var($heading, FILTER_SANITIZE_STRING);

   // Update text values first
   $update_slide = $conn->prepare("UPDATE `slider` SET sub_heading = ?, heading = ? WHERE id = ?");
   $update_slide->execute([$sub_heading, $heading, $slide_id]);

   $message[] = 'Slide text updated successfully!';

   // Handle image change if a new file is uploaded
   $old_image = $_POST['old_image'];
   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../images/'.$image;

   if(!empty($image)){
      if($image_size > 2000000){
         $message[] = 'Image size is too large!';
      }else{
         // Update the filename in the database
         $update_image = $conn->prepare("UPDATE `slider` SET image = ? WHERE id = ?");
         $update_image->execute([$image, $slide_id]);
         
         // Move new file and clean up the old file to save storage space
         move_uploaded_file($image_tmp_name, $image_folder);
         if(file_exists('../images/'.$old_image) && $old_image != $image){
            unlink('../images/'.$old_image);
         }
         $message[] = 'Slide banner image replaced successfully!';
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Slider Banner</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="update-product">

   <h1 class="heading">Modify Slider Entry</h1>

   <?php
      $update_id = $_GET['update'];
      $select_slide = $conn->prepare("SELECT * FROM `slider` WHERE id = ?");
      $select_slide->execute([$update_id]);
      if($select_slide->rowCount() > 0){
         while($fetch_slide = $select_slide->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="slide_id" value="<?= $fetch_slide['id']; ?>">
      <input type="hidden" name="old_image" value="<?= $fetch_slide['image']; ?>">
      
      <div class="image-container">
         <div class="main-image">
            <img src="../images/<?= $fetch_slide['image']; ?>" alt="Current Banner" style="max-width:100%; border-radius:.5rem;">
         </div>
      </div>
      
      <span>Edit Sub-Heading text</span>
      <input type="text" name="sub_heading" required class="box" maxlength="100" placeholder="e.g. Upto 50% off" value="<?= $fetch_slide['sub_heading']; ?>">
      
      <span>Edit Main Heading title</span>
      <input type="text" name="heading" required class="box" maxlength="100" placeholder="e.g. Latest Smartphones" value="<?= $fetch_slide['heading']; ?>">
      
      <span>Replace Banner Image (Leave empty to keep current image)</span>
      <input type="file" name="image" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
      
      <div class="flex-btn">
         <input type="submit" name="update_slide" class="btn" value="Save Changes">
         <a href="manage_slider.php" class="option-btn">Cancel / Go Back</a>
      </div>
   </form>
   
   <?php
         }
      }else{
         echo '<p class="empty">No slider settings found for this identifier!</p>';
      }
   ?>

</section>

<script src="../js/admin_script.js"></script>
   
</body>
</html>