<?php

include '../components/connect.php';

session_start();

$message = [];

if (isset($_POST['submit'])) {

   $name    = trim($_POST['name'] ?? '');
   $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
   $phone   = trim($_POST['phone'] ?? '');
   $address = trim($_POST['address'] ?? '');
   $pass    = $_POST['pass'] ?? '';
   $cpass   = $_POST['cpass'] ?? '';

   // Input validations
   if (!$email) {
      $message[] = 'Please enter a valid email address!';
   } elseif ($pass !== $cpass) {
      $message[] = 'Confirm password does not match!';
   } elseif (strlen($pass) < 6) {
      $message[] = 'Password must be at least 6 characters long!';
   } else {
      // Check if email already exists
      $select_farmer = $conn->prepare("SELECT id FROM `farmers` WHERE email = ?");
      $select_farmer->execute([$email]);

      if ($select_farmer->rowCount() > 0) {
         $message[] = 'Email address is already registered!';
      } else {
         // Hash password securely using Bcrypt / Argon2
         $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

         $insert_farmer = $conn->prepare("INSERT INTO `farmers`(name, email, password, phone, address) VALUES(?,?,?,?,?)");
         $insert_farmer->execute([$name, $email, $hashed_pass, $phone, $address]);

         // Store message in session so it persists across header redirect
         $_SESSION['success_msg'] = 'Registered successfully! Please log in now.';
         header('location:farmer_login.php');
         exit();
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Farmer Registration</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body style="background-color: var(--light-bg); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem;">

<?php if (!empty($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div class="message" style="position: fixed; top: 2rem; max-width: 1200px; width: 90%; z-index: 10000;">
         <span><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<section class="form-container" style="width: 100%; max-width: 50rem; background: var(--white); padding: 2rem; border-radius: .5rem; box-shadow: var(--box-shadow);">

   <form action="" method="post" style="text-align: center;">
      <h3 style="font-size: 2.5rem; color: var(--black); text-transform: uppercase; margin-bottom: 1rem;">Farmer Register</h3>
      
      <input type="text" name="name" required placeholder="Enter full name" class="box" maxlength="100" value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <input type="email" name="email" required placeholder="Enter email address" class="box" maxlength="100" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <input type="text" name="phone" required placeholder="Enter phone number" class="box" maxlength="15" value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <input type="text" name="address" required placeholder="Enter farm location address" class="box" maxlength="200" value="<?= htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      
      <input type="password" name="pass" required placeholder="Create secure password" class="box" maxlength="100">
      <input type="password" name="cpass" required placeholder="Confirm password" class="box" maxlength="100">
      
      <input type="submit" value="Register Now" name="submit" class="btn">
      
      <p style="margin-top: 1.5rem; font-size: 1.6rem; color: var(--light-color);">
         Already have an account? <a href="farmer_login.php" style="color: var(--purple, #8b5cf6);">Login here</a>
      </p>
   </form>

</section>

</body>
</html>