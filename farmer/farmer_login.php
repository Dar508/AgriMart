<?php
include '../components/connect.php';
session_start();

// If already logged in, skip login page completely
if (isset($_SESSION['farmer_id'])) {
   header('location: dashboard.php');
   exit();
}

if (isset($_SESSION['success_msg'])) {
   $message[] = $_SESSION['success_msg'];
   unset($_SESSION['success_msg']);
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
   session_unset();
   session_destroy();
   header('location: farmer_login.php');
   exit();
}

if (isset($_POST['submit'])) {
   $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
   $pass  = $_POST['pass'] ?? '';

   if (!$email) {
      $message[] = 'Please enter a valid email address!';
   } elseif (empty($pass)) {
      $message[] = 'Please enter your password!';
   } else {
      $select_farmer = $conn->prepare("SELECT * FROM `farmers` WHERE email = ?");
      $select_farmer->execute([$email]);
      
      if ($select_farmer->rowCount() > 0) {
         $row = $select_farmer->fetch(PDO::FETCH_ASSOC);
         $stored_password = $row['password'];
         
         $is_match = false;
         if (!empty($stored_password)) {
            if (password_verify($pass, $stored_password)) {
               $is_match = true;
            } elseif ($stored_password === sha1($pass) || $stored_password === md5($pass) || $stored_password === $pass) {
               $is_match = true;
               // Auto-upgrade legacy or plain-text password to secure hash
               $new_hash = password_hash($pass, PASSWORD_DEFAULT);
               $update_hash = $conn->prepare("UPDATE `farmers` SET password = ? WHERE id = ?");
               $update_hash->execute([$new_hash, $row['id']]);
            }
         }

         if ($is_match) {
            $_SESSION['farmer_id'] = $row['id'];
            session_write_close();
            header('location: dashboard.php');
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
   <title>Farmer Portal Login - AgriMart</title>
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
   <form action="" method="post" style="text-align: center;">
      <h3 style="font-size: 2.5rem; color: var(--black); text-transform: uppercase; margin-bottom: 1rem;">Farmer Portal</h3>
      
      <input type="email" name="email" required placeholder="Enter email address" class="box" maxlength="100" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <input type="password" name="pass" required placeholder="Enter your password" class="box" maxlength="100">
      
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
