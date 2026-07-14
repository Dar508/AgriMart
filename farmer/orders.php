<?php

include '../components/connect.php';

session_start();

$farmer_id = $_SESSION['farmer_id'];

if(!isset($farmer_id)){
   header('location:farmer_login.php');
   exit();
}

// Handle Order Status Updates from the Farmer
if(isset($_POST['update_payment'])){
   $order_id = $_POST['order_id'];
   $payment_status = filter_var($_POST['payment_status'], FILTER_SANITIZE_STRING);
   
   $update_status = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_status->execute([$payment_status, $order_id]);
   $message[] = 'Order status has been updated successfully!';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Incoming Farmer Orders</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/farmer_header.php'; ?>

<section class="orders">

   <h1 class="heading">Incoming Marketplace Orders</h1>

   <div class="box-container">

   <?php
      /* This query dynamically cross-references ordered items with your specific farmer ID.
         It ensures Farmers can only see and manage orders for their own listed produce.
      */
      $select_orders = $conn->prepare("
         SELECT DISTINCT o.* FROM `orders` o 
         JOIN `products` p ON (o.total_products LIKE CONCAT('%', p.name, '%'))
         WHERE p.farmer_id = ? 
         ORDER BY o.placed_on DESC
      ");
      $select_orders->execute([$farmer_id]);

      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p> Placed On : <span><?= $fetch_orders['placed_on']; ?></span> </p>
      <p> Name : <span><?= $fetch_orders['name']; ?></span> </p>
      <p> Phone : <span><?= $fetch_orders['number']; ?></span> </p>
      <p> Email : <span><?= $fetch_orders['email']; ?></span> </p>
      <p> Address : <span><?= $fetch_orders['address']; ?></span> </p>
      <p> Ordered Crop/Produce : <span style="color: var(--main-color); font-weight:700;"><?= $fetch_orders['total_products']; ?></span> </p>
      <p> Total Earnings : <span>NRs <?= $fetch_orders['total_price']; ?>/-</span> </p>
      <p> Payment Method : <span><?= $fetch_orders['method']; ?></span> </p>
      
      <form action="" method="post">
         <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
         <select name="payment_status" class="select" style="width: 100%; padding: 1rem; border: 1px solid #cbd5e1; margin-bottom: 1rem; border-radius: .5rem; font-size: 1.6rem;">
            <option value="" selected disabled><?= $fetch_orders['payment_status']; ?></option>
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
      }else{
         echo '<p class="empty">No incoming orders found for your crops yet!</p>';
      }
   ?>

   </div>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>