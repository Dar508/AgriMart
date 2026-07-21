<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if (empty($admin_id)) {
   header('location:admin_login.php');
   exit();
}

// Multi-Image Upload Handler
if (isset($_POST['add_slide'])) {

   $sub_heading = filter_var($_POST['sub_heading'], FILTER_SANITIZE_STRING);
   $heading = filter_var($_POST['heading'], FILTER_SANITIZE_STRING);

   if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
      
      $total_files = count($_FILES['images']['name']);
      $success_count = 0;

      for ($i = 0; $i < $total_files; $i++) {
         $image_name = $_FILES['images']['name'][$i];
         $image_name = filter_var($image_name, FILTER_SANITIZE_STRING);
         $image_size = $_FILES['images']['size'][$i];
         $image_tmp = $_FILES['images']['tmp_name'][$i];

         if ($image_size > 2000000) {
            $message[] = "Image ($image_name) is larger than 2MB!";
         } else {
            // Generate unique filename to avoid overwriting existing files
            $ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $new_image_name = uniqid('slide_') . '.' . $ext;
            $image_folder = '../images/' . $new_image_name;

            $insert_slide = $conn->prepare("INSERT INTO `slider` (sub_heading, heading, image) VALUES (?, ?, ?)");
            $insert_slide->execute([$sub_heading, $heading, $new_image_name]);

            if ($insert_slide) {
               move_uploaded_file($image_tmp, $image_folder);
               $success_count++;
            }
         }
      }

      if ($success_count > 0) {
         $message[] = "$success_count slide banner(s) uploaded successfully!";
      }
   } else {
      $message[] = 'Please select at least one image!';
   }
}

// Single Slide Delete Handler
if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_slide_image = $conn->prepare("SELECT * FROM `slider` WHERE id = ?");
   $delete_slide_image->execute([$delete_id]);
   $fetch_delete_image = $delete_slide_image->fetch(PDO::FETCH_ASSOC);

   if ($fetch_delete_image) {
      $file_path = '../images/' . $fetch_delete_image['image'];
      if (file_exists($file_path)) {
         unlink($file_path);
      }
   }

   $delete_slide = $conn->prepare("DELETE FROM `slider` WHERE id = ?");
   $delete_slide->execute([$delete_id]);
   header('location:manage_slider.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manage Slider Banners</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      .preview-container {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
         gap: 1.2rem;
         margin-top: 1.5rem;
         width: 100%;
      }
      .preview-card {
         position: relative;
         border-radius: .6rem;
         overflow: hidden;
         box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
         background: #f8fafc;
         height: 90px;
         border: 2px solid #e2e8f0;
      }
      .preview-card img {
         width: 100%;
         height: 100%;
         object-fit: cover;
      }
      .preview-card .remove-btn {
         position: absolute;
         top: 4px;
         right: 4px;
         background: #ef4444;
         color: #ffffff;
         border: none;
         border-radius: 50%;
         width: 24px;
         height: 24px;
         font-size: 1.4rem;
         font-weight: bold;
         cursor: pointer;
         display: flex;
         align-items: center;
         justify-content: center;
         line-height: 1;
         box-shadow: 0 2px 4px rgba(0,0,0,0.2);
      }
      .preview-card .remove-btn:hover {
         background: #dc2626;
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="add-products">

   <h1 class="heading">Add Hero Banners</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <span>Sub-Heading (e.g., Upto 50% Off)</span>
            <input type="text" class="box" required maxlength="100" placeholder="Enter sub-heading" name="sub_heading">
         </div>
         <div class="inputBox">
            <span>Main Heading (e.g., Fresh Organic Produce)</span>
            <input type="text" class="box" required maxlength="100" placeholder="Enter main heading" name="heading">
         </div>
         <div class="inputBox">
            <span>Banner Images (Choose Multiple)</span>
            <input type="file" id="imageInput" name="images[]" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" multiple required>
         </div>
      </div>

      <!-- Live Previews Grid -->
      <div id="previewContainer" class="preview-container"></div>

      <input type="submit" value="Upload Banner(s)" class="btn" name="add_slide" style="margin-top: 2rem;">
   </form>

</section>

<section class="show-products">

   <h1 class="heading">Active Banners</h1>

   <div class="box-container">

   <?php
      $show_slides = $conn->prepare("SELECT * FROM `slider` ORDER BY id DESC");
      $show_slides->execute();
      if ($show_slides->rowCount() > 0) {
         while ($fetch_slides = $show_slides->fetch(PDO::FETCH_ASSOC)) { 
   ?>
   <div class="box">
      <img src="../images/<?= htmlspecialchars($fetch_slides['image']); ?>" alt="Banner Image" style="width: 100%; height: 180px; object-fit: cover; border-radius: .5rem;">
      <div class="name" style="font-size: 1.4rem; color: var(--light-color); margin-top: 1rem;"><?= htmlspecialchars($fetch_slides['sub_heading']); ?></div>
      <div class="price" style="font-size: 1.8rem; color: var(--black); font-weight: bold; margin-bottom: 1rem;"><?= htmlspecialchars($fetch_slides['heading']); ?></div>
      <div class="flex-btn">
         <a href="update_slider.php?update=<?= $fetch_slides['id']; ?>" class="option-btn">Edit / Replace</a>
         <a href="manage_slider.php?delete=<?= $fetch_slides['id']; ?>" class="delete-btn" onclick="return confirm('Delete this slide banner?');">Delete</a>
      </div>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">No slider graphics uploaded yet!</p>';
      }
   ?>
   
   </div>

</section>

<script src="../js/admin_script.js"></script>

<script>
const imageInput = document.getElementById('imageInput');
const previewContainer = document.getElementById('previewContainer');

// Store files in a DataTransfer object so individual deletions only affect the target file
let dataTransfer = new DataTransfer();

imageInput.addEventListener('change', (e) => {
   // Append newly picked files to current queue
   for (let file of e.target.files) {
      dataTransfer.items.add(file);
   }
   
   // Sync input element files with dataTransfer state
   imageInput.files = dataTransfer.files;
   renderPreviews();
});

function renderPreviews() {
   previewContainer.innerHTML = '';

   Array.from(dataTransfer.files).forEach((file, index) => {
      const reader = new FileReader();

      reader.onload = (e) => {
         const card = document.createElement('div');
         card.classList.add('preview-card');

         card.innerHTML = `
            <img src="${e.target.result}" alt="Preview">
            <button type="button" class="remove-btn" onclick="removeSingleImage(${index})">&times;</button>
         `;

         previewContainer.appendChild(card);
      };

      reader.readAsDataURL(file);
   });
}

function removeSingleImage(indexToRemove) {
   const newDT = new DataTransfer();

   // Copy all files EXCEPT the one clicked for removal
   Array.from(dataTransfer.files).forEach((file, index) => {
      if (index !== indexToRemove) {
         newDT.items.add(file);
      }
   });

   dataTransfer = newDT;
   imageInput.files = dataTransfer.files; // Update actual form input
   renderPreviews(); // Re-render preview grid
}
</script>

</body>
</html>