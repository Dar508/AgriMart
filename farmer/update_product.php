<?php

include '../components/connect.php';

session_start();

$farmer_id = $_SESSION['farmer_id'] ?? '';

if (empty($farmer_id)) {
   header('location:farmer_login.php');
   exit();
}

$update_id = (int)($_GET['update'] ?? 0);

if (!$update_id) {
   header('location:products.php');
   exit();
}

$message = [];

// ==========================================
// UPDATE PRODUCT HANDLER
// ==========================================
if (isset($_POST['update'])) {

   $name             = trim($_POST['name'] ?? '');
   $price            = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
   $supplier_price   = filter_var($_POST['supplier_price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
   $min_supplier_qty = (int)($_POST['min_supplier_qty'] ?? 1);
   $category         = trim($_POST['category'] ?? '');
   $details          = trim($_POST['details'] ?? '');
   $is_seasonal      = isset($_POST['is_seasonal']) ? 1 : 0;

   // Update textual fields
   $update_product = $conn->prepare("UPDATE `products` SET name = ?, category = ?, details = ?, price = ?, supplier_price = ?, min_supplier_qty = ?, is_seasonal = ? WHERE id = ? AND farmer_id = ?");
   $update_product->execute([$name, $category, $details, $price, $supplier_price, $min_supplier_qty, $is_seasonal, $update_id, $farmer_id]);

   $message[] = 'Product details updated successfully!';

   // Handle Image Replacements (Image 01, 02, 03)
   $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

   for ($i = 1; $i <= 3; $i++) {
      $img_key = 'image_0' . $i;
      $old_img = $_POST['old_' . $img_key] ?? '';
      
      if (!empty($_FILES[$img_key]['name'])) {
         $img_name = $_FILES[$img_key]['name'];
         $img_size = $_FILES[$img_key]['size'];
         $img_tmp  = $_FILES[$img_key]['tmp_name'];
         $ext      = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));

         if (!in_array($ext, $allowed_extensions)) {
            $message[] = "Image $i format not allowed (Use JPG, PNG, WEBP)!";
            continue;
         }

         if ($img_size > 2000000) {
            $message[] = "Image $i size exceeds 2MB limit!";
            continue;
         }

         $new_filename = 'farmer_' . $farmer_id . '_' . uniqid() . '_' . $i . '.' . $ext;
         
         // Update DB
         $update_img = $conn->prepare("UPDATE `products` SET $img_key = ? WHERE id = ? AND farmer_id = ?");
         $update_img->execute([$new_filename, $update_id, $farmer_id]);
         
         // Move new file & delete old file if it exists
         move_uploaded_file($img_tmp, '../uploaded_img/' . $new_filename);
         if (!empty($old_img) && file_exists('../uploaded_img/' . $old_img)) {
            unlink('../uploaded_img/' . $old_img);
         }
         
         $message[] = "Image $i updated successfully!";
      }
   }
}

// Fetch current details
$select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? AND farmer_id = ?");
$select_products->execute([$update_id, $farmer_id]);

if ($select_products->rowCount() === 0) {
   header('location:products.php');
   exit();
}

$fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Product - AgriMart</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/farmer_header.php'; ?>

<!-- Flash Messages -->
<?php if (!empty($message) && is_array($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div class="message" style="position: sticky; top:0; max-width: 1200px; margin: 0 auto; padding:2rem; background: var(--white); display:flex; align-items:center; justify-content:space-between; gap:1.5rem; border-bottom: var(--border); z-index: 10000;">
         <span style="font-size: 2rem; color:var(--black);"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
         <i class="fas fa-times" style="font-size: 2.5rem; color:var(--red); cursor:pointer;" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<section class="update-product">

   <h1 class="heading">Update Product Listing</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <!-- Hidden inputs to keep track of old images for deletion upon replacement -->
      <input type="hidden" name="old_image_01" value="<?= htmlspecialchars($fetch_products['image_01'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="old_image_02" value="<?= htmlspecialchars($fetch_products['image_02'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="old_image_03" value="<?= htmlspecialchars($fetch_products['image_03'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

      <!-- Image Previews Container -->
      <div class="image-container" style="display: flex; gap: 2rem; justify-content: center; margin-bottom: 2rem; flex-wrap: wrap;">
         <?php for ($i = 1; $i <= 3; $i++): 
            $img_col = 'image_0' . $i;
            if (!empty($fetch_products[$img_col])):
         ?>
            <div style="text-align: center;">
               <img src="../uploaded_img/<?= htmlspecialchars($fetch_products[$img_col], ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width: 120px; height: 120px; object-fit: cover; border-radius: .8rem; border: var(--border);">
               <p style="font-size: 1.4rem; color: var(--light-color); margin-top: .5rem;"><?= $i === 1 ? 'Primary Image' : 'Image ' . $i ?></p>
            </div>
         <?php endif; endfor; ?>
      </div>

      <span>Product Name</span>
      <input type="text" name="name" required class="box" maxlength="100" placeholder="Product name" value="<?= htmlspecialchars($fetch_products['name'], ENT_QUOTES, 'UTF-8'); ?>">

      <span>Retail Price per kg (NRs)</span>
      <input type="number" name="price" step="0.01" min="0" max="999999" required class="box" placeholder="Retail price" value="<?= htmlspecialchars($fetch_products['price'], ENT_QUOTES, 'UTF-8'); ?>">

      <span>Wholesale Price per kg (NRs)</span>
      <input type="number" name="supplier_price" step="0.01" min="0" max="999999" required class="box" placeholder="Wholesale price" value="<?= htmlspecialchars($fetch_products['supplier_price'], ENT_QUOTES, 'UTF-8'); ?>">

      <span>Bulk Minimum Order Qty (kg)</span>
      <input type="number" name="min_supplier_qty" min="1" max="9999" required class="box" value="<?= htmlspecialchars($fetch_products['min_supplier_qty'], ENT_QUOTES, 'UTF-8'); ?>">

      <span>Category</span>
      <select name="category" class="box" required>
         <option value="fruits" <?= ($fetch_products['category'] ?? '') === 'fruits' ? 'selected' : ''; ?>>Fruits</option>
         <option value="vegetables" <?= ($fetch_products['category'] ?? '') === 'vegetables' ? 'selected' : ''; ?>>Vegetables</option>
         <option value="grains" <?= ($fetch_products['category'] ?? '') === 'grains' ? 'selected' : ''; ?>>Grains & Organic Staples</option>
         <option value="other" <?= ($fetch_products['category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other Products</option>
      </select>

      <span>Replace Primary Image (Optional)</span>
      <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">

      <span>Replace Image 02 (Optional)</span>
      <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">

      <span>Replace Image 03 (Optional)</span>
      <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">

      <span>Details</span>
      <textarea name="details" class="box" required cols="30" rows="10"><?= htmlspecialchars($fetch_products['details'], ENT_QUOTES, 'UTF-8'); ?></textarea>

      <label style="display:flex; align-items:center; gap:.8rem; margin:1rem 0 2rem; font-size:1.5rem; color:var(--black);">
         <input type="checkbox" name="is_seasonal" value="1" <?= !empty($fetch_products['is_seasonal']) ? 'checked' : ''; ?> style="width:1.8rem; height:1.8rem;">
         Feature this as seasonal produce on the customer homepage
      </label>

      <div class="flex-btn">
         <input type="submit" name="update" class="btn" value="Save Changes">
         <a href="products.php" class="option-btn" style="background: var(--light-bg); color: var(--black); text-align: center; padding: 1.2rem 3rem; font-size: 1.8rem; border-radius: .5rem;">Go Back</a>
      </div>
   </form>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
