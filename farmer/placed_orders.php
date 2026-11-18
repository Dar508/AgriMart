<?php

include '../components/connect.php';

session_start();

$farmer_id = $_SESSION['farmer_id'] ?? '';

if(empty($farmer_id)){
   header('location:farmer_login.php');
   exit();
}

// Handle payment status update
if (isset($_POST['update_payment'])) {
   $order_id = (int)($_POST['order_id'] ?? 0);
   $payment_status = $_POST['payment_status'] ?? '';
   if ($order_id > 0 && in_array($payment_status, ['pending', 'completed'], true)) {
      $check_order = $conn->prepare("SELECT o.id FROM `orders` o JOIN `products` p ON o.total_products LIKE CONCAT('%', p.name, ' (%') WHERE o.id = ? AND p.farmer_id = ? LIMIT 1");
      $check_order->execute([$order_id, $farmer_id]);
      if ($check_order->fetchColumn()) {
         $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
         $update_payment->execute([$payment_status, $order_id]);
         $message[] = 'Payment status updated successfully!';
      } else {
         $message[] = 'That order does not contain your produce.';
      }
   } else {
      $message[] = 'Choose a valid order status.';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Farmer Placed Orders - AgriMart</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/farmer_header.php'; ?>

<section class="orders">

   <h1 class="heading">Orders Containing Your Produce</h1>

   <?php
   if(isset($message)){
      foreach($message as $msg){
         echo '
         <div class="message" style="margin: 0 auto 2rem auto; max-width: 1200px; padding: 1.5rem; background: var(--white); display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; border-bottom: var(--border);">
            <span style="font-size: 1.8rem; color: var(--black);">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</span>
            <i class="fas fa-times" style="font-size: 2rem; color: var(--red); cursor: pointer;" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
   ?>

   <div class="box-container">

   <?php
      // Fetch only orders containing products belonging to this specific logged-in farmer
      $select_orders = $conn->prepare("SELECT DISTINCT o.* FROM `orders` o JOIN `products` p ON o.total_products LIKE CONCAT('%', p.name, ' (%') WHERE p.farmer_id = ? ORDER BY o.id DESC");
      $select_orders->execute([$farmer_id]);

      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p> placed on : <span><?= htmlspecialchars($fetch_orders['placed_on'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> name : <span><?= htmlspecialchars($fetch_orders['name'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> number : <span><?= htmlspecialchars($fetch_orders['number'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> address : <span><?= htmlspecialchars($fetch_orders['address'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> total products : <span><?= htmlspecialchars($fetch_orders['total_products'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> total price : <span>Rs.<?= htmlspecialchars($fetch_orders['total_price'], ENT_QUOTES, 'UTF-8'); ?>/-</span> </p>
      <p> payment method : <span><?= htmlspecialchars($fetch_orders['method'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      
      <form action="" method="post">
         <input type="hidden" name="order_id" value="<?= htmlspecialchars($fetch_orders['id'], ENT_QUOTES, 'UTF-8'); ?>">
         <select name="payment_status" class="select" style="width: 100%; margin: 1rem 0; padding: 1rem; font-size: 1.6rem; border: var(--border); border-radius: .5rem;">
            <option selected disabled><?= htmlspecialchars($fetch_orders['payment_status'], ENT_QUOTES, 'UTF-8'); ?></option>
            <option value="pending">pending</option>
            <option value="completed">completed</option>
         </select>
        <div class="flex-btn">
         <input type="submit" value="update" class="option-btn" name="update_payment">
        </div>
      </form>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">No orders placed for your products yet!</p>';
      }
   ?>

   </div>

</section>

<script src="../js/admin_script.js"></script>
   
</body>
</html>
