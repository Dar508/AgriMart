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
   <link rel="stylesheet" href="css/style.css?v=20261119-seasonal">
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
               $displayed_slides = 0;
               while ($fetch_slide = $select_slider->fetch(PDO::FETCH_ASSOC)) {
                  $slide_image = basename((string) ($fetch_slide['image'] ?? ''));
                  if ($slide_image === '' || !is_file(__DIR__ . '/uploaded_img/' . $slide_image)) {
                     continue;
                  }
                  $displayed_slides++;
         ?>
            <div class="swiper-slide slide">
               <div class="image">
                  <img src="uploaded_img/<?= htmlspecialchars($slide_image, ENT_QUOTES, 'UTF-8'); ?>" alt="">
               </div>
               <div class="content">
                  <span><?= htmlspecialchars($fetch_slide['subtitle'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                  <h3><?= htmlspecialchars($fetch_slide['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
                  <a href="shop.php" class="btn">Shop Now</a>
               </div>
            </div>
         <?php
               }
               if ($displayed_slides === 0) {
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

<!-- Seasonal fruit and vegetable listings selected by farmers -->
<section class="seasonal-section">
   <div class="seasonal-intro">
      <div>
         <span class="seasonal-eyebrow"><i class="fas fa-leaf"></i> Fresh from Nepal’s farms</span>
         <h2>Seasonal Produce</h2>
         <p>Discover what local farmers are harvesting right now.</p>
      </div>
      <a class="seasonal-shop-link" href="shop.php">Browse all produce <i class="fas fa-arrow-right"></i></a>
   </div>

   <div class="seasonal-grid">
      <?php
         $select_seasonal_products = $conn->prepare("SELECT * FROM `products` WHERE is_seasonal = 1 ORDER BY id DESC");
         $select_seasonal_products->execute();
         $seasonal_count = 0;
         while ($seasonal_product = $select_seasonal_products->fetch(PDO::FETCH_ASSOC)):
            $seasonal_images = [];
            foreach (['image_01', 'image_02', 'image_03'] as $image_key) {
               $seasonal_image = basename((string) ($seasonal_product[$image_key] ?? ''));
               if ($seasonal_image !== '' && is_file(__DIR__ . '/uploaded_img/' . $seasonal_image)) {
                  $seasonal_images[] = $seasonal_image;
               }
            }
            if (!$seasonal_images) {
               continue;
            }
            $seasonal_count++;
            $seasonal_main_image = $seasonal_images[0];
            $seasonal_retail_price = $seasonal_product['price'];
            $seasonal_supplier_price = $seasonal_product['supplier_price'] ?? 0;
            $seasonal_min_qty = $seasonal_product['min_supplier_qty'] ?? 1;
            $seasonal_display_price = ($user_type === 'supplier' && $seasonal_supplier_price > 0) ? $seasonal_supplier_price : $seasonal_retail_price;
            $seasonal_display_qty = ($user_type === 'supplier') ? $seasonal_min_qty : 1;
      ?>
         <form action="" method="post" class="seasonal-card">
            <input type="hidden" name="pid" value="<?= htmlspecialchars($seasonal_product['id'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="name" value="<?= htmlspecialchars($seasonal_product['name'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="price" value="<?= htmlspecialchars($seasonal_display_price, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="image" value="<?= htmlspecialchars($seasonal_main_image, ENT_QUOTES, 'UTF-8'); ?>" class="seasonal-cart-image">

            <div class="seasonal-card-actions">
               <button class="fas fa-heart" type="submit" name="add_to_wishlist" value="add_to_wishlist" aria-label="Add <?= htmlspecialchars($seasonal_product['name'], ENT_QUOTES, 'UTF-8'); ?> to wishlist"></button>
               <a href="quick_view.php?pid=<?= htmlspecialchars($seasonal_product['id'], ENT_QUOTES, 'UTF-8'); ?>" class="fas fa-eye" aria-label="View <?= htmlspecialchars($seasonal_product['name'], ENT_QUOTES, 'UTF-8'); ?>"></a>
            </div>

            <div class="seasonal-gallery">
               <div class="seasonal-image-frame">
                  <img class="seasonal-main-image" src="uploaded_img/<?= htmlspecialchars($seasonal_main_image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($seasonal_product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                  <span class="seasonal-badge"><i class="fas fa-leaf"></i> In season</span>
               </div>
               <?php if (count($seasonal_images) > 1): ?>
                  <div class="seasonal-thumbnails" aria-label="More photos of <?= htmlspecialchars($seasonal_product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                     <?php foreach ($seasonal_images as $image_index => $seasonal_image): ?>
                        <button type="button" class="seasonal-thumb<?= $image_index === 0 ? ' is-active' : ''; ?>" data-image="<?= htmlspecialchars($seasonal_image, ENT_QUOTES, 'UTF-8'); ?>" aria-label="Show product photo <?= $image_index + 1; ?>" aria-pressed="<?= $image_index === 0 ? 'true' : 'false'; ?>">
                           <img src="uploaded_img/<?= htmlspecialchars($seasonal_image, ENT_QUOTES, 'UTF-8'); ?>" alt="">
                        </button>
                     <?php endforeach; ?>
                  </div>
               <?php endif; ?>
            </div>

            <div class="seasonal-card-info">
               <span class="seasonal-category"><?= htmlspecialchars(ucfirst($seasonal_product['category']), ENT_QUOTES, 'UTF-8'); ?></span>
               <h3><?= htmlspecialchars($seasonal_product['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
               <div class="seasonal-card-bottom">
                  <div class="price"><span>Rs. </span><?= htmlspecialchars($seasonal_display_price, ENT_QUOTES, 'UTF-8'); ?><span>/-</span></div>
                  <label class="seasonal-quantity">Qty
                     <input type="number" name="qty" min="<?= (int) $seasonal_display_qty; ?>" max="999" value="<?= (int) $seasonal_display_qty; ?>">
                  </label>
               </div>
               <input type="submit" value="Add To Cart" class="btn seasonal-add-btn" name="add_to_cart">
            </div>
         </form>
      <?php endwhile; ?>
      <?php if ($seasonal_count === 0): ?>
         <p class="empty seasonal-empty">Farmers have not marked seasonal produce yet.</p>
      <?php endif; ?>
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
            
              $retail_price     = $fetch_product['price'];
              $supplier_price   = $fetch_product['supplier_price'] ?? 0;
              $min_supplier_qty = $fetch_product['min_supplier_qty'] ?? 1;

              if ($user_type === 'supplier') {
                 $display_price = ($supplier_price > 0) ? $supplier_price : $retail_price;
                 $min_qty       = $min_supplier_qty;
                 $badge_html    = '<span class="badge badge-wholesale" style="font-size: 1rem; color: #059669; display: block; font-weight: 600; margin-top: 0.3rem;"><i class="fas fa-tag"></i> Wholesale Tier Active</span>';
              } else {
                 $display_price = $retail_price;
                 $min_qty       = 1;
                 $badge_html    = '';
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

         <?php if ($user_type !== 'supplier' && $supplier_price > 0): ?>
            <div style="font-size: 1.1rem; color: #059669; margin: 0.5rem 0;">
               <i class="fas fa-tags"></i> Bulk Deal: Rs. <?= htmlspecialchars($supplier_price, ENT_QUOTES, 'UTF-8'); ?>/- (Min: <?= htmlspecialchars($min_supplier_qty, ENT_QUOTES, 'UTF-8'); ?> units)
            </div>
         <?php endif; ?>
         
         <div class="flex">
            <div class="price"><span>Rs. </span><?= htmlspecialchars($display_price, ENT_QUOTES, 'UTF-8'); ?><span>/-</span></div>
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

// Let shoppers browse the photos farmers uploaded for each seasonal product.
document.querySelectorAll(".seasonal-card").forEach((card) => {
   const mainImage = card.querySelector(".seasonal-main-image");
   const cartImage = card.querySelector(".seasonal-cart-image");
   card.querySelectorAll(".seasonal-thumb").forEach((thumb) => {
      thumb.addEventListener("click", () => {
         const image = thumb.dataset.image;
         if (!image || !mainImage) return;
         mainImage.src = `uploaded_img/${image}`;
         if (cartImage) cartImage.value = image;
         card.querySelectorAll(".seasonal-thumb").forEach((item) => {
            item.classList.toggle("is-active", item === thumb);
            item.setAttribute("aria-pressed", item === thumb ? "true" : "false");
         });
      });
   });
});

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
