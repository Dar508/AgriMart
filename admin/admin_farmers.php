<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if(empty($admin_id)){
   header('location:admin_login.php');
   exit();
}

// Handle password reset request from admin
if(isset($_POST['reset_password'])){
   $farmer_id = filter_var($_POST['farmer_id'], FILTER_SANITIZE_SPECIAL_CHARS);
   $new_pass = $_POST['new_pass'];
   
   if(empty($new_pass)){
      $message[] = 'Please enter a new password!';
   } else {
      $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
      
      $update_pass = $conn->prepare("UPDATE `farmers` SET password = ? WHERE id = ?");
      $update_pass->execute([$hashed_pass, $farmer_id]);
      
      if($update_pass->rowCount() > 0){
         $message[] = 'Farmer password successfully updated!';
      } else {
         $message[] = 'Failed to update password or farmer not found!';
      }
   }
}

// Handle deletion of farmer account if needed
if(isset($_GET['delete'])){
   $delete_id = filter_var($_GET['delete'], FILTER_SANITIZE_SPECIAL_CHARS);
   
   $delete_farmer = $conn->prepare("DELETE FROM `farmers` WHERE id = ?");
   $delete_farmer->execute([$delete_id]);
   
   header('location:admin_farmers.php');
   exit();
}

// Fetch all registered farmers
$select_farmers = $conn->prepare("SELECT * FROM `farmers` ORDER BY id DESC");
$select_farmers->execute();
$farmers = $select_farmers->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin - Manage Farmers</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">

   <h1 class="heading">Registered Farmers</h1>

   <?php
   if(isset($message)){
      foreach($message as $msg){
         echo '
         <div class="message" style="margin: 0 auto 2rem auto; max-width: 1200px;">
            <span>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
   ?>

   <div class="box-container">

      <?php
      if(count($farmers) > 0){
         foreach($farmers as $farmer){
      ?>
      <div class="box">
         <p> farmer id : <span><?= htmlspecialchars($farmer['id'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> username : <span><?= htmlspecialchars($farmer['name'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> email : <span><?= htmlspecialchars($farmer['email'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
         
         <!-- Password Reset Form for this Farmer -->
         <form action="" method="post" style="margin: 1.5rem 0; border-top: 1px solid var(--border); padding-top: 1.5rem;">
            <input type="hidden" name="farmer_id" value="<?= htmlspecialchars($farmer['id'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="password" name="new_pass" required placeholder="New password" maxlength="20" class="box" style="margin-bottom: 1rem;">
            <input type="submit" value="Reset Password" name="reset_password" class="option-btn" style="width: 100%; margin-bottom: 1rem;" onclick="return confirm('Are you sure you want to reset this farmer\'s password?');">
         </form>

         <a href="admin_farmers.php?delete=<?= htmlspecialchars($farmer['id'], ENT_QUOTES, 'UTF-8'); ?>" class="delete-btn" onclick="return confirm('Delete this farmer account? All associated products may be affected.');">Delete Farmer</a>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">No registered farmers found!</p>';
      }
      ?>

   </div>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
