<?php

include '../components/connect.php';

session_start();

// SHORTCUT LOGOUT LOGIC: If action=logout is detected, destroy session instantly
if(isset($_GET['action']) && $_GET['action'] == 'logout'){
   session_unset();
   session_destroy();
   header('location:farmer_login.php');
   exit();
}

if(isset($_POST['submit'])){

   // ✅ Modern PHP 8+ Sanitization
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $raw_pass = htmlspecialchars($_POST['pass'], ENT_QUOTES, 'UTF-8');
   $pass = md5($raw_pass);

   $select_farmer = $conn->prepare("SELECT * FROM `farmers` WHERE email = ? AND password = ?");
   $select_farmer->execute([$email, $pass]);
   $row = $select_farmer->fetch(PDO::FETCH_ASSOC);

   if($select_farmer->rowCount() > 0){
      $_SESSION['farmer_id'] = $row['id'];
      header('location:dashboard.php');
      exit();
   }else{
      $message[] = 'Incorrect username or password!';
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

<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message" style="position: fixed; top: 2rem; max-width: 1200px; width: 90%;">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="form-container" style="width: 100%; max-width: 40rem; background: var(--white); padding: 2rem; border-radius: .5rem; box-shadow: var(--box-shadow);">

   <form action="" method="post" style="text-align: center;">
      <h3 style="font-size: 2.5rem; color: var(--black); text-transform: uppercase; margin-bottom: 1rem;">Farmer Portal</h3>
      <input type="email" name="email" required placeholder="Enter email address" class="box" maxlength="100">
      <input type="password" name="pass" required placeholder="Enter your password" class="box" maxlength="50">
      <input type="submit" value="Access Dashboard" name="submit" class="btn">
      <p style="margin-top: 1.5rem; font-size: 1.6rem; color: var(--light-color);">New vendor? <a href="farmer_register.php" style="color: var(--purple);">Create account</a></p>
   </form>

</section>

</body>
</html>