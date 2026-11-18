<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';
$search_box = trim($_POST['search_box'] ?? '');
$search_location = trim($_POST['search_location'] ?? '');
$nepal_places = [
   'Kathmandu', 'Lalitpur', 'Bhaktapur', 'Pokhara', 'Biratnagar', 'Birgunj', 'Janakpur',
   'Hetauda', 'Bharatpur', 'Butwal', 'Dharan', 'Itahari', 'Nepalgunj', 'Dhangadhi',
   'Tulsipur', 'Ghorahi', 'Damak', 'Birtamod', 'Banepa', 'Dhulikhel', 'Lahan',
   'Rajbiraj', 'Gorkha', 'Baglung', 'Beni', 'Tansen', 'Jomsom', 'Dipayal'
];
if (!in_array($search_location, $nepal_places, true)) {
   $search_location = '';
}

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
   <form action="" method="post" style="flex-wrap:wrap;">
      <input type="text" name="search_box" placeholder="Search produce (optional)" maxlength="100" class="box" value="<?= htmlspecialchars($search_box, ENT_QUOTES, 'UTF-8'); ?>">
      <select name="search_location" class="box" aria-label="Filter by farmer city" style="max-width:24rem;">
         <option value="">All Nepal locations</option>
         <?php foreach ($nepal_places as $place): ?>
            <option value="<?= htmlspecialchars($place, ENT_QUOTES, 'UTF-8'); ?>" <?= $search_location === $place ? 'selected' : ''; ?>><?= htmlspecialchars($place, ENT_QUOTES, 'UTF-8'); ?></option>
         <?php endforeach; ?>
      </select>
      <button type="submit" class="fas fa-search" name="search_btn"></button>
   </form>
</section>

<section class="products" style="padding-top: 0; min-height:100vh;">

   <div class="box-container">

   <?php
     if (isset($_POST['search_box']) || isset($_POST['search_location']) || isset($_POST['search_btn'])) {
        if ($search_box !== '' || $search_location !== '') {
           $query = "SELECT p.* FROM `products` p LEFT JOIN `farmers` f ON f.id = p.farmer_id WHERE (? = '' OR p.name LIKE ? OR p.details LIKE ?) AND (? = '' OR f.address LIKE ?) ORDER BY p.id DESC";
           $select_products = $conn->prepare($query);
           $select_products->execute([$search_box, "%{$search_box}%", "%{$search_box}%", $search_location, "%{$search_location}%"]);

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
      <?php if ($search_location !== ''): ?>
         <div style="font-size:1.3rem; color:#64748b; margin:.5rem 0;"><i class="fas fa-location-dot"></i> Farmer in <?= htmlspecialchars($search_location, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      
      <div class="flex">
         <div class="price"><span>Rs. </span><?= htmlspecialchars($fetch_product['price']); ?><span>/-</span></div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
      </div>
      
      <input type="submit" value="Add To Cart" class="btn" name="add_to_cart">
   </form>
   <?php
              }
           } else {
           echo '<p class="empty">No products found for that search and location. Try another Nepal city.</p>';
           }
        } else {
           echo '<p class="empty">Enter a produce name or choose a Nepal location to search.</p>';
        }
     }
   ?>

   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
