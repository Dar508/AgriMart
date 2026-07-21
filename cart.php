<?php

include 'components/connect.php';

session_start();

$message = [];

$user_id = $_SESSION['user_id'] ?? '';

if (empty($user_id)) {
   header('location:user_login.php');
   exit();
}

// Include wishlist & cart submission handler
include 'components/wishlist_cart.php';

// Delete individual cart item
if (isset($_POST['delete'])) {
   $cart_id = (int)($_POST['cart_id'] ?? 0);
   
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ? AND user_id = ?");
   $delete_cart_item->execute([$cart_id, $user_id]);
   $message[] = 'Cart item deleted!';
}

// Delete all items from cart (Postback method to prevent accidental GET execution)
if (isset($_POST['delete_all'])) {
   $delete_cart_all = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart_all->execute([$user_id]);
   $message[] = 'All items deleted from cart!';
}

// Update cart item quantity
if (isset($_POST['update_qty'])) {
   $cart_id = (int)($_POST['cart_id'] ?? 0);
   $qty     = (int)($_POST['qty'] ?? 1);
   
   if ($qty > 0 && $qty <= 999) {
      $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ? AND user_id = ?");
      $update_qty->execute([$qty, $cart_id, $user_id]);
      $message[] = 'Cart quantity updated!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart - AgriMart</title>
   
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- Display Messages -->
<?php
if (!empty($message) && is_array($message)) {
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

<section class="products shopping-cart">

   <h1 class="heading">Shopping Cart</h1>

   <div class="box-container">

   <?php
      $grand_total = 0;
      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if ($select_cart->rowCount() > 0) {
         while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
            $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
            $grand_total += $sub_total;
   ?>
   <form action="" method="post" class="box">
      <!-- Hidden fields for cart/wishlist context -->
      <input type="hidden" name="cart_id" value="<?= htmlspecialchars($fetch_cart['id'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_cart['pid'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_cart['name'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_cart['price'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_cart['image'], ENT_QUOTES, 'UTF-8'); ?>">

      <!-- Quick View & Wishlist Actions -->
      <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_cart['pid'], ENT_QUOTES, 'UTF-8'); ?>" class="fas fa-eye"></a>
      <button class="fas fa-heart" type="submit" name="add_to_wishlist" value="add_to_wishlist"></button>

      <img src="uploaded_img/<?= htmlspecialchars($fetch_cart['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Product">
      
      <div class="name"><?= htmlspecialchars($fetch_cart['name'], ENT_QUOTES, 'UTF-8'); ?></div>
      
      <div class="flex">
         <div class="price">NRs <?= htmlspecialchars($fetch_cart['price'], ENT_QUOTES, 'UTF-8'); ?>/-</div>
         <input type="number" name="qty" class="qty" min="1" max="999" onkeypress="if(this.value.length == 4) return false;" value="<?= htmlspecialchars($fetch_cart['quantity'], ENT_QUOTES, 'UTF-8'); ?>">
         <button type="submit" class="fas fa-edit" name="update_qty" title="Update Quantity"></button>
      </div>

      <div class="sub-total"> Sub Total : <span>NRs <?= htmlspecialchars($sub_total, ENT_QUOTES, 'UTF-8'); ?>/-</span> </div>
      
      <input type="submit" value="Delete Item" onclick="return confirm('Delete this item from cart?');" class="delete-btn" name="delete">
   </form>
   <?php
         }
      } else {
         echo '<p class="empty">Your shopping cart is empty!</p>';
      }
   ?>
   </div>

   <div class="cart-total">
      <p>Grand Total : <span>NRs <?= htmlspecialchars($grand_total, ENT_QUOTES, 'UTF-8'); ?>/-</span></p>
      
      <div class="flex-btn">
         <a href="shop.php" class="option-btn">Continue Shopping</a>
         
         <form action="" method="post" style="display:inline;">
            <input type="submit" name="delete_all" value="Delete All Items" class="delete-btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" onclick="return confirm('Delete all items from cart?');">
         </form>
         
         <a href="checkout.php" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">Proceed To Checkout</a>
      </div>
   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>