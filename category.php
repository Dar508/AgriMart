<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';
$user_type = 'user';

if (!empty($user_id)) {
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

$category = $_GET['category'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= ucfirst(htmlspecialchars($category)); ?> - Category</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products">

   <h1 class="heading">Category: <?= htmlspecialchars(ucfirst($category)); ?></h1>

   <div class="box-container">

   <?php
     $select_products = $conn->prepare("SELECT * FROM `products` WHERE category = ? ORDER BY id DESC");
     $select_products->execute([$category]);

     if ($select_products->rowCount() > 0) {
        while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
           $supplier_price   = $fetch_product['supplier_price'] ?? $fetch_product['price'];
           $min_supplier_qty = $fetch_product['min_supplier_qty'] ?? 1;

           if ($user_type === 'supplier') {
              $display_price = $supplier_price;
              $min_qty       = $min_supplier_qty;
              $badge_html    = '<span class="badge badge-wholesale">Wholesale Bulk Tier</span>';
           } else {
              $display_price = $fetch_product['price'];
              $min_qty       = 1;
              $badge_html    = '<span class="badge badge-retail">Retail Tier</span>';
           }
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($display_price, ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>">

      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="">

      <div class="name">
         <?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?>
         <?= $badge_html; ?>
      </div>

      <div class="flex">
         <div class="price"><span>NRS </span><?= htmlspecialchars($display_price, ENT_QUOTES, 'UTF-8'); ?><span>/kg</span></div>
         <input type="number" name="qty" class="qty" min="<?= $min_qty; ?>" max="999" onkeypress="if(this.value.length == 4) return false;" value="<?= $min_qty; ?>">
      </div>

      <input type="submit" value="Add To Cart" class="btn" name="add_to_cart">
   </form>
   <?php
        }
     } else {
        echo '<p class="empty">No products found in this category!</p>';
     }
   ?>

   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>