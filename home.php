<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';
$user_type = 'user';
$message = [];

if (!empty($user_id)) {
   try {
      $select_user = $conn->prepare("SELECT user_type FROM `users` WHERE id = ?");
      $select_user->execute([$user_id]);
      $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);
      $user_type = $fetch_user['user_type'] ?? 'user';
   } catch (PDOException $e) {
      $user_type = 'user';
   }
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

   <!-- Swiper CSS -->
   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- Notification Messages -->
<?php
if (!empty($message) && is_array($message)) {
   foreach ($message as $msg) {
      echo '
      <div class="message">
         <span>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<!-- Hero Slider Section -->
<div class="home-bg">
   <section class="home">
      <div class="swiper home-slider">
         <div class="swiper-wrapper">
         <?php
            try {
               $select_slider = $conn->prepare("SELECT * FROM `slider`");
               $select_slider->execute();
               if ($select_slider->rowCount() > 0) {
                  while ($fetch_slide = $select_slider->fetch(PDO::FETCH_ASSOC)) {
         ?>
            <div class="swiper-slide slide">
               <div class="image">
                  <img src="images/<?= htmlspecialchars($fetch_slide['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Banner Image">
               </div>
               <div class="content">
                  <span><?= htmlspecialchars($fetch_slide['sub_heading'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <h3><?= htmlspecialchars($fetch_slide['heading'], ENT_QUOTES, 'UTF-8'); ?></h3>
                  <a href="shop.php" class="btn">Shop Now</a>
               </div>
            </div>
         <?php
                  }
               } else {
                  echo '<p class="empty">No promotional banners active.</p>';
               }
            } catch (PDOException $e) {
               echo '<p class="empty">Welcome to AgriMart Fresh Produce Market</p>';
            }
         ?>
         </div>
         <div class="swiper-pagination"></div>
      </div>
   </section>
</div>

<!-- Shop Category Section -->
<section class="category">

   <h1 class="heading">Shop By Category</h1>

   <div class="swiper category-slider">
      <div class="swiper-wrapper">

         <a href="category.php?category=fruits" class="swiper-slide slide category-card">
            <i class="fas fa-apple-alt icon-fruits"></i>
            <h3>Fruits</h3>
         </a>

         <a href="category.php?category=vegetables" class="swiper-slide slide category-card">
            <i class="fas fa-carrot icon-veggies"></i>
            <h3>Vegetables</h3>
         </a>

         <a href="category.php?category=grains" class="swiper-slide slide category-card">
            <i class="fas fa-seedling icon-grains"></i>
            <h3>Grains & Staples</h3>
         </a>

         <!-- Added Other Products Category -->
         <a href="category.php?category=other" class="swiper-slide slide category-card">
            <i class="fas fa-boxes icon-other"></i>
            <h3>Other Products</h3>
         </a>

      </div>
      <div class="swiper-pagination"></div>
   </div>

</section>

<!-- Latest Products Slider -->
<section class="home-products">

   <h1 class="heading">Latest Products</h1>

   <div class="swiper products-slider">
      <div class="swiper-wrapper">

      <?php
        $select_products = $conn->prepare("SELECT * FROM `products` ORDER BY id DESC LIMIT 6"); 
        $select_products->execute();

        if ($select_products->rowCount() > 0) {
           while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
            
              $supplier_price   = $fetch_product['supplier_price'] ?? $fetch_product['price'];
              $min_supplier_qty = $fetch_product['min_supplier_qty'] ?? 1;

              if ($user_type === 'supplier') {
                 $display_price = $supplier_price;
                 $min_qty       = $min_supplier_qty;
                 $badge_html    = '<span class="badge badge-wholesale">Wholesale Bulk Tier</span>';
              } else {
                 $display_price = $fetch_product['price'];
                 $min_qty       = 1;
                 $badge_html    = '<span class="badge badge-retail">Retail Tier</span>';
              }
      ?>
      <form action="" method="post" class="swiper-slide slide">
         <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>">
         <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?>">
         <input type="hidden" name="price" value="<?= htmlspecialchars($display_price, ENT_QUOTES, 'UTF-8'); ?>">
         <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>">
         
         <button class="fas fa-heart" type="submit" name="add_to_wishlist" value="add_to_wishlist"></button>
         <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>" class="fas fa-eye"></a>
         <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="Product">

         <div class="name">
            <?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?>
            <?= $badge_html; ?>
         </div>
         
         <div class="flex">
            <div class="price"><span>NRS </span><?= htmlspecialchars($display_price, ENT_QUOTES, 'UTF-8'); ?><span>/kg</span></div>
            <input type="number" name="qty" class="qty" min="<?= $min_qty; ?>" max="999" onkeypress="if(this.value.length == 4) return false;" value="<?= $min_qty; ?>">
         </div>
         
         <input type="submit" value="Add To Cart" class="btn" name="add_to_cart">
      </form>
      <?php
           }
        } else {
           echo '<p class="empty">No products added yet!</p>';
        }
      ?>

      </div>
      <div class="swiper-pagination"></div>
   </div>

</section>

<?php include 'components/footer.php'; ?>

<!-- Swiper JS -->
<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>

<script>
const homeSwiper = new Swiper(".home-slider", {
   loop: true,
   spaceBetween: 20,
   pagination: {
      el: ".swiper-pagination",
      clickable: true,
   },
});

const categorySwiper = new Swiper(".category-slider", {
   loop: true,
   spaceBetween: 20,
   pagination: {
      el: ".swiper-pagination",
      clickable: true,
   },
   breakpoints: {
      0: { slidesPerView: 2 },
      650: { slidesPerView: 3 },
      768: { slidesPerView: 4 },
      1024: { slidesPerView: 4 },
   },
});

const productsSwiper = new Swiper(".products-slider", {
   loop: true,
   spaceBetween: 20,
   pagination: {
      el: ".swiper-pagination",
      clickable: true,
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