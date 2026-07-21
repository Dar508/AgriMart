<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}

// Handle success message passed from registration or reset page
if (isset($_SESSION['success_msg'])) {
   $message[] = $_SESSION['success_msg'];
   unset($_SESSION['success_msg']);
}

if (isset($_POST['submit'])) {

   $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
   $pass  = $_POST['pass'] ?? '';

   if (!$email) {
      $message[] = 'Please enter a valid email address!';
   } else {
      // Fetch user record by email only
      $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
      $select_user->execute([$email]);
      $row = $select_user->fetch(PDO::FETCH_ASSOC);

      if ($row) {
         $password_matches = false;

         // Check modern hash first
         if (password_verify($pass, $row['password'])) {
            $password_matches = true;
         } elseif ($row['password'] === sha1($pass)) {
            // Backward compatibility for legacy SHA-1 accounts: rehash to password_hash on login
            $password_matches = true;
            $new_hash = password_hash($pass, PASSWORD_DEFAULT);
            $rehash = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
            $rehash->execute([$new_hash, $row['id']]);
         }

         if ($password_matches) {
            $_SESSION['user_id'] = $row['id'];
            header('location:home.php');
            exit();
         } else {
            $message[] = 'Incorrect email or password!';
         }
      } else {
         $message[] = 'Incorrect email or password!';
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Login Now</h3>
      
      <input type="email" name="email" required placeholder="enter your email" maxlength="100" class="box" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <input type="password" name="pass" required placeholder="enter your password" maxlength="100" class="box">
      
      <!-- Forgot Password Link -->
      <div style="text-align: right; margin: -0.5rem 0 1.5rem 0;">
         <a href="user_forgot_password.php" style="font-size: 1.4rem; color: var(--light-color);">Forgot password?</a>
      </div>

      <input type="submit" value="login now" class="btn" name="submit">
      <p>don't have an account?</p>
      <a href="user_register.php" class="option-btn">register now</a>
   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>