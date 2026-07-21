<?php

include '../components/connect.php';

session_start();

// Handle success message passed from registration
if (isset($_SESSION['success_msg'])) {
   $message[] = $_SESSION['success_msg'];
   unset($_SESSION['success_msg']);
}

// SHORTCUT LOGOUT LOGIC: If action=logout is detected, destroy session instantly
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
   $_SESSION = array();
   if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
         $params["path"], $params["domain"],
         $params["secure"], $params["httponly"]
      );
   }
   session_destroy();
   header('location:farmer_login.php');
   exit();
}

if (isset($_POST['submit'])) {

   $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
   $pass  = $_POST['pass'] ?? '';

   if (!$email) {
      $message[] = 'Please enter a valid email address!';
   } else {
      // Fetch user record by email
      $select_farmer = $conn->prepare("SELECT * FROM `farmers` WHERE email = ?");
      $select_farmer->execute([$email]);
      $row = $select_farmer->fetch(PDO::FETCH_ASSOC);

      // Verify password (supports modern password_hash & legacy md5 fallback)
      if ($row) {
         $password_matches = false;

         if (password_verify($pass, $row['password'])) {
            $password_matches = true;
         } elseif ($row['password'] === md5($pass)) {
            // Backward compatibility for old MD5 accounts: rehash to modern password_hash on successful login
            $password_matches = true;
            $new_hash = password_hash($pass, PASSWORD_DEFAULT);
            $rehash = $conn->prepare("UPDATE `farmers` SET password = ? WHERE id = ?");
            $rehash->execute([$new_hash, $row['id']]);
         }

         if ($password_matches) {
            $_SESSION['farmer_id'] = $row['id'];
            header('location:dashboard.php');
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
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Farmer Portal Login</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body style="background-color: var(--light-bg); display: flex; align-items: center; justify-content: center; min-height: 100vh;">

<?php if (!empty($message) && is_array($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div class="message" style="position: fixed; top: 2rem; max-width: 1200px; width: 90%; z-index: 10000;">
         <span><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<section class="form-container" style="width: 100%; max-width: 40rem; background: var(--white); padding: 2rem; border-radius: .5rem; box-shadow: var(--box-shadow);">

   <form action="" method="post" style="text-align: center;">
      <h3 style="font-size: 2.5rem; color: var(--black); text-transform: uppercase; margin-bottom: 1rem;">Farmer Portal</h3>
      
      <input type="email" name="email" required placeholder="Enter email address" class="box" maxlength="100" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <input type="password" name="pass" required placeholder="Enter your password" class="box" maxlength="100">
      
      <!-- Forgot Password Link -->
      <div style="text-align: right; margin: -0.5rem 0 1.5rem 0;">
         <a href="forgot_password.php" style="font-size: 1.4rem; color: var(--light-color);">Forgot password?</a>
      </div>

      <input type="submit" value="Access Dashboard" name="submit" class="btn">
      
      <p style="margin-top: 1.5rem; font-size: 1.6rem; color: var(--light-color);">
         New vendor? <a href="farmer_register.php" style="color: var(--purple, #8b5cf6);">Create account</a>
      </p>
   </form>

</section>

</body>
</html>