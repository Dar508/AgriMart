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

   // Update textual fields
   $update_product = $conn->prepare("UPDATE `products` SET name = ?, category = ?, details = ?, price = ?, supplier_price = ?, min_supplier_qty = ? WHERE id = ? AND farmer_id = ?");
   $update_product->execute([$name, $category, $details, $price, $supplier_price, $min_supplier_qty, $update_id, $farmer_id]);

   $message[] = 'Product updated successfully!';

   // Handle Image 01 Replacement
   $old_image_01 = $_POST['old_image_01'] ?? '';
   $image_01     = $_FILES['image_01']['name'] ?? '';
   $image_01_tmp = $_FILES['image_01']['tmp_name'] ?? '';
   $image_01_size= $_FILES['image_01']['size'] ?? 0;

   if (!empty($image_01)) {
      if ($image_01_size > 2000000) {
         $message[] = 'Image 1 size is too large (max 2MB)!';
      } else {
         $ext = strtolower(pathinfo($image_01, PATHINFO_EXTENSION));
         $new_name_01 = 'farmer_' . $farmer_id . '_' . uniqid() . '_1.' . $ext;
         
         $update_img_01 = $conn->prepare("UPDATE `products` SET image_01 = ? WHERE id = ? AND farmer_id = ?");
         $update_img_01->execute([$new_name_01, $update_id, $farmer_id]);
         
         move_uploaded_file($image_01_tmp, '../uploaded_img/' . $new_name_01);
         if (!empty($old_image_01) && file_exists('../uploaded_img/' . $old_image_01)) {
            unlink('../uploaded_img/' . $old_image_01);
         }
         $message[] = 'Primary image updated!';
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
      <input type="hidden" name="old_image_01" value="<?= htmlspecialchars($fetch_products['image_01'], ENT_QUOTES, 'UTF-8'); ?>">

      <div class="image-container" style="text-align: center; margin-bottom: 2rem;">
         <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="" style="height: 200px; object-fit: cover; border-radius: .8rem;">
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
         <option value="fruits" <?= $fetch_products['category'] === 'fruits' ? 'selected' : ''; ?>>Fruits</option>
         <option value="vegetables" <?= $fetch_products['category'] === 'vegetables' ? 'selected' : ''; ?>>Vegetables</option>
         <option value="grains" <?= $fetch_products['category'] === 'grains' ? 'selected' : ''; ?>>Grains & Organic Staples</option>
         <option value="other" <?= $fetch_products['category'] === 'other' ? 'selected' : ''; ?>>Other Products</option>
      </select>

      <span>Replace Primary Image (Optional)</span>
      <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">

      <span>Details</span>
      <textarea name="details" class="box" required cols="30" rows="10"><?= htmlspecialchars($fetch_products['details'], ENT_QUOTES, 'UTF-8'); ?></textarea>

      <div class="flex-btn">
         <input type="submit" name="update" class="btn" value="Save Changes">
         <a href="products.php" class="option-btn">Go Back</a>
      </div>
   </form>

</section>

</body>
</html>