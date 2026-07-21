<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if (empty($admin_id)) {
   header('location:admin_login.php');
   exit();
}

// Handle Farmer Deletion + Product Cleanup
if (isset($_GET['delete'])) {
   $delete_id = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);

   // 1. Delete product images from server storage
   $select_products = $conn->prepare("SELECT image_01, image_02, image_03 FROM `products` WHERE farmer_id = ?");
   $select_products->execute([$delete_id]);
   while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
      if (!empty($fetch_product['image_01'])) @unlink('../uploaded_img/' . $fetch_product['image_01']);
      if (!empty($fetch_product['image_02'])) @unlink('../uploaded_img/' . $fetch_product['image_02']);
      if (!empty($fetch_product['image_03'])) @unlink('../uploaded_img/' . $fetch_product['image_03']);
   }

   // 2. Delete all products belonging to this farmer
   $delete_products = $conn->prepare("DELETE FROM `products` WHERE farmer_id = ?");
   $delete_products->execute([$delete_id]);

   // 3. Delete farmer account
   $delete_farmer = $conn->prepare("DELETE FROM `farmers` WHERE id = ?");
   $delete_farmer->execute([$delete_id]);

   header('location:farmers_accounts.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Farmer Accounts</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">

   <h1 class="heading">Farmer Accounts</h1>

   <div class="box-container">

   <?php
      $select_farmers = $conn->prepare("SELECT * FROM `farmers`");
      $select_farmers->execute();
      
      if ($select_farmers->rowCount() > 0) {
         while ($fetch_accounts = $select_farmers->fetch(PDO::FETCH_ASSOC)) {   
   ?>
   <div class="box">
      <p> Farmer ID : <span><?= $fetch_accounts['id']; ?></span> </p>
      <p> Name : <span><?= htmlspecialchars($fetch_accounts['name']); ?></span> </p>
      <p> Email : <span><?= htmlspecialchars($fetch_accounts['email']); ?></span> </p>
      <p> Phone : <span><?= htmlspecialchars($fetch_accounts['phone'] ?? 'N/A'); ?></span> </p>
      <a href="farmers_accounts.php?delete=<?= $fetch_accounts['id']; ?>" onclick="return confirm('Deleting this farmer will also delete all their products from the market! Continue?')" class="delete-btn">Delete Account</a>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">No farmer accounts available!</p>';
      }
   ?>

   </div>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>