<?php
include '../components/connect.php';
session_start();

$message = [];

if (isset($_POST['submit'])) {
   $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

   if (!$email) {
      $message[] = 'Please enter a valid email address!';
   } else {
      // Query the dedicated 'farmers' table
      $select_farmer = $conn->prepare("SELECT id FROM `farmers` WHERE email = ?");
      $select_farmer->execute([$email]);

      if ($select_farmer->rowCount() > 0) {
         // Generate a secure random token & set 1-hour expiration
         $token = bin2hex(random_bytes(32));
         $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

         // Save token to farmers table
         $update = $conn->prepare("UPDATE `farmers` SET reset_token = ?, reset_expires = ? WHERE email = ?");
         $update->execute([$token, $expires, $email]);

         // Create the reset link
        $reset_link = "reset_password.php?token=" . $token;

         // Send Email & force display the local testing link
         $subject = "Password Reset Request - AgriMart";
         $message_body = "Hello,\n\nClick the link below to reset your password (valid for 1 hour):\n" . $reset_link;
         $headers = "From: no-reply@agrimart.com";

         @mail($email, $subject, $message_body, $headers);
         $message[] = 'Reset link generated! (Click to reset: <a href="'.$reset_link.'" style="color: #3498db; text-decoration: underline; font-weight: bold;">Reset Here</a>)';

      } else {
         $message[] = 'No account found in the farmers table with that email address!';
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Forgot Password - Farmer Portal</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body style="background-color: var(--light-bg); display: flex; align-items: center; justify-content: center; min-height: 100vh;">

<?php if (!empty($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div class="message" style="position: fixed; top: 2rem; max-width: 1200px; width: 90%; z-index: 10000; background: #fff; padding: 1.5rem; border-left: 5px solid #2ecc71; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
         <span style="font-size: 1.5rem; color: #333;"><?= $msg; ?></span>
         <i class="fas fa-times" style="float: right; cursor: pointer; font-size: 1.6rem;" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<section class="form-container" style="width: 100%; max-width: 40rem; background: var(--white); padding: 2rem; border-radius: .5rem; box-shadow: var(--box-shadow);">
   <form action="" method="post" style="text-align: center;">
      <h3 style="font-size: 2.2rem; color: var(--black); text-transform: uppercase; margin-bottom: 1rem;">Reset Password</h3>
      <p style="font-size: 1.4rem; color: var(--light-color); margin-bottom: 1.5rem;">Enter your email address to receive a password reset link.</p>
      
      <input type="email" name="email" required placeholder="Enter your registered email" class="box" maxlength="100" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      
      <input type="submit" value="Send Reset Link" name="submit" class="btn">
      
      <p style="margin-top: 1.5rem; font-size: 1.5rem; color: var(--light-color);">
         Remembered it? <a href="farmer_login.php" style="color: var(--purple, #8b5cf6);">Back to login</a>
      </p>
   </form>
</section>

</body>
</html>