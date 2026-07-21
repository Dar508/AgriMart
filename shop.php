<?php

include 'components/connect.php';

session_start();

$message = [];

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

// Fetch user type to check if they are a supplier/wholesaler
$user_type = 'user';
if(!empty($user_id)){
   try {
      $select_user = $conn->prepare("SELECT user_type FROM `users` WHERE id = ?");
      $select_user->execute([$user_id]);
      $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);
      $user_type = $fetch_user['user_type'] ?? 'user';
   } catch (PDOException $e) {
      $user_type = 'user';
   }
}

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shop</title>
   
   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<?php
if(!empty($message) && is_array($message)){
   foreach($message as $msg){
      echo '
      <div class="message" style="position: sticky; top:0; max-width: 1200px; margin: 0 auto; padding:1.5rem 2rem; background: var(--white); display:flex; align-items:center; justify-content:space-between; gap:1.5rem; border-bottom: var(--border); z-index: 10000;">
         <span style="font-size: 1.8rem; color:var(--black);">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</span>
         <i class="fas fa-times" style="font-size: 2.2rem; color:var(--red); cursor:pointer;" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="products">

   <h1 class="heading">Latest Products</h1>

   <div class="box-container">

   <?php
     $select_products = $conn->prepare("SELECT * FROM `products`"); 
     $select_products->execute();
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
         
         $retail_price     = $fetch_product['price'];
         $supplier_price   = $fetch_product['supplier_price'] ?? 0;
         $min_supplier_qty = $fetch_product['min_supplier_qty'] ?? 1;

         // Determine which price/quantity values to present based on account type
         if ($user_type === 'supplier') {
            $display_price = ($supplier_price > 0) ? $supplier_price : $retail_price;
            $min_qty       = $min_supplier_qty;
            $badge_html    = '<span style="display: block; font-size: 1rem; color: #059669; font-weight: 600; margin-top: 0.3rem;"><i class="fas fa-tag"></i> Wholesale Tier Active</span>';
         } else {
            $display_price = $retail_price;
            $min_qty       = 1;
            $badge_html    = '';
         }
   ?>
   <form action="" method="post" class="box">
      <!-- Hidden fields referencing dynamic prices and quantities -->
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($display_price, ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>">
      
      <button class="fas fa-heart" type="submit" name="add_to_wishlist" value="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
      
      <div class="name">
         <?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?>
         <?= $badge_html; ?>
      </div>

      <?php if ($user_type !== 'supplier' && $supplier_price > 0): ?>
         <div style="font-size: 1.1rem; color: #059669; margin: 0.5rem 0;">
            <i class="fas fa-tags"></i> Bulk Deal: Rs <?= htmlspecialchars($supplier_price, ENT_QUOTES, 'UTF-8'); ?>/- (Min: <?= htmlspecialchars($min_supplier_qty, ENT_QUOTES, 'UTF-8'); ?> units)
         </div>
      <?php endif; ?>

      <div class="flex">
         <div class="price"><span>Rs </span><?= htmlspecialchars($display_price, ENT_QUOTES, 'UTF-8'); ?><span>/-</span></div>
         <input type="number" name="qty" class="qty" min="<?= $min_qty; ?>" max="999" onkeypress="if(this.value.length == 4) return false;" value="<?= $min_qty; ?>">
      </div>
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">No products found!</p>';
   }
   ?>

   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>