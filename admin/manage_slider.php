<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if(isset($_POST['add_slide'])){

   $sub_heading = $_POST['sub_heading'];
   $sub_heading = filter_var($sub_heading, FILTER_SANITIZE_STRING);
   $heading = $_POST['heading'];
   $heading = filter_var($heading, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../images/'.$image; // Saves directly into your store theme images folder

   if($image_size > 2000000){
      $message[] = 'image size is too large!';
   }else{
      $insert_slide = $conn->prepare("INSERT INTO `slider`(sub_heading, heading, image) VALUES(?,?,?)");
      $insert_slide->execute([$sub_heading, $heading, $image]);

      if($insert_slide){
         move_uploaded_file($image_tmp_name, $image_folder);
         $message[] = 'New slider image added successfully!';
      }
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_slide_image = $conn->prepare("SELECT * FROM `slider` WHERE id = ?");
   $delete_slide_image->execute([$delete_id]);
   $fetch_delete_image = $delete_slide_image->fetch(PDO::FETCH_ASSOC);
   
   if($fetch_delete_image){
      unlink('../images/'.$fetch_delete_image['image']);
   }
   
   $delete_slide = $conn->prepare("DELETE FROM `slider` WHERE id = ?");
   $delete_slide->execute([$delete_id]);
   header('location:manage_slider.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manage Slider</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="add-products">

   <h1 class="heading">Add Hero Slide</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <span>Sub-Heading (e.g., upto 50% off)</span>
            <input type="text" class="box" required maxlength="100" placeholder="Enter sub-heading" name="sub_heading">
         </div>
         <div class="inputBox">
            <span>Main Heading (e.g., latest smartphones)</span>
            <input type="text" class="box" required maxlength="100" placeholder="Enter main heading" name="heading">
         </div>
         <div class="inputBox">
            <span>Banner Image (required)</span>
            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
         </div>
      </div>
      <input type="submit" value="Add Slide" class="btn" name="add_slide">
   </form>

</section>

<section class="show-products">

   <h1 class="heading">Active Slides</h1>

   <div class="box-container">

   <?php
      $show_slides = $conn->prepare("SELECT * FROM `slider`");
      $show_slides->execute();
      if($show_slides->rowCount() > 0){
         while($fetch_slides = $show_slides->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <div class="box">
      <img src="../images/<?= $fetch_slides['image']; ?>" alt="" style="width: 100%; height: auto; object-fit: contain;">
      <div class="name" style="font-size: 1.5rem; color: var(--light-color);"><?= $fetch_slides['sub_heading']; ?></div>
      <div class="price" style="font-size: 2rem; color: var(--black);"><?= $fetch_slides['heading']; ?></div>
      <div class="flex-btn">
         <!-- FIXED: Added the Update option link to connect to update_slider.php -->
         <a href="update_slider.php?update=<?= $fetch_slides['id']; ?>" class="option-btn">Update</a>
         <a href="manage_slider.php?delete=<?= $fetch_slides['id']; ?>" class="delete-btn" onclick="return confirm('Delete this slide banner?');">Delete Slide</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">No slider graphics uploaded yet!</p>';
      }
   ?>
   
   </div>

</section>

<script src="../js/admin_script.js"></script>
   
</body>
</html>