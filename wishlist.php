<?php

include 'components/connect.php';

session_start();

// Initialize message array
$message = [];

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
   exit();
}

// Include shared cart and wishlist action handler
include 'components/wishlist_cart.php';

if(isset($_POST['delete'])){
   $wishlist_id = htmlspecialchars($_POST['wishlist_id'], ENT_QUOTES, 'UTF-8');
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ? AND user_id = ?");
   $delete_wishlist_item->execute([$wishlist_id, $user_id]);
   $message[] = 'Wishlist item deleted!';
}

if(isset($_GET['delete_all'])){
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   header('location:wishlist.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Wishlist</title>
   
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

   <h3 class="heading">Your Wishlist</h3>

   <div class="box-container">

   <?php
      $grand_total = 0;
      $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
      $select_wishlist->execute([$user_id]);
      if($select_wishlist->rowCount() > 0){
         while($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){
            $grand_total += $fetch_wishlist['price'];  
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_wishlist['pid'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="wishlist_id" value="<?= htmlspecialchars($fetch_wishlist['id'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_wishlist['name'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_wishlist['price'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_wishlist['image'], ENT_QUOTES, 'UTF-8'); ?>">
      
      <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_wishlist['pid'], ENT_QUOTES, 'UTF-8'); ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_wishlist['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_wishlist['name'], ENT_QUOTES, 'UTF-8'); ?></div>
      <div class="flex">
         <div class="price">Rs <?= htmlspecialchars($fetch_wishlist['price'], ENT_QUOTES, 'UTF-8'); ?>/-</div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
      </div>
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
      <input type="submit" value="delete item" onclick="return confirm('delete this from wishlist?');" class="delete-btn" name="delete">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">Your wishlist is empty!</p>';
   }
   ?>
   </div>

   <div class="wishlist-total">
      <p>Grand Total : <span>Rs <?= $grand_total; ?>/-</span></p>
      <a href="shop.php" class="option-btn">Continue Shopping</a>
      <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 0)?'':'disabled'; ?>" onclick="return confirm('Delete all items from wishlist?');">Delete All Items</a>
   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>