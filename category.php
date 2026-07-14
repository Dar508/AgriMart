<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
   // Fetch user role tier from database
   $select_user_type = $conn->prepare("SELECT user_type FROM `users` WHERE id = ?");
   $select_user_type->execute([$user_id]);
   $fetch_user_type = $select_user_type->fetch(PDO::FETCH_ASSOC);
   $user_type = $fetch_user_type ? $fetch_user_type['user_type'] : 'user';
}else{
   $user_id = '';
   $user_type = 'user'; // Guest visitors see regular retail pricing
}

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Food Category Marketplace</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products">

   <h1 class="heading">Fresh Produce Category</h1>

   <div class="box-container">

   <?php
      $category = $_GET['category'];
      // Filter products dynamically based on url string query parameter
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE category = ?"); 
      $select_products->execute([$category]);
      
      if($select_products->rowCount() > 0){
         while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
            
            // Adjust calculations and elements according to the verified consumer tier
            if($user_type == 'supplier'){
               $display_price = $fetch_product['supplier_price'];
               $min_qty = $fetch_product['min_supplier_qty'];
               $badge = '<span class="badge" style="color: green; font-size: 1.2rem; display: block; margin-top: .5rem;">Wholesale Bulk Tier</span>';
            } else {
               $display_price = $fetch_product['price'];
               $min_qty = 1;
               $badge = '<span class="badge" style="color: orange; font-size: 1.2rem; display: block; margin-top: .5rem;">Retail Tier</span>';
            }
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
      <input type="hidden" name="price" value="<?= $display_price; ?>">
      <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
      
      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
      
      <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
      <div class="name"><?= $fetch_product['name']; ?> <?= $badge; ?></div>
      
      <div class="flex">
         <div class="price"><span>$</span><?= $display_price; ?><span>/kg</span></div>
         <input type="number" name="qty" class="qty" min="<?= $min_qty; ?>" max="99" onkeypress="if(this.value.length == 4) return false;" value="<?= $min_qty; ?>">
      </div>
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
         }
      }else{
         echo '<p class="empty">No products found under this agricultural category yet!</p>';
      }
   ?>

   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>