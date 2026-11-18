<?php
// Handle Logout if triggered
if (isset($_GET['logout'])) {
   session_unset();
   session_destroy();
   header('location: farmer_login.php');
   exit();
}
?>

<header class="header" style="position: sticky; top:0; left:0; right:0; background: var(--white); box-shadow: var(--box-shadow); z-index: 1000;">
   <section class="flex" style="display: flex; align-items: center; justify-content: space-between; padding: 1.5rem 4%; max-width: 1200px; margin: 0 auto;">
      
      <a href="dashboard.php" class="logo" style="font-size: 2.2rem; font-weight: bolder; color: var(--black);">Farmer<span>Panel</span></a>

      <nav class="navbar" style="display: flex; gap: 2rem; align-items: center;">
         <a href="dashboard.php" style="font-size: 1.6rem; color: var(--black); text-decoration: none; font-weight: 600;">Dashboard</a>
         <a href="products.php" style="font-size: 1.6rem; color: var(--black); text-decoration: none; font-weight: 600;">Manage Products</a>
         <a href="placed_orders.php" style="font-size: 1.6rem; color: var(--black); text-decoration: none; font-weight: 600;">Incoming Orders</a>
         <a href="messages.php" style="font-size: 1.6rem; color: var(--black); text-decoration: none; font-weight: 600;">Messages</a>
      </nav>

      <div class="icons" style="display: flex; gap: 1.5rem; align-items: center;">
         <div id="menu-btn" class="fas fa-bars" style="font-size: 2rem; cursor: pointer; display: none; color: var(--black);"></div>
         
         <!-- Profile icon directly acts as the logout action -->
         <a href="dashboard.php?logout=1" onclick="return confirm('Logout from the platform?');" title="Logout" style="font-size: 2.2rem; cursor: pointer; color: var(--red); text-decoration: none;">
            <i class="fas fa-user-circle"></i>
         </a>
      </div>

   </section>
</header>