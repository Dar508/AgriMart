<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if (empty($admin_id)) {
   header('location:admin_login.php');
   exit();
}

// Fetch Admin Profile Info
$select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

// Metrics Queries
$total_pendings = 0;
$select_pendings = $conn->prepare("SELECT total_price FROM `orders` WHERE payment_status = ?");
$select_pendings->execute(['pending']);
while ($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)) {
   $total_pendings += (float)($fetch_pendings['total_price'] ?? 0);
}

$total_completes = 0;
$select_completes = $conn->prepare("SELECT total_price FROM `orders` WHERE payment_status = ?");
$select_completes->execute(['completed']);
while ($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)) {
   $total_completes += (float)($fetch_completes['total_price'] ?? 0);
}

$select_orders = $conn->prepare("SELECT * FROM `orders` ");
$select_orders->execute();
$number_of_orders = $select_orders->rowCount();

$select_products = $conn->prepare("SELECT * FROM `products` ");
$select_products->execute();
$number_of_products = $select_products->rowCount();

$select_users = $conn->prepare("SELECT * FROM `users` ");
$select_users->execute();
$number_of_users = $select_users->rowCount();

$select_farmers = $conn->prepare("SELECT * FROM `farmers` ");
$select_farmers->execute();
$number_of_farmers = $select_farmers->rowCount();

$select_admins = $conn->prepare("SELECT * FROM `admins` ");
$select_admins->execute();
$number_of_admins = $select_admins->rowCount();

$select_messages = $conn->prepare("SELECT * FROM `messages` ");
$select_messages->execute();
$number_of_messages = $select_messages->rowCount();

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard - AgriMart</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="dashboard" style="padding: 2rem; max-width: 1200px; margin: 0 auto;">

   <div style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 2.5rem; border-radius: 1rem; margin-bottom: 2.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
      <div>
         <h1 style="font-size: 2.8rem; margin-bottom: .5rem; color: #ffffff;">Welcome back, <?= htmlspecialchars($fetch_profile['name'] ?? 'Admin'); ?>! 👋</h1>
         <p style="font-size: 1.5rem; opacity: 0.9; color: #e0e7ff;">Here is what is happening across the AgriMart platform today.</p>
      </div>
      <a href="update_profile.php" class="btn" style="background: #ffffff; color: #059669; font-weight: 700; padding: 1rem 2rem; border-radius: .5rem; text-decoration: none; font-size: 1.4rem;">Update Profile</a>
   </div>

   <h2 style="font-size: 2.2rem; color: var(--black); margin-bottom: 1.5rem; font-weight: 700;">Platform Overview</h2>

   <div class="box-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr)); gap: 2rem;">

      <!-- Registered Farmers -->
      <div class="box" style="background: #fff; padding: 2rem; border-radius: .8rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #10b981; text-align: left;">
         <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
               <h3 style="font-size: 3rem; color: #111827; margin-bottom: .2rem;"><?= $number_of_farmers; ?></h3>
               <p style="font-size: 1.4rem; color: #6b7280; font-weight: 600;">Registered Farmers</p>
            </div>
            <i class="fas fa-tractor" style="font-size: 3rem; color: #10b981; opacity: 0.8;"></i>
         </div>
         <a href="farmers_accounts.php" class="btn" style="display: inline-block; margin-top: 1.5rem; width: 100%; text-align: center; background: #10b981; color: #fff; padding: .8rem; border-radius: .4rem; font-size: 1.3rem;">Manage Farmers</a>
      </div>

      <!-- Customer Accounts -->
      <div class="box" style="background: #fff; padding: 2rem; border-radius: .8rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #3b82f6; text-align: left;">
         <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
               <h3 style="font-size: 3rem; color: #111827; margin-bottom: .2rem;"><?= $number_of_users; ?></h3>
               <p style="font-size: 1.4rem; color: #6b7280; font-weight: 600;">Customer Accounts</p>
            </div>
            <i class="fas fa-users" style="font-size: 3rem; color: #3b82f6; opacity: 0.8;"></i>
         </div>
         <a href="users_accounts.php" class="btn" style="display: inline-block; margin-top: 1.5rem; width: 100%; text-align: center; background: #3b82f6; color: #fff; padding: .8rem; border-radius: .4rem; font-size: 1.3rem;">Manage Users</a>
      </div>

      <!-- Market Products (With Delete & Review Navigation) -->
      <div class="box" style="background: #fff; padding: 2rem; border-radius: .8rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #f59e0b; text-align: left;">
         <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
               <h3 style="font-size: 3rem; color: #111827; margin-bottom: .2rem;"><?= $number_of_products; ?></h3>
               <p style="font-size: 1.4rem; color: #6b7280; font-weight: 600;">Market Products</p>
            </div>
            <i class="fas fa-shopping-basket" style="font-size: 3rem; color: #f59e0b; opacity: 0.8;"></i>
         </div>
         <a href="products.php" class="btn" style="display: inline-block; margin-top: 1.5rem; width: 100%; text-align: center; background: #f59e0b; color: #fff; padding: .8rem; border-radius: .4rem; font-size: 1.3rem;">Manage Products & Reviews</a>
      </div>

      <!-- Pending Orders Revenue -->
      <div class="box" style="background: #fff; padding: 2rem; border-radius: .8rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #ef4444; text-align: left;">
         <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
               <h3 style="font-size: 2.5rem; color: #111827; margin-bottom: .2rem;">Rs. <?= number_format($total_pendings); ?>/-</h3>
               <p style="font-size: 1.4rem; color: #6b7280; font-weight: 600;">Pending Orders</p>
            </div>
            <i class="fas fa-clock" style="font-size: 3rem; color: #ef4444; opacity: 0.8;"></i>
         </div>
         <a href="placed_orders.php" class="btn" style="display: inline-block; margin-top: 1.5rem; width: 100%; text-align: center; background: #ef4444; color: #fff; padding: .8rem; border-radius: .4rem; font-size: 1.3rem;">View Pending Orders</a>
      </div>

      <!-- Completed Orders Revenue -->
      <div class="box" style="background: #fff; padding: 2rem; border-radius: .8rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #10b981; text-align: left;">
         <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
               <h3 style="font-size: 2.5rem; color: #111827; margin-bottom: .2rem;">Rs. <?= number_format($total_completes); ?>/-</h3>
               <p style="font-size: 1.4rem; color: #6b7280; font-weight: 600;">Completed Revenue</p>
            </div>
            <i class="fas fa-check-circle" style="font-size: 3rem; color: #10b981; opacity: 0.8;"></i>
         </div>
         <div style="margin-top: 1.5rem; font-size: 1.3rem; color: #6b7280;">Delivered & Paid</div>
      </div>

      <!-- Unread Messages -->
      <div class="box" style="background: #fff; padding: 2rem; border-radius: .8rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #8b5cf6; text-align: left;">
         <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
               <h3 style="font-size: 3rem; color: #111827; margin-bottom: .2rem;"><?= $number_of_messages; ?></h3>
               <p style="font-size: 1.4rem; color: #6b7280; font-weight: 600;">Unread Messages</p>
            </div>
            <i class="fas fa-envelope" style="font-size: 3rem; color: #8b5cf6; opacity: 0.8;"></i>
         </div>
         <a href="messages.php" class="btn" style="display: inline-block; margin-top: 1.5rem; width: 100%; text-align: center; background: #8b5cf6; color: #fff; padding: .8rem; border-radius: .4rem; font-size: 1.3rem;">See Messages</a>
      </div>

   </div>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>