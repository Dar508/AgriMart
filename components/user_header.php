<?php
   if (isset($message) && is_array($message)) {
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

<header class="header">

   <section class="flex">

      <a href="home.php" class="logo">AgriMart<span>.</span></a>

      <nav class="navbar">
         <a href="home.php">HOME</a>
         <a href="about.php">ABOUT</a>
         <a href="orders.php">ORDERS</a>
         <a href="shop.php">SHOP</a>
         <a href="contact.php">CONTACT</a>
      </nav>

      <div class="icons">
         <?php
            $total_wishlist_counts = 0;
            $total_cart_counts = 0;

            // Only run queries if the user is logged in
            if (!empty($user_id)) {
               $count_wishlist_items = $conn->prepare("SELECT COUNT(*) AS total FROM `wishlist` WHERE user_id = ?");
               $count_wishlist_items->execute([$user_id]);
               $total_wishlist_counts = $count_wishlist_items->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

               $count_cart_items = $conn->prepare("SELECT COUNT(*) AS total FROM `cart` WHERE user_id = ?");
               $count_cart_items->execute([$user_id]);
               $total_cart_counts = $count_cart_items->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            }
         ?>
         <div id="menu-btn" class="fas fa-bars"></div>
         <a href="search_page.php"><i class="fas fa-search"></i></a>
         <a href="wishlist.php"><i class="fas fa-heart"></i><span>(<?= $total_wishlist_counts; ?>)</span></a>
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $total_cart_counts; ?>)</span></a>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php          
            $fetch_profile = null;
            if (!empty($user_id)) {
               $select_profile = $conn->prepare("SELECT name FROM `users` WHERE id = ?");
               $select_profile->execute([$user_id]);
               if ($select_profile->rowCount() > 0) {
                  $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
               }
            }

            if ($fetch_profile) {
         ?>
         <p><?= htmlspecialchars($fetch_profile["name"], ENT_QUOTES, 'UTF-8'); ?></p>
         <a href="update_user.php" class="btn">update profile</a>
         <a href="components/user_logout.php" class="delete-btn" onclick="return confirm('logout from the website?');">logout</a> 
         <?php
            } else {
         ?>
         <p>please login or register first!</p>
         <div class="flex-btn">
            <a href="user_register.php" class="option-btn">register</a>
            <a href="user_login.php" class="option-btn">login</a>
         </div>
         <?php
            }
         ?>      
         
         
      </div>

   </section>

</header>