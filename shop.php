<?php

include 'components/connect.php';

session_start();

$message = [];

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
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
   ?>
   <form action="" method="post" class="box">
      <!-- Hidden fields directly referencing product values -->
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_product['price'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>">
      
      <button class="fas fa-heart" type="submit" name="add_to_wishlist" value="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
      
      <div class="name"><?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?></div>
      <div class="flex">
         <div class="price"><span>Rs </span><?= htmlspecialchars($fetch_product['price'], ENT_QUOTES, 'UTF-8'); ?><span>/-</span></div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
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