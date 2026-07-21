<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';
$message = [];

include 'components/wishlist_cart.php';

// Fetch PID safely
$pid = filter_var($_GET['pid'] ?? '', FILTER_SANITIZE_NUMBER_INT);

// ADD REVIEW HANDLE
if (isset($_POST['add_review'])) {

   if (empty($user_id)) {
      header('location:user_login.php');
      exit();
   }

   $review_pid = filter_var($_POST['pid'] ?? '', FILTER_SANITIZE_NUMBER_INT);
   $rating     = filter_var($_POST['rating'] ?? 5, FILTER_SANITIZE_NUMBER_INT);
   $review     = trim($_POST['review'] ?? '');

   if (empty($review)) {
      $message[] = 'Please write a message before submitting your review!';
   } else {
      // Check if the user has already reviewed this product
      $check_review = $conn->prepare("SELECT id FROM `reviews` WHERE pid = ? AND user_id = ?");
      $check_review->execute([$review_pid, $user_id]);

      if ($check_review->rowCount() > 0) {
         $message[] = 'You have already submitted a review for this product!';
      } else {
         // Fetch author name
         $select_user = $conn->prepare("SELECT name FROM `users` WHERE id = ?");
         $select_user->execute([$user_id]);
         $user_name = $select_user->fetch(PDO::FETCH_ASSOC)['name'] ?? 'Anonymous';

         try {
            $insert_review = $conn->prepare("INSERT INTO `reviews` (user_id, user_name, pid, rating, review) VALUES (?, ?, ?, ?, ?)");
            $insert_review->execute([$user_id, $user_name, $review_pid, $rating, $review]);
            $message[] = 'Your review has been submitted successfully!';
         } catch (PDOException $e) {
            $message[] = 'Database error occurred. Please try again.';
         }
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quick View</title>
   
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- System Notification Alerts -->
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

<section class="quick-view">

   <h1 class="heading">Quick View</h1>

   <?php
     $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?"); 
     $select_products->execute([$pid]);

     if ($select_products->rowCount() > 0) {
        while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_product['id']); ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name']); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_product['price']); ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01']); ?>">
      
      <div class="row">
         <div class="image-container">
            <div class="main-image">
               <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" id="main-product-img" alt="Product Image">
            </div>
            <div class="sub-image">
               <?php if (!empty($fetch_product['image_01'])): ?>
                  <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" class="thumb-img" alt="">
               <?php endif; ?>
               <?php if (!empty($fetch_product['image_02'])): ?>
                  <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_02']); ?>" class="thumb-img" alt="">
               <?php endif; ?>
               <?php if (!empty($fetch_product['image_03'])): ?>
                  <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_03']); ?>" class="thumb-img" alt="">
               <?php endif; ?>
            </div>
         </div>
         
         <div class="content">
            <div class="name"><?= htmlspecialchars($fetch_product['name']); ?></div>
            <div class="flex">
               <div class="price"><span>Rs. </span><?= htmlspecialchars($fetch_product['price']); ?><span>/-</span></div>
               <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
            </div>
            <div class="details"><?= htmlspecialchars($fetch_product['details']); ?></div>
            <div class="flex-btn">
               <input type="submit" value="Add To Cart" class="btn" name="add_to_cart">
               <input class="option-btn" type="submit" name="add_to_wishlist" value="Add To Wishlist">
            </div>
         </div>
      </div>
   </form>
   <?php
        }
     } else {
        echo '<p class="empty">No product found!</p>';
     }
   ?>

</section>

<!-- REVIEWS & RATING SECTION -->
<section class="reviews-container">

   <h1 class="heading">Customer Reviews</h1>

   <!-- Write Review Form -->
   <div class="add-review-form">
      <form action="" method="post">
         <input type="hidden" name="pid" value="<?= htmlspecialchars($pid); ?>">
         
         <label class="form-label">Rating:</label>
         <select name="rating" required class="box">
            <option value="5">5 Stars - Excellent Produce</option>
            <option value="4">4 Stars - Very Good</option>
            <option value="3">3 Stars - Average Quality</option>
            <option value="2">2 Stars - Below Expectation</option>
            <option value="1">1 Star - Poor Quality</option>
         </select>

         <label class="form-label">Your Review:</label>
         <textarea name="review" cols="30" rows="5" required placeholder="Write details about product freshness, packaging, or delivery..." class="box"></textarea>

         <input type="submit" value="Submit Review" name="add_review" class="btn">
      </form>
   </div>

   <!-- Display Existing Reviews -->
   <div class="reviews-list">
      <?php
         $select_reviews = $conn->prepare("SELECT * FROM `reviews` WHERE pid = ? ORDER BY id DESC");
         $select_reviews->execute([$pid]);

         if ($select_reviews->rowCount() > 0) {
            while ($fetch_review = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
      ?>
      <div class="review-box">
         <div class="user-info">
            <span class="user-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($fetch_review['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
            <div class="stars">
               <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="fas fa-star" style="color: <?= ($i <= $fetch_review['rating']) ? '#f59e0b' : '#cbd5e1'; ?>;"></i>
               <?php endfor; ?>
            </div>
         </div>
         <p class="review-text"><?= htmlspecialchars($fetch_review['review'], ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
      <?php
            }
         } else {
            echo '<p class="empty">No reviews yet. Be the first to review this product!</p>';
         }
      ?>
   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
<script>
   // Interactive Gallery Thumbnail Switcher
   document.querySelectorAll('.thumb-img').forEach(image => {
      image.onclick = () => {
         document.querySelector('#main-product-img').src = image.getAttribute('src');
      }
   });
</script>

</body>
</html>