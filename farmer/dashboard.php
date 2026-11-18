<?php
include '../components/connect.php';

session_start();

$farmer_id = $_SESSION['farmer_id'] ?? null;

// Redirect unauthenticated users
if (!$farmer_id) {
   header('location: farmer_login.php');
   exit();
}

// Handle Logout securely from dashboard or header request
if (isset($_GET['logout'])) {
   session_unset();
   session_destroy();
   header('location: farmer_login.php');
   exit();
}

// Fetch logged-in farmer's profile info
$select_profile = $conn->prepare("SELECT * FROM `farmers` WHERE id = ?");
$select_profile->execute([$farmer_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
$farmer_name = $fetch_profile['name'] ?? 'Farmer';

// Orders store items as "Name (unit price x qty)" in total_products.
$select_products = $conn->prepare("SELECT name FROM `products` WHERE farmer_id = ?");
$select_products->execute([$farmer_id]);
$farmer_products = $select_products->fetchAll(PDO::FETCH_COLUMN);
$number_of_products = count($farmer_products);

$select_orders = $conn->prepare("SELECT id, payment_status, total_products FROM `orders` ORDER BY id DESC");
$select_orders->execute();
$number_of_orders = 0;
$total_pendings = 0;
$total_completes = 0;
while ($order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
   $farmer_order_total = 0;
   foreach ($farmer_products as $product_name) {
      $pattern = '/' . preg_quote($product_name, '/') . '\\s*\\(([0-9]+(?:\\.[0-9]+)?)\\s*x\\s*([0-9]+)\\)/i';
      if (preg_match_all($pattern, $order['total_products'], $matches, PREG_SET_ORDER)) {
         foreach ($matches as $match) {
            $farmer_order_total += (float)$match[1] * (int)$match[2];
         }
      }
   }
   if ($farmer_order_total > 0) {
      $number_of_orders++;
      if ($order['payment_status'] === 'completed') {
         $total_completes += $farmer_order_total;
      } else {
         $total_pendings += $farmer_order_total;
      }
   }
}

$select_seasonal = $conn->prepare("SELECT id, name, category, image_01 FROM `products` WHERE farmer_id = ? AND is_seasonal = 1 ORDER BY id DESC LIMIT 4");
$select_seasonal->execute([$farmer_id]);
$seasonal_products = $select_seasonal->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Farmer Dashboard - AgriMart</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body style="background-color: #f9fafb;">

<!-- Loads the fixed header component containing navigation, profile dropdown, and logout -->
<?php include '../components/farmer_header.php'; ?>

<section class="dashboard" style="padding: 0 4% 2rem 4%; max-width: 1200px; margin: 0 auto;">

   <div style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 2.5rem; border-radius: 1rem; margin-bottom: 2.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
      <div>
         <h1 style="font-size: 2.8rem; margin-bottom: .5rem; color: #ffffff;">Welcome back, <?= htmlspecialchars($farmer_name, ENT_QUOTES, 'UTF-8'); ?>! 👋</h1>
         <p style="font-size: 1.5rem; opacity: 0.9; color: #e0e7ff;">Here is the overview of your farm products and earnings on AgriMart.</p>
      </div>
      <a href="update_profile.php" class="btn" style="background: #ffffff; color: #059669; font-weight: 700; padding: 1rem 2rem; border-radius: .5rem; text-decoration: none; font-size: 1.4rem;">
         <i class="fas fa-user-edit" style="margin-right: .5rem;"></i> Update Profile
      </a>
   </div>

   <h2 style="font-size: 2.2rem; color: var(--black); margin-bottom: 1.5rem; font-weight: 700;">Farm Overview</h2>

   <div class="box-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr)); gap: 2rem;">

      <!-- Farmer's Products -->
      <div class="box" style="background: #fff; padding: 2rem; border-radius: .8rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #f59e0b; text-align: left;">
         <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
               <h3 style="font-size: 3rem; color: #111827; margin-bottom: .2rem;"><?= $number_of_products; ?></h3>
               <p style="font-size: 1.4rem; color: #6b7280; font-weight: 600;">My Products</p>
            </div>
            <i class="fas fa-shopping-basket" style="font-size: 3rem; color: #f59e0b; opacity: 0.8;"></i>
         </div>
         <a href="products.php" class="btn" style="display: inline-block; margin-top: 1.5rem; width: 100%; text-align: center; background: #f59e0b; color: #fff; padding: .8rem; border-radius: .4rem; font-size: 1.3rem;">Manage Products</a>
      </div>

      <!-- Pending Orders / Revenue -->
      <div class="box" style="background: #fff; padding: 2rem; border-radius: .8rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #ef4444; text-align: left;">
         <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
               <h3 style="font-size: 2.5rem; color: #111827; margin-bottom: .2rem;">Rs. <?= number_format($total_pendings); ?>/-</h3>
               <p style="font-size: 1.4rem; color: #6b7280; font-weight: 600;">Pending Revenue</p>
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
         <div style="margin-top: 1.5rem; font-size: 1.3rem; color: #6b7280;">Delivered & Paid Earnings</div>
      </div>

      <!-- Total Orders Received -->
      <div class="box" style="background: #fff; padding: 2rem; border-radius: .8rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #3b82f6; text-align: left;">
         <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
               <h3 style="font-size: 3rem; color: #111827; margin-bottom: .2rem;"><?= $number_of_orders; ?></h3>
               <p style="font-size: 1.4rem; color: #6b7280; font-weight: 600;">Total Orders</p>
            </div>
            <i class="fas fa-box-open" style="font-size: 3rem; color: #3b82f6; opacity: 0.8;"></i>
         </div>
         <a href="placed_orders.php" class="btn" style="display: inline-block; margin-top: 1.5rem; width: 100%; text-align: center; background: #3b82f6; color: #fff; padding: .8rem; border-radius: .4rem; font-size: 1.3rem;">View All Orders</a>
      </div>

   </div>

   <section style="margin-top:3rem; padding:2rem; background:#fff; border-radius:1rem; box-shadow:0 4px 6px -1px rgba(0,0,0,.1);">
      <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
         <div>
            <h2 style="font-size:2.2rem; color:#111827; margin:0;">Seasonal Produce</h2>
            <p style="font-size:1.4rem; color:#6b7280; margin:.5rem 0 0;">Mark fruit or vegetables seasonal in Manage Products to feature them for shoppers.</p>
         </div>
         <a href="products.php" class="btn" style="font-size:1.4rem;">Manage seasonal listings</a>
      </div>
      <?php if ($seasonal_products): ?>
         <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(18rem,1fr)); gap:1.2rem;">
            <?php foreach ($seasonal_products as $seasonal_product): ?>
               <div style="display:flex; align-items:center; gap:1rem; padding:1rem; border:1px solid #e5e7eb; border-radius:.8rem;">
                  <img src="../uploaded_img/<?= htmlspecialchars($seasonal_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width:6rem; height:6rem; border-radius:.6rem; object-fit:cover;">
                  <div><strong style="font-size:1.5rem; color:#111827;"><?= htmlspecialchars($seasonal_product['name'], ENT_QUOTES, 'UTF-8'); ?></strong><div style="font-size:1.2rem; color:#6b7280;"><?= htmlspecialchars(ucfirst($seasonal_product['category']), ENT_QUOTES, 'UTF-8'); ?> · Seasonal</div></div>
               </div>
            <?php endforeach; ?>
         </div>
      <?php else: ?>
         <p style="font-size:1.4rem; color:#6b7280; margin:0;">No seasonal listings yet. Use Manage seasonal listings to add one.</p>
      <?php endif; ?>
   </section>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
