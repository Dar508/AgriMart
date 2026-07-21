<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if (empty($admin_id)) {
   header('location:admin_login.php');
   exit();
}

// ==========================================
// ADMIN DELETE PRODUCT HANDLER
// ==========================================
if (isset($_GET['delete'])) {
   $delete_id = (int)$_GET['delete'];

   // Fetch product details to clean up physical images
   $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $select_product->execute([$delete_id]);
   $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);

   if ($fetch_product) {
      for ($i = 1; $i <= 3; $i++) {
         $img_key = 'image_0' . $i;
         if (!empty($fetch_product[$img_key]) && file_exists('../uploaded_img/' . $fetch_product[$img_key])) {
            unlink('../uploaded_img/' . $fetch_product[$img_key]);
         }
      }

      // Delete associated product record
      $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
      $delete_product->execute([$delete_id]);

      // Delete associated reviews using correct foreign key 'pid'
      $delete_reviews = $conn->prepare("DELETE FROM `reviews` WHERE pid = ?");
      $delete_reviews->execute([$delete_id]);

      header('location:products.php');
      exit();
   }
}

// Optional: Delete Individual Review
if (isset($_GET['delete_review'])) {
   $review_id = (int)$_GET['delete_review'];
   $delete_rev = $conn->prepare("DELETE FROM `reviews` WHERE id = ?");
   $delete_rev->execute([$review_id]);
   header('location:products.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manage Market Products & Reviews - Admin</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      .reviews-box {
         margin-top: 1rem;
         background: #f8fafc;
         border: 1px solid #e2e8f0;
         border-radius: .5rem;
         padding: 1rem;
         max-height: 200px;
         overflow-y: auto;
      }
      .review-item {
         border-bottom: 1px dashed #cbd5e1;
         padding: .5rem 0;
      }
      .review-item:last-child {
         border-bottom: none;
      }
      .rating-stars {
         color: #f59e0b;
         font-size: 1.2rem;
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="show-products">
   <h1 class="heading">All Market Products & Reviews</h1>

   <div class="box-container">
   <?php
      $select_products = $conn->prepare("SELECT p.*, f.name AS farmer_name FROM `products` p LEFT JOIN `farmers` f ON p.farmer_id = f.id ORDER BY p.id DESC");
      $select_products->execute();

      if ($select_products->rowCount() > 0) {
         while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) { 
            $product_id = $fetch_products['id'];
   ?>
   <div class="box">
      <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_01'] ?? ''); ?>" alt="" style="width: 100%; height: 180px; object-fit: cover; border-radius: .5rem;">
      <div class="name"><?= htmlspecialchars($fetch_products['name'] ?? ''); ?></div>
      <div style="font-size: 1.3rem; color: #6b7280; padding-top: .5rem;">Listed by: <strong><?= htmlspecialchars($fetch_products['farmer_name'] ?? 'Unknown Farmer'); ?></strong></div>
      <div style="font-size: 1.4rem; padding: .5rem 0;">Retail: NRs <span><?= htmlspecialchars($fetch_products['price'] ?? '0'); ?></span>/kg</div>
      
      <!-- Product Reviews Header & Display -->
      <div class="reviews-box">
         <strong style="font-size: 1.3rem; color: #1e293b;">Customer Reviews:</strong>
         <?php
            // Updated query to use 'pid' instead of 'product_id'
            $select_reviews = $conn->prepare("SELECT r.*, u.name AS user_name FROM `reviews` r LEFT JOIN `users` u ON r.user_id = u.id WHERE r.pid = ? ORDER BY r.id DESC");
            $select_reviews->execute([$product_id]);

            if ($select_reviews->rowCount() > 0) {
               while ($fetch_review = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
         ?>
            <div class="review-item">
               <div style="display: flex; justify-content: space-between; align-items: center;">
                  <span style="font-size: 1.2rem; font-weight: bold;"><?= htmlspecialchars($fetch_review['user_name'] ?? 'Anonymous'); ?></span>
                  <div class="rating-stars">
                     <?php
                        $rating = (int)($fetch_review['rating'] ?? 5);
                        for ($i = 1; $i <= 5; $i++) {
                           echo $i <= $rating ? '★' : '☆';
                        }
                     ?>
                  </div>
               </div>
               <!-- Updated field name from 'comment' to 'review' to match database schema -->
               <p style="font-size: 1.2rem; color: #475569; margin-top: .2rem;"><?= htmlspecialchars($fetch_review['review'] ?? ''); ?></p>
               <a href="products.php?delete_review=<?= $fetch_review['id']; ?>" onclick="return confirm('Delete this user review?');" style="color: #ef4444; font-size: 1.1rem; text-decoration: underline;">Remove Review</a>
            </div>
         <?php
               }
            } else {
               echo '<p style="font-size: 1.2rem; color: #94a3b8; margin-top: .5rem;">No reviews posted yet.</p>';
            }
         ?>
      </div>

      <div class="flex-btn" style="margin-top: 1.5rem;">
         <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this product and all its reviews?');">Delete Product</a>
      </div>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">No market products available to manage!</p>';
      }
   ?>
   </div>
</section>

<script src="../js/admin_script.js"></script>

</body>
</html>