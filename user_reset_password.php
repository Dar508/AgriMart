<?php
include 'components/connect.php';
session_start();

$token = $_GET['token'] ?? '';
$message = [];
$valid_token = false;

if (!empty($token)) {
   $select_user = $conn->prepare("SELECT id FROM `users` WHERE reset_token = ? AND reset_expires > NOW()");
   $select_user->execute([$token]);
   $user = $select_user->fetch(PDO::FETCH_ASSOC);

   if ($user) {
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

      $update = $conn->prepare("UPDATE `users` SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
      $update->execute([$hashed_pass, $user['id']]);

      $_SESSION['success_msg'] = 'Password updated successfully! Please login with your new password.';
      header('location:user_login.php');
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
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<?php if ($valid_token): ?>
<section class="form-container">
   <form action="" method="post">
      <h3>New Password</h3>
      
      <input type="password" name="pass" required placeholder="Enter new password" class="box" maxlength="100">
      <input type="password" name="cpass" required placeholder="Confirm new password" class="box" maxlength="100">
      
      <input type="submit" value="Update Password" name="submit" class="btn">
   </form>
</section>
<?php endif; ?>

<?php include 'components/footer.php'; ?>

</body>
</html>