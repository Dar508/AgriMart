<?php

include '../components/connect.php';

session_start();

// Track unique vendor session identity instead of the master administrator
$farmer_id = $_SESSION['farmer_id'];

if(!isset($farmer_id)){
   header('location:farmer_login.php');
   exit();
}

if(isset($_POST['add_product'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
   $supplier_price = filter_var($_POST['supplier_price'], FILTER_SANITIZE_STRING);
   $min_supplier_qty = filter_var($_POST['min_supplier_qty'], FILTER_SANITIZE_STRING);
   $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
   $details = filter_var($_POST['details'], FILTER_SANITIZE_STRING);

   $image_01 = filter_var($_FILES['image_01']['name'], FILTER_SANITIZE_STRING);
   $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
   $image_folder_01 = '../uploaded_img/'.$image_01;

   // Check if product name already exists on the platform
   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'product name already exists!';
   }else{
      // CRITICAL: Includes farmer_id to secure ownership data mapping
      $insert_products = $conn->prepare("INSERT INTO `products`(farmer_id, name, category, details, price, supplier_price, min_supplier_qty, image_01) VALUES(?,?,?,?,?,?,?,?)");
      $insert_products->execute([$farmer_id, $name, $category, $details, $price, $supplier_price, $min_supplier_qty, $image_01]);

      if($insert_products){
         move_uploaded_file($image_tmp_name_01, $image_folder_01);
         $message[] = 'new harvest product listed successfully!';
      }
   }
}

// DELETE HANDLE (Only allows a farmer to drop their own inventory lines)
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ? AND farmer_id = ?");
   $delete_product_image->execute([$delete_id, $farmer_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   
   if($fetch_delete_image){
      unlink('../uploaded_img/'.$fetch_delete_image['image_01']);
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
   <link rel="stylesheet" href="../css/admin_style.css"> </head>
<body>

<?php include '../components/farmer_header.php'; ?>

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
      // Securely limits selection to the active logged-in farmer account
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE farmer_id = ?");
      $select_products->execute([$farmer_id]);
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <div class="box">
      <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div style="font-size: 1.4rem; padding: 0.5rem 0;">Retail: NRs<span><?= $fetch_products['price']; ?></span>/kg</div>
      <div style="font-size: 1.4rem; color: green; padding-bottom: 0.5rem;">Wholesale: NRs<span><?= $fetch_products['supplier_price']; ?></span>/kg (Min: <?= $fetch_products['min_supplier_qty']; ?>kg)</div>
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