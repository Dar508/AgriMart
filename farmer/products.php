<?php

include '../components/connect.php';

session_start();

$message = [];

if (!isset($_SESSION['farmer_id'])) {
   header('location:farmer_login.php');
   exit();
}

$farmer_id = $_SESSION['farmer_id'];

// ==========================================
// ADD PRODUCT HANDLER
// ==========================================
if (isset($_POST['add_product'])) {

   // Input Sanitization
   $name              = trim($_POST['name'] ?? '');
   $price             = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
   $supplier_price    = filter_var($_POST['supplier_price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
   $min_supplier_qty  = (int)($_POST['min_supplier_qty'] ?? 1);
   $category          = trim($_POST['category'] ?? '');
   $details           = trim($_POST['details'] ?? '');

   // Check if product name already exists for this farmer
   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ? AND farmer_id = ?");
   $select_products->execute([$name, $farmer_id]);

   if ($select_products->rowCount() > 0) {
      $message[] = 'You already have a product listed with this name!';
   } else {
      $uploaded_images = [];
      $upload_error    = false;

      if (!empty($_FILES['images']['name'][0])) {
         $total_files = count($_FILES['images']['name']);

         if ($total_files > 3) {
            $message[] = 'You can upload a maximum of 3 images!';
            $upload_error = true;
         } else {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

            for ($i = 0; $i < $total_files; $i++) {
               $raw_name = $_FILES['images']['name'][$i];
               $img_size = $_FILES['images']['size'][$i];
               $img_tmp  = $_FILES['images']['tmp_name'][$i];
               $ext      = strtolower(pathinfo($raw_name, PATHINFO_EXTENSION));

               // Validate File Extension
               if (!in_array($ext, $allowed_extensions)) {
                  $message[] = "Image " . ($i + 1) . " must be JPG, PNG, or WEBP format!";
                  $upload_error = true;
                  break;
               }

               // Validate Size (Max 2MB)
               if ($img_size > 2000000) {
                  $message[] = "Image " . ($i + 1) . " size is too large (max 2MB)!";
                  $upload_error = true;
                  break;
               }

               // Generate Unique Filename to avoid collision
               $unique_name = 'farmer_' . $farmer_id . '_' . uniqid() . '_' . $i . '.' . $ext;
               $img_folder  = '../uploaded_img/' . $unique_name;

               $uploaded_images[$i] = [
                  'name'   => $unique_name,
                  'tmp'    => $img_tmp,
                  'folder' => $img_folder
               ];
            }
         }
      } else {
         $message[] = 'Please select at least one primary product image!';
         $upload_error = true;
      }

      if (!$upload_error) {
         try {
            if (!is_dir('../uploaded_img')) {
               mkdir('../uploaded_img', 0755, true);
            }

            $image_01 = $uploaded_images[0]['name'] ?? '';
            $image_02 = $uploaded_images[1]['name'] ?? '';
            $image_03 = $uploaded_images[2]['name'] ?? '';

            $insert_products = $conn->prepare("INSERT INTO `products`(farmer_id, name, category, details, price, supplier_price, min_supplier_qty, image_01, image_02, image_03) VALUES(?,?,?,?,?,?,?,?,?,?)");
            $insert_products->execute([$farmer_id, $name, $category, $details, $price, $supplier_price, $min_supplier_qty, $image_01, $image_02, $image_03]);

            foreach ($uploaded_images as $img) {
               move_uploaded_file($img['tmp'], $img['folder']);
            }

            $message[] = 'New harvest product listed successfully!';
         } catch (PDOException $e) {
            $message[] = 'Database error: ' . $e->getMessage();
         }
      }
   }
}

// ==========================================
// DELETE PRODUCT HANDLER
// ==========================================
if (isset($_GET['delete'])) {
   $delete_id = (int)$_GET['delete'];
   
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ? AND farmer_id = ?");
   $delete_product_image->execute([$delete_id, $farmer_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);

   if ($fetch_delete_image) {
      // Clean up physical images
      for ($i = 1; $i <= 3; $i++) {
         $img_key = 'image_0' . $i;
         if (!empty($fetch_delete_image[$img_key]) && file_exists('../uploaded_img/' . $fetch_delete_image[$img_key])) {
            unlink('../uploaded_img/' . $fetch_delete_image[$img_key]);
         }
      }

      $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ? AND farmer_id = ?");
      $delete_product->execute([$delete_id, $farmer_id]);
      
      header('location:products.php');
      exit();
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Farmer Products Workspace - AgriMart</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      .preview-container {
         display: flex;
         gap: 1.5rem;
         margin-top: 1rem;
         flex-wrap: wrap;
      }
      .preview-box {
         position: relative;
         width: 110px;
         height: 110px;
         border: 2px solid var(--border-color, #e2e8f0);
         border-radius: 0.8rem;
         overflow: hidden;
         background: #f8fafc;
         box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
      }
      .preview-box img {
         width: 100%;
         height: 100%;
         object-fit: cover;
      }
      .preview-box .badge-label {
         position: absolute;
         bottom: 0;
         left: 0;
         right: 0;
         background: rgba(16, 185, 129, 0.9);
         color: #fff;
         font-size: 1rem;
         text-align: center;
         padding: 0.2rem 0;
         font-weight: bold;
      }
      .preview-box .delete-img-btn {
         position: absolute;
         top: 5px;
         right: 5px;
         background: rgba(239, 68, 68, 0.9);
         color: #fff;
         border-radius: 50%;
         width: 24px;
         height: 24px;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 1.2rem;
         cursor: pointer;
         transition: background 0.2s ease;
         z-index: 10;
      }
      .preview-box .delete-img-btn:hover {
         background: #dc2626;
      }
   </style>
</head>
<body>

<?php include '../components/farmer_header.php'; ?>

<!-- Flash Message Container -->
<?php if (!empty($message) && is_array($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div class="message" style="position: sticky; top:0; max-width: 1200px; margin: 0 auto; padding:2rem; background: var(--white); display:flex; align-items:center; justify-content:space-between; gap:1.5rem; border-bottom: var(--border); z-index: 10000;">
         <span style="font-size: 2rem; color:var(--black);"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
         <i class="fas fa-times" style="font-size: 2.5rem; color:var(--red); cursor:pointer;" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<section class="add-products">
   <h1 class="heading">Upload New Harvest</h1>
   <form action="" method="post" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <span>Product Name (required)</span>
            <input type="text" class="box" required maxlength="100" placeholder="e.g., Organic Red Apples" name="name">
         </div>
         <div class="inputBox">
            <span>Retail Price per kg (NRs) (required)</span>
            <input type="number" step="0.01" min="0" max="999999" class="box" required placeholder="Price for retail users" name="price">
         </div>
         <div class="inputBox">
            <span>Wholesale Price per kg (NRs) (required)</span>
            <input type="number" step="0.01" min="0" max="999999" class="box" required placeholder="Price for bulk suppliers" name="supplier_price">
         </div>
         <div class="inputBox">
            <span>Bulk Minimum Order Qty (kg)</span>
            <input type="number" min="1" max="9999" class="box" required value="10" name="min_supplier_qty">
         </div>
         <div class="inputBox">
            <span>Produce Category (required)</span>
            <select name="category" class="box" required>
               <option value="" selected disabled>Select Category</option>
               <option value="fruits">Fruits</option>
               <option value="vegetables">Vegetables</option>
               <option value="grains">Grains & Organic Staples</option>
               <option value="other">Other Products</option>
            </select>
         </div>
         
         <div class="inputBox">
            <span>Product Images (Select up to 3: 1st will be Primary)</span>
            <input type="file" id="imageInput" name="images[]" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" multiple required onchange="handleFileSelect(event)">
         </div>

         <!-- Live Image Preview Container -->
         <div class="inputBox" style="width: 100%;">
            <span>Selected Images Preview:</span>
            <div id="imagePreviewContainer" class="preview-container">
               <p style="font-size: 1.4rem; color: #64748b; margin-top: 0.5rem;">No images selected yet.</p>
            </div>
         </div>

         <div class="inputBox">
            <span>Product Details / Harvest Conditions (required)</span>
            <textarea name="details" class="box" required placeholder="Enter organic certification info or harvest details..." cols="30" rows="10"></textarea>
         </div>
      </div>
      <input type="submit" value="Publish Produce" class="btn" name="add_product">
   </form>
</section>

<section class="show-products">
   <h1 class="heading">Your Current Stock</h1>
   <div class="box-container">
   <?php
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE farmer_id = ? ORDER BY id DESC");
      $select_products->execute([$farmer_id]);
      if ($select_products->rowCount() > 0) {
         while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) { 
   ?>
   <div class="box">
      <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_products['name'], ENT_QUOTES, 'UTF-8'); ?>">
      <div class="name"><?= htmlspecialchars($fetch_products['name'], ENT_QUOTES, 'UTF-8'); ?></div>
      <div style="font-size: 1.4rem; padding: 0.5rem 0;">Retail: NRs <span><?= htmlspecialchars($fetch_products['price'], ENT_QUOTES, 'UTF-8'); ?></span>/kg</div>
      <div style="font-size: 1.4rem; color: green; padding-bottom: 0.5rem;">
         Wholesale: NRs <span><?= htmlspecialchars($fetch_products['supplier_price'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></span>/kg 
         (Min: <?= htmlspecialchars($fetch_products['min_supplier_qty'] ?? '1', ENT_QUOTES, 'UTF-8'); ?> kg)
      </div>
      <div class="details"><span><?= htmlspecialchars($fetch_products['details'], ENT_QUOTES, 'UTF-8'); ?></span></div>
      <div class="flex-btn">
         <a href="update_product.php?update=<?= htmlspecialchars($fetch_products['id'], ENT_QUOTES, 'UTF-8'); ?>" class="option-btn">Update</a>
         <a href="products.php?delete=<?= htmlspecialchars($fetch_products['id'], ENT_QUOTES, 'UTF-8'); ?>" class="delete-btn" onclick="return confirm('Delete this product entry?');">Delete</a>
      </div>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">No produce listed yet! Use the form above to add your harvest.</p>';
      }
   ?>
   </div>
</section>

<script>
let dt = new DataTransfer();

function handleFileSelect(event) {
   const input = event.target;
   const newFiles = Array.from(event.target.files);

   // Append newly picked files up to maximum limit of 3
   for (let file of newFiles) {
      if (dt.items.length >= 3) {
         alert('Maximum limit reached! You can only upload up to 3 images per product.');
         break;
      }
      dt.items.add(file);
   }

   // Synchronize updated file array back into actual HTML file input
   input.files = dt.files;
   renderPreviews();
}

function removeImage(indexToRemove) {
   const input = document.getElementById('imageInput');
   const newDt = new DataTransfer();

   // Keep all files EXCEPT the target clicked for removal
   Array.from(dt.files).forEach((file, index) => {
      if (index !== indexToRemove) {
         newDt.items.add(file);
      }
   });

   dt = newDt;
   input.files = dt.files; // Update real form element file list
   renderPreviews();
}

function renderPreviews() {
   const container = document.getElementById('imagePreviewContainer');
   container.innerHTML = '';

   if (dt.files.length === 0) {
      container.innerHTML = '<p style="font-size: 1.4rem; color: #64748b; margin-top: 0.5rem;">No images selected yet.</p>';
      return;
   }

   Array.from(dt.files).forEach((file, index) => {
      const reader = new FileReader();

      reader.onload = function(e) {
         const box = document.createElement('div');
         box.className = 'preview-box';

         const delBtn = document.createElement('div');
         delBtn.className = 'delete-img-btn';
         delBtn.innerHTML = '<i class="fas fa-times"></i>';
         delBtn.onclick = () => removeImage(index);

         const img = document.createElement('img');
         img.src = e.target.result;

         const label = document.createElement('div');
         label.className = 'badge-label';
         label.innerText = index === 0 ? 'Primary' : `Image ${index + 1}`;

         box.appendChild(delBtn);
         box.appendChild(img);
         box.appendChild(label);
         container.appendChild(box);
      };

      reader.readAsDataURL(file);
   });
}
</script>

</body>
</html>
