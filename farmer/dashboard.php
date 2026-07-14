<?php

include '../components/connect.php';

session_start();

$farmer_id = $_SESSION['farmer_id'];

if(!isset($farmer_id)){
   header('location:farmer_login.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Farmer Dashboard</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/farmer_header.php'; ?>

<section class="dashboard">

   <h1 class="heading">Overview Dashboard</h1>

   <div class="box-container">

      <div class="box">
         <h3>Welcome!</h3>
         <p>Manage your listed crops, fresh fruits, or seasonal vegetable inventories securely.</p>
         <a href="products.php" class="btn">Manage Produce</a>
      </div>

      <div class="box">
         <?php
            // Counts only products uploaded by this specific farmer
            $select_products = $conn->prepare("SELECT * FROM `products` WHERE farmer_id = ?");
            $select_products->execute([$farmer_id]);
            $number_of_products = $select_products->rowCount();
         ?>
         <h3><?= $number_of_products; ?></h3>
         <p style="font-size: 1.6rem; color: var(--light-color);">Active Listed Items</p>
         <a href="products.php" class="option-btn">View Inventory</a>
      </div>

   </div>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>