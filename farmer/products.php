<?php

include '../components/connect.php';

session_start();

// Initialize message array
$message = [];

if(!isset($_SESSION['farmer_id'])){
   header('location:farmer_login.php');
   exit();
}

$farmer_id = $_SESSION['farmer_id'];

if(isset($_POST['add_product'])){

   // ✅ Modern PHP 8+ Sanitization
   $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
   $price = htmlspecialchars($_POST['price'], ENT_QUOTES, 'UTF-8');
   $supplier_price = htmlspecialchars($_POST['supplier_price'], ENT_QUOTES, 'UTF-8');
   $min_supplier_qty = htmlspecialchars($_POST['min_supplier_qty'], ENT_QUOTES, 'UTF-8');
   $category = htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8');
   $details = htmlspecialchars($_POST['details'], ENT_QUOTES, 'UTF-8');

   // Image 1 Handle
   $image_01 = htmlspecialchars($_FILES['image_01']['name'], ENT_QUOTES, 'UTF-8');
   $image_01 = filter_var($image_01, FILTER_DEFAULT);
   $image_size_01 = $_FILES['image_01']['size'];
   $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
   $image_folder_01 = '../uploaded_img/'.$image_01;

   // Defaults for optional secondary images
   $image_02 = '';
   $image_03 = '';

   // Check if product name already exists
   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'Product name already exists!';
   }else{
      if($image_size_01 > 2000000){
         $message[] = 'Image size is too large (max 2MB)!';
      }else{
         try {
            if(!is_dir('../uploaded_img')){
               mkdir('../uploaded_img', 0755, true);
            }

            // Explicitly pass empty strings for image_02 and image_03 to prevent MySQL errors
            $insert_products = $conn->prepare("INSERT INTO `products`(farmer_id, name, category, details, price, supplier_price, min_supplier_qty, image_01, image_02, image_03) VALUES(?,?,?,?,?,?,?,?,?,?)");
            $insert_products->execute([$farmer_id, $name, $category, $details, $price, $supplier_price, $min_supplier_qty, $image_01, $image_02, $image_03]);

            move_uploaded_file($image_tmp_name_01, $image_folder_01);
            $message[] = 'New harvest product listed successfully!';
         } catch (PDOException $e) {
            $message[] = 'Database error: ' . $e->getMessage();
         }
      }
   }
}

// DELETE HANDLE
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ? AND farmer_id = ?");
   $delete_product_image->execute([$delete_id, $farmer_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   
   if($fetch_delete_image){
      if(file_exists('../uploaded_img/'.$fetch_delete_image['image_01'])){
         unlink('../uploaded_img/'.$fetch_delete_image['image_01']);
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
   <title>Farmer Products Workspace</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/farmer_header.php'; ?>

<?php
if(!empty($message) && is_array($message)){
   foreach($message as $msg){
      echo '
      <div class="message" style="position: sticky; top:0; max-width: 1200px; margin: 0 auto; padding:2rem; background: var(--white); display:flex; align-items:center; justify-content:space-between; gap:1.5rem; border-bottom: var(--border); z-index: 10000;">
         <span style="font-size: 2rem; color:var(--black);">'.$msg.'</span>
         <i class="fas fa-times" style="font-size: 2.5rem; color:var(--red); cursor:pointer;" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="add-products">
   <h1 class="heading">Upload New Harvest</h1>
   <form action="" method="post" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <span>Product Name (required)</span>
            <input type="text" class="box" required maxlength="100" placeholder="e.g., Organic Red Apples" name="name">
         </div>
         <div class="inputBox">
            <span>Retail Price per kg (NRS) (required)</span>
            <input type="number" min="0" max="999999" class="box" required placeholder="Price for normal users" name="price">
         </div>
         <div class="inputBox">
            <span>Wholesale Price per kg (NRS) (required)</span>
            <input type="number" min="0" max="999999" class="box" required placeholder="Price for bulk suppliers" name="supplier_price">
         </div>
         <div class="inputBox">
            <span>Bulk Minimum Order Qty (kg)</span>
            <input type="number" min="1" max="9999" class="box" required value="10" name="min_supplier_qty">
         </div>
         <div class="inputBox">
            <span>Produce Category (required)</span>
            <select name="category" class="box" required>
               <option value="" selected disabled>select category</option>
               <option value="fruits">Fruits</option>
               <option value="vegetables">Vegetables</option>
               <option value="grains">Grains & Organic Staples</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Product Image (required)</span>
            <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
         </div>
         <div class="inputBox">
            <span>Product Details / Harvest Conditions (required)</span>
            <textarea name="details" class="box" required placeholder="Enter organic certification info or collection details..." cols="30" rows="10"></textarea>
         </div>
      </div>
      <input type="submit" value="Publish Produce" class="btn" name="add_product">
   </form>
</section>

<section class="show-products">
   <h1 class="heading">Your Current Stock</h1>
   <div class="box-container">
   <?php
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE farmer_id = ?");
      $select_products->execute([$farmer_id]);
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <div class="box">
      <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div style="font-size: 1.4rem; padding: 0.5rem 0;">Retail: NRs <span><?= $fetch_products['price']; ?></span>/kg</div>
      <div style="font-size: 1.4rem; color: green; padding-bottom: 0.5rem;">Wholesale: NRs <span><?= isset($fetch_products['supplier_price']) ? $fetch_products['supplier_price'] : '0'; ?></span>/kg (Min: <?= isset($fetch_products['min_supplier_qty']) ? $fetch_products['min_supplier_qty'] : '1'; ?>kg)</div>
      <div class="details"><span><?= $fetch_products['details']; ?></span></div>
      <div class="flex-btn">
         <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">Update</a>
         <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Delete this product entry?');">Delete</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">No produce listed yet! Use the form above to add your harvest.</p>';
      }
   ?>
   </div>
</section>

</body>
</html>