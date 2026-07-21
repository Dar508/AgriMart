<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';

if (empty($user_id)) {
   header('location:user_login.php');
   exit();
}

$message = [];

if (isset($_POST['order'])) {

   // Trim and validate raw input
   $name     = trim($_POST['name'] ?? '');
   $number   = trim($_POST['number'] ?? '');
   $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
   $method   = trim($_POST['method'] ?? '');
   
   $flat     = trim($_POST['flat'] ?? '');
   $street   = trim($_POST['street'] ?? '');
   $city     = trim($_POST['city'] ?? '');
   $state    = trim($_POST['state'] ?? '');
   $country  = trim($_POST['country'] ?? '');
   $pin_code = trim($_POST['pin_code'] ?? '');

   $address  = 'flat no. ' . $flat . ', ' . $street . ', ' . $city . ', ' . $state . ', ' . $country . ' - ' . $pin_code;

   if (!$email) {
      $message[] = 'Invalid email address provided!';
   } else {
      // Re-verify cart and recalculate totals server-side (Prevents Tampering)
      $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $check_cart->execute([$user_id]);

      if ($check_cart->rowCount() > 0) {
         $grand_total = 0;
         $cart_products = [];

         while ($cart_item = $check_cart->fetch(PDO::FETCH_ASSOC)) {
            $cart_products[] = $cart_item['name'] . ' (' . $cart_item['price'] . ' x ' . $cart_item['quantity'] . ') ';
            $grand_total += ($cart_item['price'] * $cart_item['quantity']);
         }

         $total_products = implode(', ', $cart_products);

         // Secure Insertion via Prepared Statements
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $grand_total]);

         // Clear cart after order confirmation
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);

         $message[] = 'Order placed successfully!';
      } else {
         $message[] = 'Your cart is empty!';
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>
   
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- Display Alerts -->
<?php
if (!empty($message) && is_array($message)) {
   foreach ($message as $msg_text) {
      echo '
      <div class="message">
         <span>' . htmlspecialchars($msg_text, ENT_QUOTES, 'UTF-8') . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="checkout-orders">

   <form action="" method="POST">

      <h3>Your Orders</h3>

      <div class="display-orders">
      <?php
         $grand_total = 0;
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);

         if ($select_cart->rowCount() > 0) {
            while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
               $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
               $grand_total += $sub_total;
      ?>
         <p> 
            <?= htmlspecialchars($fetch_cart['name'], ENT_QUOTES, 'UTF-8'); ?> 
            <span>(NRs <?= htmlspecialchars($fetch_cart['price'], ENT_QUOTES, 'UTF-8'); ?>/- x <?= htmlspecialchars($fetch_cart['quantity'], ENT_QUOTES, 'UTF-8'); ?>)</span> 
         </p>
      <?php
            }
         } else {
            echo '<p class="empty">Your cart is empty!</p>';
         }
      ?>
         <div class="grand-total">Grand Total : <span>NRs <?= htmlspecialchars($grand_total, ENT_QUOTES, 'UTF-8'); ?>/-</span></div>
      </div>

      <h3>Place Your Orders</h3>

      <div class="flex">
         <div class="inputBox">
            <span>Your Name :</span>
            <input type="text" name="name" placeholder="Enter your name" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Your Phone Number :</span>
            <input type="tel" name="number" placeholder="Enter your number" class="box" pattern="[0-9]{7,15}" onkeypress="if(this.value.length == 10) return false;" required>
         </div>
         <div class="inputBox">
            <span>Your Email :</span>
            <input type="email" name="email" placeholder="Enter your email" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Payment Method :</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Cash On Delivery</option>
               <option value="esewa">eSewa</option>
               <option value="khalti">Khalti</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Address Line 01 :</span>
            <input type="text" name="flat" placeholder="e.g. Flat / House Number" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Address Line 02 :</span>
            <input type="text" name="street" placeholder="e.g. Street Name / Ward No." class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>City :</span>
            <input type="text" name="city" placeholder="e.g. Pokhara" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>State :</span>
            <input type="text" name="state" placeholder="e.g. Gandaki" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Country :</span>
            <input type="text" name="country" placeholder="e.g. Nepal" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Pin Code :</span>
            <input type="number" name="pin_code" placeholder="e.g. 33700" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" value="Place Order">

   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>