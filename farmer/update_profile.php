<?php

include '../components/connect.php';

session_start();

$farmer_id = $_SESSION['farmer_id'] ?? '';

if (empty($farmer_id)) {
   header('location:farmer_login.php');
   exit();
}

$message = [];

// Handle Profile Update
if (isset($_POST['submit'])) {

   $name = trim($_POST['name'] ?? '');
   $name = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
   
   $email = trim($_POST['email'] ?? '');
   $email = filter_var($email, FILTER_VALIDATE_EMAIL);
   $address = trim($_POST['address'] ?? '');

   if (!$email) {
      $message[] = 'Please enter a valid email address!';
   } else {
      // Check if email is already taken by another user
      $select_email = $conn->prepare("SELECT id FROM `farmers` WHERE email = ? AND id != ?");
      $select_email->execute([$email, $farmer_id]);

      if ($select_email->rowCount() > 0) {
         $message[] = 'Email address is already registered by another account!';
      } else {
         // Update basic profile details
         $update_profile = $conn->prepare("UPDATE `farmers` SET name = ?, email = ?, address = ? WHERE id = ?");
         $update_profile->execute([$name, $email, $address, $farmer_id]);
         $message[] = 'Profile updated successfully!';
      }
   }

   // Handle Password Change Logic
   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709'; // SHA1 of empty string for fallback check
   $old_pass   = $_POST['old_pass'] ?? '';
   $new_pass   = $_POST['new_pass'] ?? '';
   $c_pass     = $_POST['c_pass'] ?? '';

   if (!empty($old_pass) || !empty($new_pass) || !empty($c_pass)) {
      if (empty($old_pass)) {
         $message[] = 'Please enter your old password!';
      } elseif (empty($new_pass)) {
         $message[] = 'Please enter a new password!';
      } elseif ($new_pass !== $c_pass) {
         $message[] = 'Confirm password does not match new password!';
      } else {
         // Fetch current stored password for verification
         $select_prev_pass = $conn->prepare("SELECT password FROM `farmers` WHERE id = ?");
         $select_prev_pass->execute([$farmer_id]);
         $fetch_prev_pass = $select_prev_pass->fetch(PDO::FETCH_ASSOC);
         $stored_password = $fetch_prev_pass['password'] ?? '';

         $old_pass_matches = false;

         // Check if stored password uses modern hashing or legacy types
         if (!empty($stored_password) && password_get_info($stored_password)['algo'] !== 0) {
            if (password_verify($old_pass, $stored_password)) {
               $old_pass_matches = true;
            }
         } else {
            if ($stored_password === sha1($old_pass) || $stored_password === md5($old_pass)) {
               $old_pass_matches = true;
            }
         }

         if ($old_pass_matches) {
            $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_pass = $conn->prepare("UPDATE `farmers` SET password = ? WHERE id = ?");
            $update_pass->execute([$hashed_new_pass, $farmer_id]);
            $message[] = 'Password updated successfully!';
         } else {
            $message[] = 'Incorrect old password entered!';
         }
      }
   }
}

// Fetch up-to-date user info
$select_profile = $conn->prepare("SELECT * FROM `farmers` WHERE id = ?");
$select_profile->execute([$farmer_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile - Farmer Portal</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/farmer_header.php'; ?>

<!-- Flash Message Container -->
<?php if (!empty($message) && is_array($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div class="message" style="position: sticky; top:0; max-width: 1200px; margin: 0 auto; padding:2rem; background: var(--white); display:flex; align-items:center; justify-content:space-between; gap:1.5rem; border-bottom: var(--border); z-index: 10000;">
         <span style="font-size: 2rem; color:var(--black);"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
         <i class="fas fa-times" style="font-size: 2.5rem; color:var(--red); cursor:pointer;" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<section class="form-container" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">

   <form action="" method="post" style="width: 100%; max-width: 50rem; background: var(--white); padding: 2.5rem; border-radius: .8rem; box-shadow: var(--box-shadow);">
      
      <h3 style="font-size: 2.5rem; text-transform: uppercase; color: var(--black); margin-bottom: 2rem; text-align: center;">Update Profile</h3>

      <div style="font-size: 1.5rem; color: var(--light-color); margin-bottom: .8rem;">Username</div>
      <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required placeholder="Enter your username" class="box" maxlength="50" style="width: 100%; margin-bottom: 1.5rem; padding: 1.2rem; font-size: 1.6rem; border: var(--border); border-radius: .5rem;">

      <div style="font-size: 1.5rem; color: var(--light-color); margin-bottom: .8rem;">Email Address</div>
      <input type="email" name="email" value="<?= htmlspecialchars($fetch_profile['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required placeholder="Enter your email" class="box" maxlength="50" style="width: 100%; margin-bottom: 1.5rem; padding: 1.2rem; font-size: 1.6rem; border: var(--border); border-radius: .5rem;">

      <div style="font-size: 1.5rem; color: var(--light-color); margin-bottom: .8rem;">Farm Location (include city, Nepal)</div>
      <input type="text" name="address" value="<?= htmlspecialchars($fetch_profile['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required placeholder="e.g. Pokhara-24, Kaski" class="box" maxlength="200" style="width: 100%; margin-bottom: 1.5rem; padding: 1.2rem; font-size: 1.6rem; border: var(--border); border-radius: .5rem;">

      <div style="font-size: 1.5rem; color: var(--light-color); margin-bottom: .8rem;">Old Password (leave blank if unchanged)</div>
      <input type="password" name="old_pass" placeholder="Enter previous password" class="box" maxlength="50" style="width: 100%; margin-bottom: 1.5rem; padding: 1.2rem; font-size: 1.6rem; border: var(--border); border-radius: .5rem;">

      <div style="font-size: 1.5rem; color: var(--light-color); margin-bottom: .8rem;">New Password (leave blank if unchanged)</div>
      <input type="password" name="new_pass" placeholder="Enter new password" class="box" maxlength="50" style="width: 100%; margin-bottom: 1.5rem; padding: 1.2rem; font-size: 1.6rem; border: var(--border); border-radius: .5rem;">

      <div style="font-size: 1.5rem; color: var(--light-color); margin-bottom: .8rem;">Confirm New Password</div>
      <input type="password" name="c_pass" placeholder="Confirm new password" class="box" maxlength="50" style="width: 100%; margin-bottom: 2rem; padding: 1.2rem; font-size: 1.6rem; border: var(--border); border-radius: .5rem;">

      <input type="submit" value="Update Now" name="submit" class="btn" style="width: 100%; background: var(--green); color: var(--white); padding: 1.2rem; font-size: 1.7rem; border-radius: .5rem; cursor: pointer; text-transform: uppercase;">

   </form>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
