<?php
include '../components/connect.php';

session_start();

$farmer_id = $_SESSION['farmer_id'] ?? null;

// Redirect unauthenticated users
if (!$farmer_id) {
   header('location: farmer_login.php');
   exit();
}

// Handle Logout securely
if (isset($_GET['logout'])) {
   session_unset();
   session_destroy();
   header('location: farmer_login.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Farmer Messages - AgriMart</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body style="background-color: #f9fafb;">

<?php include '../components/farmer_header.php'; ?>

<section class="messages" style="padding: 2rem 4%; max-width: 1200px; margin: 0 auto;">

   <h1 class="heading" style="font-size: 2.5rem; color: var(--black); margin-bottom: 2rem; text-align: center; font-weight: 700;">Customer Messages</h1>

   <div class="box" style="max-width:80rem; margin:0 auto; padding:2rem; background:#fff; border-radius:.8rem; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); font-size:1.6rem; color:#475569;">
      Customer contact messages are managed by the AgriMart administrator. They are not linked to individual farmers, so they are not shown in this farmer account.
   </div>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
