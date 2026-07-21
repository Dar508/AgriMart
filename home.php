<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
   
   // Safely query user_type in case column exists, otherwise fall back gracefully
   try {
      $select_user_type = $conn->prepare("SELECT user_type FROM `users` WHERE id = ?");
      $select_user_type->execute([$user_id]);
      $fetch_user_type = $select_user_type->fetch(PDO::FETCH_ASSOC);
      $user_type = ($fetch_user_type && isset($fetch_user_type['user_type'])) ? $fetch_user_type['user_type'] : 'user';
   } catch (PDOException $e) {
      $user_type = 'user'; // Fallback if user_type column isn't created yet
   }
}else{
   $user_id = '';
   $user_type = 'user'; // Non-logged-in guest visitors default to retail tier
}

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>AgriMart - Fresh Farm Marketplace</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<div class="home-bg">

<section class="home">

   <div class="swiper home-slider">
   
   <div class="swiper-wrapper">

   <?php
      // Safely fetch slider entries, fallback if table doesn't exist
      try {
         $select_slider = $conn->prepare("SELECT * FROM `slider`");
         $select_slider->execute();
         if($select_slider->rowCount() > 0){
            while($fetch_slide = $select_slider->fetch(PDO::FETCH_ASSOC)){
      ?>
         <div class="swiper-slide slide">
            <div class="image">
               <img src="images/<?= $fetch_slide['image']; ?>" alt="">
            </div>
            <div class="content">
               <span><?= $fetch_slide['sub_heading']; ?></span>
               <h3><?= $fetch_slide['heading']; ?></h3>
               <a href="shop.php" class="btn">shop now</a>
            </div>
         </div>
      <?php
            }
         }else{
            echo '<p class="empty" style="text-align: center; font-size: 2rem; width: 100%; padding: 2rem;">No promotional banner campaigns active.</p>';
         }
      } catch (PDOException $e) {
         echo '<p class="empty" style="text-align: center; font-size: 2rem; width: 100%; padding: 2rem;">Welcome to AgriMart Fresh Produce Market</p>';
      }
   ?>

   </div>

      <div class="swiper-pagination"></div>

   </div>

</section>

</div>

<section class="category">

   <h1 class="heading">shop by category</h1>

   <div class="swiper category-slider">

   <div class="swiper-wrapper">

   <!-- Fruits Section -->
   <a href="category.php?category=fruits" class="swiper-slide slide" style="text-align: center; padding: 2rem;">
      <i class="fas fa-apple-alt" style="font-size: 4.5rem; color: #e74c3c; margin-bottom: 1rem; display: block;"></i>
      <h3>Fruits</h3>
   </a>

   <!-- Vegetables Section -->
   <a href="category.php?category=vegetables" class="swiper-slide slide" style="text-align: center; padding: 2rem;">
      <i class="fas fa-carrot" style="font-size: 4.5rem; color: #e67e22; margin-bottom: 1rem; display: block;"></i>
      <h3>Vegetables</h3>
   </a>

   <!-- Grains & Staples Section -->
   <a href="category.php?category=grains" class="swiper-slide slide" style="text-align: center; padding: 2rem;">
      <i class="fas fa-seedling" style="font-size: 4.5rem; color: #27ae60; margin-bottom: 1rem; display: block;"></i>
      <h3>Grains & Staples</h3>
   </a>

   </div>

   <div class="swiper-pagination"></div>

   </div>

</section>

<section class="home-products">

   <h1 class="heading">latest products</h1>

   <div class="swiper products-slider">

   <div class="swiper-wrapper">

   <?php
     $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 6"); 
     $select_products->execute();
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
         
         // Safely handle wholesale/supplier tier column check
         $supplier_price = isset($fetch_product['supplier_price']) ? $fetch_product['supplier_price'] : $fetch_product['price'];
         $min_supplier_qty = isset($fetch_product['min_supplier_qty']) ? $fetch_product['min_supplier_qty'] : 1;

         if($user_type == 'supplier'){
            $display_price = $supplier_price;
            $min_qty = $min_supplier_qty;
            $badge = '<span class="badge" style="color: green; font-size: 1.2rem; display: block; margin-top: .5rem;">Wholesale Bulk Tier</span>';
         } else {
            $display_price = $fetch_product['price'];
            $min_qty = 1;
            $badge = '<span class="badge" style="color: orange; font-size: 1.2rem; display: block; margin-top: .5rem;">Retail Tier</span>';
         }
   ?>
   <form action="" method="post" class="swiper-slide slide">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
      <input type="hidden" name="price" value="<?= $display_price; ?>">
      <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
      
      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
      
      <div class="name"><?= $fetch_product['name']; ?> <?= $badge; ?></div>
      <div class="flex">
         <div class="price"><span>NRS </span><?= $display_price; ?><span>/kg</span></div>
         <input type="number" name="qty" class="qty" min="<?= $min_qty; ?>" max="999" onkeypress="if(this.value.length == 4) return false;" value="<?= $min_qty; ?>">
      </div>
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">No products added yet!</p>';
   }
   ?>

   </div>

   <div class="swiper-pagination"></div>

   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>

<script>
var swiper = new Swiper(".home-slider", {
   loop:true,
   spaceBetween: 20,
   pagination: {
      el: ".swiper-pagination",
      clickable:true,
    },
});

var swiper = new Swiper(".category-slider", {
   loop:true,
   spaceBetween: 20,
   pagination: {
      el: ".swiper-pagination",
      clickable:true,
   },
   breakpoints: {
      0: { slidesPerView: 2 },
      650: { slidesPerView: 3 },
      768: { slidesPerView: 3 },
      1024: { slidesPerView: 3 },
   },
});

var swiper = new Swiper(".products-slider", {
   loop:true,
   spaceBetween: 20,
   pagination: {
      el: ".swiper-pagination",
      clickable:true,
   },
   breakpoints: {
      550: { slidesPerView: 2 },
      768: { slidesPerView: 2 },
      1024: { slidesPerView: 3 },
   },
});
</script>

</body>
</html>