<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';

// Handle Order Cancellation
if (isset($_POST['cancel_order'])) {
   if (empty($user_id)) {
      header('location:user_login.php');
      exit();
   }

   $order_id = filter_var($_POST['order_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

   // Verify order belongs to this user and is still pending
   $check_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ? AND payment_status = 'pending'");
   $check_order->execute([$order_id, $user_id]);
   $order = $check_order->fetch(PDO::FETCH_ASSOC);

   if ($order) {
      // 1. Update order status to canceled
      $update_order = $conn->prepare("UPDATE `orders` SET payment_status = 'canceled' WHERE id = ?");
      $update_order->execute([$order_id]);

      // 2. Restock product quantities back to inventory
      // Format expected: "Product Name (Qty) , Product Name (Qty)"
      $products_list = explode(' , ', $order['total_products']);
      foreach ($products_list as $product_item) {
         if (preg_match('/^(.*)\s\((\d+)\)$/', trim($product_item), $matches)) {
            $product_name = trim($matches[1]);
            $quantity = (int)$matches[2];

            $restock = $conn->prepare("UPDATE `products` SET stock = stock + ? WHERE name = ?");
            $restock->execute([$quantity, $product_name]);
         }
      }

      $message[] = 'Order canceled successfully and inventory updated!';
   } else {
      $message[] = 'Order cannot be canceled as it is already processed or completed!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Orders</title>
   
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="orders">

   <h1 class="heading">Placed Orders</h1>

   <div class="box-container">

   <?php
      if (empty($user_id)) {
         echo '<p class="empty">Please login to see your orders.</p>';
      } else {
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY id DESC");
         $select_orders->execute([$user_id]);

         if ($select_orders->rowCount() > 0) {
            while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
               $payment_status = $fetch_orders['payment_status'] ?? 'pending';
               
               // Dynamic status styling
               if ($payment_status === 'pending') {
                  $status_color = 'red';
               } elseif ($payment_status === 'canceled') {
                  $status_color = 'gray';
               } else {
                  $status_color = 'green';
               }
   ?>
   <div class="box">
      <p>Placed on : <span><?= htmlspecialchars($fetch_orders['placed_on'] ?? ''); ?></span></p>
      <p>Name : <span><?= htmlspecialchars($fetch_orders['name'] ?? ''); ?></span></p>
      <p>Email : <span><?= htmlspecialchars($fetch_orders['email'] ?? ''); ?></span></p>
      <p>Number : <span><?= htmlspecialchars($fetch_orders['number'] ?? ''); ?></span></p>
      <p>Address : <span><?= htmlspecialchars($fetch_orders['address'] ?? ''); ?></span></p>
      <p>Payment Method : <span><?= htmlspecialchars($fetch_orders['method'] ?? ''); ?></span></p>
      <p>Your Orders : <span><?= htmlspecialchars($fetch_orders['total_products'] ?? ''); ?></span></p>
      <p>Total Price : <span>NRs <?= htmlspecialchars($fetch_orders['total_price'] ?? '0'); ?>/-</span></p>
      <p>Payment Status : <span style="color: <?= $status_color; ?>; font-weight: 700; text-transform: capitalize;"><?= htmlspecialchars($payment_status); ?></span></p>

      <!-- Cancel Order Button: Rendered only if status is pending -->
      <?php if ($payment_status === 'pending'): ?>
         <form action="" method="post" onsubmit="return confirm('Are you sure you want to cancel this order?');">
            <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
            <input type="submit" value="Cancel Order" name="cancel_order" class="delete-btn" style="margin-top: 1rem; width: 100%;">
         </form>
      <?php endif; ?>
   </div>
   <?php
            }
         } else {
            echo '<p class="empty">No orders placed yet!</p>';
         }
      }
   ?>

   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>