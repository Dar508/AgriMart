<?php

include '../components/connect.php';

session_start();

$message = [];

// Enforce farmer session check
if (!isset($_SESSION['farmer_id'])) {
   header('location:farmer_login.php');
   exit();
}

$farmer_id = $_SESSION['farmer_id'];

// ==========================================
// HANDLE ORDER STATUS UPDATES
// ==========================================
if (isset($_POST['update_payment'])) {
   $order_id       = (int)($_POST['order_id'] ?? 0);
   $payment_status = trim($_POST['payment_status'] ?? '');

   // Allowed status options
   $allowed_statuses = ['pending', 'completed'];

   if ($order_id > 0 && in_array($payment_status, $allowed_statuses, true)) {
      // Verify that this order contains produce belonging to this specific farmer
      $check_ownership = $conn->prepare("
         SELECT o.id FROM `orders` o 
         JOIN `products` p ON o.total_products LIKE CONCAT('%', p.name, '%')
         WHERE o.id = ? AND p.farmer_id = ?
      ");
      $check_ownership->execute([$order_id, $farmer_id]);

      if ($check_ownership->rowCount() > 0) {
         $update_status = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
         $update_status->execute([$payment_status, $order_id]);
         $message[] = 'Order status updated successfully!';
      } else {
         $message[] = 'Unauthorized action or invalid order!';
      }
   } else {
      $message[] = 'Please select a valid order status!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Incoming Orders - AgriMart</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/farmer_header.php'; ?>

<!-- Flash Message Display Container -->
<?php if (!empty($message) && is_array($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div class="message" style="position: sticky; top:0; max-width: 1200px; margin: 0 auto; padding:1.5rem 2rem; background: var(--white, #fff); display:flex; align-items:center; justify-content:space-between; gap:1.5rem; border-bottom: var(--border, 1px solid #e2e8f0); z-index: 10000;">
         <span style="font-size: 1.8rem; color:var(--black, #1e293b);"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
         <i class="fas fa-times" style="font-size: 2.2rem; color:var(--red, #e11d48); cursor:pointer;" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<section class="orders">

   <h1 class="heading">Incoming Marketplace Orders</h1>

   <div class="box-container">

   <?php
      /* 
         Cross-references order items against farmer's listed products.
         Ensures farmers only view orders involving their own listed crops.
      */
      $select_orders = $conn->prepare("
         SELECT DISTINCT o.* FROM `orders` o 
         INNER JOIN `products` p ON (o.total_products LIKE CONCAT('%', p.name, '%'))
         WHERE p.farmer_id = ? 
         ORDER BY o.placed_on DESC
      ");
      $select_orders->execute([$farmer_id]);

      if ($select_orders->rowCount() > 0) {
         while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <div class="box">
      <p> Placed On : <span><?= htmlspecialchars($fetch_orders['placed_on'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> Name : <span><?= htmlspecialchars($fetch_orders['name'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> Phone : <span><?= htmlspecialchars($fetch_orders['number'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> Email : <span><?= htmlspecialchars($fetch_orders['email'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> Address : <span><?= htmlspecialchars($fetch_orders['address'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> Ordered Crop/Produce : <span style="color: var(--main-color, #16a34a); font-weight:700;"><?= htmlspecialchars($fetch_orders['total_products'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> Total Value : <span>NRs <?= htmlspecialchars($fetch_orders['total_price'], ENT_QUOTES, 'UTF-8'); ?>/-</span> </p>
      <p> Payment Method : <span><?= htmlspecialchars($fetch_orders['method'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      
      <form action="" method="post">
         <input type="hidden" name="order_id" value="<?= htmlspecialchars($fetch_orders['id'], ENT_QUOTES, 'UTF-8'); ?>">
         
         <select name="payment_status" class="select" style="width: 100%; padding: 1rem; border: 1px solid #cbd5e1; margin-bottom: 1rem; border-radius: .5rem; font-size: 1.6rem; background: #fff;">
            <option value="" disabled selected>Current Status: <?= htmlspecialchars(ucfirst($fetch_orders['payment_status']), ENT_QUOTES, 'UTF-8'); ?></option>
            <option value="pending">Pending Gathering</option>
            <option value="completed">Dispatched / Completed</option>
         </select>
         
         <div class="flex-btn">
            <input type="submit" value="Update Status" class="option-btn" name="update_payment">
         </div>
      </form>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">No incoming orders found for your crops yet!</p>';
      }
   ?>

   </div>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>