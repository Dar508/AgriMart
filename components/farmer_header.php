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
      <a href="../farmer/dashboard.php" class="logo">Farmer<span>Portal</span></a>

      <nav class="navbar">
         <a href="../farmer/dashboard.php">Dashboard</a>
         <a href="../farmer/products.php">Manage Products</a>
         <a href="../farmer/orders.php">Incoming Orders</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user" onclick="document.querySelector('.profile').classList.toggle('active');"></div>
      </div>

      <div class="profile">
         <?php
            $farmer_name = 'Farmer';
            if (!empty($farmer_id)) {
               $select_profile = $conn->prepare("SELECT name FROM `farmers` WHERE id = ?");
               $select_profile->execute([$farmer_id]);
               $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
               if ($fetch_profile) {
                  $farmer_name = $fetch_profile['name'];
               }
            }
         ?>
         <p><?= htmlspecialchars($farmer_name, ENT_QUOTES, 'UTF-8'); ?> (Farmer)</p>
         
         <a href="../components/farmer_logout.php" class="delete-btn" onclick="return confirm('Logout from the platform?');">logout</a> 
      </div>
   </section>
</header>