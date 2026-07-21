<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if (empty($admin_id)) {
   header('location:admin_login.php');
   exit();
}

$message = [];

// Multi-Image Upload Handler for General Banners / Events / Promotions
if (isset($_POST['add_slide'])) {

   $title    = trim($_POST['title'] ?? '');
   $subtitle = trim($_POST['subtitle'] ?? '');
   $link     = trim($_POST['link'] ?? '');

   if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
      
      $total_files = count($_FILES['images']['name']);
      $success_count = 0;

      for ($i = 0; $i < $total_files; $i++) {
         $image_name = $_FILES['images']['name'][$i];
         $image_size = $_FILES['images']['size'][$i];
         $image_tmp = $_FILES['images']['tmp_name'][$i];

         if ($image_size > 2000000) {
            $message[] = "Image ($image_name) is larger than 2MB!";
         } else {
            $ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $new_image_name = uniqid('banner_') . '.' . $ext;
            $image_folder = '../uploaded_img/' . $new_image_name;

            $insert_slide = $conn->prepare("INSERT INTO `slider` (title, subtitle, image, link) VALUES (?, ?, ?, ?)");
            $insert_slide->execute([$title, $subtitle, $new_image_name, $link]);

            if ($insert_slide) {
               move_uploaded_file($image_tmp, $image_folder);
               $success_count++;
            }
         }
      }

      if ($success_count > 0) {
         $message[] = "$success_count banner(s) uploaded successfully!";
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
      $file_path = '../uploaded_img/' . $fetch_delete_image['image'];
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
   <title>Manage General Sliders & Announcements</title>
   
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

<!-- Flash Messages -->
<?php if (!empty($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div style="max-width: 900px; margin: 1.5rem auto 0 auto; padding: 1.2rem 2rem; background: #d1fae5; color: #065f46; border-radius: .6rem; font-size: 1.4rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
         <span><i class="fas fa-info-circle" style="margin-right: .5rem;"></i> <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
         <i class="fas fa-times" style="cursor: pointer;" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<section class="add-products">

   <h1 class="heading">Add Hero Banners</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <span>Main Title / Announcement Heading</span>
            <input type="text" class="box" required maxlength="100" placeholder="e.g. SPECIAL PROMOTION OR EVENT" name="title">
         </div>
         <div class="inputBox">
            <span>Subtitle / Details (Numbers, Alphas, Symbols allowed)</span>
            <input type="text" class="box" required maxlength="150" placeholder="e.g. Join our seasonal webinar or flash sale!" name="subtitle">
         </div>
         <div class="inputBox">
            <span>Action Link URL (Optional / Event Page)</span>
            <input type="text" class="box" required maxlength="255" placeholder="e.g. events.php or shop.php" name="link">
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
      <img src="../uploaded_img/<?= htmlspecialchars($fetch_slides['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="Banner Image" style="width: 100%; height: 160px; object-fit: cover; border-radius: .5rem;">
      <div class="name" style="margin-top: 1rem;"><?= htmlspecialchars($fetch_slides['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
      <div style="font-size: 1.3rem; color: #475569; margin: .5rem 0;"><strong>Subtitle:</strong> <?= htmlspecialchars($fetch_slides['subtitle'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
      <div style="font-size: 1.2rem; color: #64748b; word-break: break-all; margin-bottom: 1rem;"><strong>Link:</strong> <?= htmlspecialchars($fetch_slides['link'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
      
      <div class="flex-btn">
         <a href="manage_slider.php?delete=<?= $fetch_slides['id']; ?>" class="delete-btn" onclick="return confirm('Delete this banner/announcement?');">Delete</a>
      </div>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">No banner graphics or announcements uploaded yet!</p>';
      }
   ?>
   
   </div>

</section>

<script src="../js/admin_script.js"></script>

<script>
const imageInput = document.getElementById('imageInput');
const previewContainer = document.getElementById('previewContainer');

let dataTransfer = new DataTransfer();

imageInput.addEventListener('change', (e) => {
   for (let file of e.target.files) {
      dataTransfer.items.add(file);
   }
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

   Array.from(dataTransfer.files).forEach((file, index) => {
      if (index !== indexToRemove) {
         newDT.items.add(file);
      }
   });

   dataTransfer = newDT;
   imageInput.files = dataTransfer.files;
   renderPreviews();
}
</script>

</body>
</html>