<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';
$search_box = trim($_POST['search_box'] ?? '');

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Search Products</title>
   
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="search-form">
   <form action="" method="post">
      <input type="text" name="search_box" placeholder="Search here..." maxlength="100" class="box" value="<?= htmlspecialchars($search_box); ?>" required>
      <button type="submit" class="fas fa-search" name="search_btn"></button>
   </form>
</section>

<section class="products" style="padding-top: 0; min-height:100vh;">

   <div class="box-container">

   <?php
     if (isset($_POST['search_box']) || isset($_POST['search_btn'])) {

        if (!empty($search_box)) {
           // Safe PDO prepared statement with parameterized wildcards
           $select_products = $conn->prepare("SELECT * FROM `products` WHERE name LIKE ? OR details LIKE ?"); 
           $select_products->execute(["%{$search_box}%", "%{$search_box}%"]);

           if ($select_products->rowCount() > 0) {
              while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_product['id']); ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name']); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_product['price']); ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01']); ?>">
      
      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_product['id']); ?>" class="fas fa-eye"></a>
      
      <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_product['name']); ?></div>
      
      <div class="flex">
         <div class="price"><span>Rs. </span><?= htmlspecialchars($fetch_product['price']); ?><span>/-</span></div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
      </div>
      
      <input type="submit" value="Add To Cart" class="btn" name="add_to_cart">
   </form>
   <?php
              }
           } else {
              echo '<p class="empty">No products found matching your search!</p>';
           }
        } else {
           echo '<p class="empty">Please enter something to search!</p>';
        }
     }
   ?>

   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>