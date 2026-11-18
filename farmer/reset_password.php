<?php
include '../components/connect.php';
session_start();

$message = [];
$token = $_GET['token'] ?? '';
$valid_token = false;

if (empty($token)) {
   $message[] = 'Invalid or missing password reset token!';
} else {
   // Fetch the farmer record by token first
   $verify_token = $conn->prepare("SELECT * FROM `farmers` WHERE reset_token = ?");
   $verify_token->execute([$token]);
   
   if ($verify_token->rowCount() > 0) {
      $farmer_data = $verify_token->fetch(PDO::FETCH_ASSOC);
      
      // Check expiration using PHP time to avoid database timezone mismatches
      if (strtotime($farmer_data['reset_expires']) > time()) {
         $valid_token = true;
      } else {
         $message[] = 'Password reset link has expired!';
      }
   } else {
      $message[] = 'Password reset link is invalid!';
   }
}

if (isset($_POST['update_pass'])) {
   $new_pass = $_POST['new_pass'] ?? '';
   $c_pass = $_POST['c_pass'] ?? '';

   if (empty($new_pass) || empty($c_pass)) {
      $message[] = 'Please fill out all password fields!';
   } elseif ($new_pass !== $c_pass) {
      $message[] = 'Confirm password does not match new password!';
   } else {
      $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);

      // Update password in the 'farmers' table and clear out the token and expiration
      $update_pass = $conn->prepare("UPDATE `farmers` SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
      $update_pass->execute([$hashed_pass, $token]);

      $_SESSION['success_msg'] = 'Your password has been successfully updated! You can now log in.';
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
   <title>Create New Password - Farmer Portal</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body style="background-color: var(--light-bg); display: flex; align-items: center; justify-content: center; min-height: 100vh;">

<?php if (!empty($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div class="message" style="position: fixed; top: 2rem; max-width: 1200px; width: 90%; z-index: 10000; background: #fff; padding: 1.5rem; border-left: 5px solid #e74c3c; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
         <span style="font-size: 1.5rem; color: #333;"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
         <i class="fas fa-times" style="float: right; cursor: pointer; font-size: 1.6rem;" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<section class="form-container" style="width: 100%; max-width: 40rem; background: var(--white); padding: 2rem; border-radius: .5rem; box-shadow: var(--box-shadow);">
   
   <?php if ($valid_token): ?>
   <form action="" method="post" style="text-align: center;">
      <h3 style="font-size: 2.2rem; color: var(--black); text-transform: uppercase; margin-bottom: 1rem;">New Password</h3>
      <p style="font-size: 1.4rem; color: var(--light-color); margin-bottom: 1.5rem;">Please enter your new secure password below.</p>
      
      <input type="password" name="new_pass" required placeholder="Enter new password" class="box" maxlength="50" style="width: 100%; margin-bottom: 1.5rem; padding: 1.2rem; font-size: 1.6rem; border: var(--border); border-radius: .5rem;">
      <input type="password" name="c_pass" required placeholder="Confirm new password" class="box" maxlength="50" style="width: 100%; margin-bottom: 2rem; padding: 1.2rem; font-size: 1.6rem; border: var(--border); border-radius: .5rem;">
      
      <input type="submit" value="Update Password" name="update_pass" class="btn" style="width: 100%;">
   </form>
   <?php else: ?>
      <div style="text-align: center; padding: 2rem;">
         <p style="font-size: 1.6rem; color: var(--red); margin-bottom: 2rem;">This link is broken, invalid, or has already expired.</p>
         <a href="forgot_password.php" class="btn" style="display: block; text-decoration: none; text-align: center;">Request New Link</a>
      </div>
   <?php endif; ?>

</section>

</body>
</html>