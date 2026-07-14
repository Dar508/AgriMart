<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
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
         <a href="../farmer/dashboard.php">dashboard</a>
         <a href="../farmer/products.php">manage produce</a>
         <a href="../farmer/orders.php">incoming orders</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user" onclick="document.querySelector('.profile').classList.toggle('active');"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `farmers` WHERE id = ?");
            $select_profile->execute([$farmer_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= $fetch_profile['name']; ?> (Farmer)</p>
         
         <a href="farmer_login.php?action=logout" class="delete-btn" onclick="return confirm('Logout from the platform?');">logout</a> 
      </div>
   </section>
</header>