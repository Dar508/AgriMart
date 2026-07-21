<?php
include '../components/connect.php';
session_start();

$token = $_GET['token'] ?? '';
$message = [];
$valid_token = false;

if (!empty($token)) {
   // Validate token and ensure it has not expired
   $select_farmer = $conn->prepare("SELECT id FROM `farmers` WHERE reset_token = ? AND reset_expires > NOW()");
   $select_farmer->execute([$token]);
   $farmer = $select_farmer->fetch(PDO::FETCH_ASSOC);

   if ($farmer) {
      $valid_token = true;
   } else {
      $message[] = 'Invalid or expired password reset link!';
   }
} else {
   $message[] = 'No reset token provided!';
}

if ($valid_token && isset($_POST['submit'])) {
   $pass  = $_POST['pass'] ?? '';
   $cpass = $_POST['cpass'] ?? '';

   if ($pass !== $cpass) {
      $message[] = 'Confirm password does not match!';
   } elseif (strlen($pass) < 6) {
      $message[] = 'Password must be at least 6 characters long!';
   } else {
      $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

      // Update password and clear reset token fields
      $update = $conn->prepare("UPDATE `farmers` SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
      $update->execute([$hashed_pass, $farmer['id']]);

      $_SESSION['success_msg'] = 'Password updated successfully! Please login with your new password.';
      header('location:farmer_login.php');
      exit();
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Create New Password</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body style="background-color: var(--light-bg); display: flex; align-items: center; justify-content: center; min-height: 100vh;">

<?php if (!empty($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div class="message" style="position: fixed; top: 2rem; max-width: 1200px; width: 90%; z-index: 10000;">
         <span><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<?php if ($valid_token): ?>
<section class="form-container" style="width: 100%; max-width: 40rem; background: var(--white); padding: 2rem; border-radius: .5rem; box-shadow: var(--box-shadow);">
   <form action="" method="post" style="text-align: center;">
      <h3 style="font-size: 2.2rem; color: var(--black); text-transform: uppercase; margin-bottom: 1rem;">New Password</h3>
      
      <input type="password" name="pass" required placeholder="Enter new password" class="box" maxlength="100">
      <input type="password" name="cpass" required placeholder="Confirm new password" class="box" maxlength="100">
      
      <input type="submit" value="Update Password" name="submit" class="btn">
   </form>
</section>
<?php endif; ?>

</body>
</html>