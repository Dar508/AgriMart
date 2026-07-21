<?php
include 'components/connect.php';
session_start();

$message = [];

if (isset($_POST['submit'])) {
   $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

   if (!$email) {
      $message[] = 'Please enter a valid email address!';
   } else {
      $select_user = $conn->prepare("SELECT id FROM `users` WHERE email = ?");
      $select_user->execute([$email]);

      if ($select_user->rowCount() > 0) {
         // Generate a secure random token & set 1-hour expiration
         $token = bin2hex(random_bytes(32));
         $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

         $update = $conn->prepare("UPDATE `users` SET reset_token = ?, reset_expires = ? WHERE email = ?");
         $update->execute([$token, $expires, $email]);

         // Create the reset link using the renamed user_reset_password.php file
         $reset_link = "http://localhost/AgriMart/user_reset_password.php?token=" . $token;

         // Send Email
         $subject = "Password Reset Request - AgriMart";
         $message_body = "Hello,\n\nClick the link below to reset your password (valid for 1 hour):\n" . $reset_link;
         $headers = "From: no-reply@agrimart.com";

         if (@mail($email, $subject, $message_body, $headers)) {
            $message[] = 'Password reset link sent to your email!';
         } else {
            // Local testing fallback link
            $message[] = 'Reset link generated! (Local testing link: <a href="'.$reset_link.'">Reset Here</a>)';
         }
      } else {
         $message[] = 'No account found with that email address!';
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Forgot Password - AgriMart</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="form-container">
   <form action="" method="post">
      <h3>Reset Password</h3>
      <p style="font-size: 1.4rem; color: var(--light-color); margin-bottom: 1.5rem; text-align: center;">
         Enter your registered email address to receive a password reset link.
      </p>
      
      <input type="email" name="email" required placeholder="Enter your email" class="box" maxlength="100">
      
      <input type="submit" value="Send Reset Link" name="submit" class="btn">
      
      <p>Remembered it? <a href="user_login.php">Back to login</a></p>
   </form>
</section>

<?php include 'components/footer.php'; ?>

</body>
</html>