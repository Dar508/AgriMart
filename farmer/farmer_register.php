<?php

include '../components/connect.php';

session_start();

if(isset($_POST['submit'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
   $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = md5($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   // Check if the email is already registered
   $select_farmer = $conn->prepare("SELECT * FROM `farmers` WHERE email = ?");
   $select_farmer->execute([$email]);

   if($select_farmer->rowCount() > 0){
      $message[] = 'Email address already registered!';
   }else{
      if($pass != $cpass){
         $message[] = 'Confirm password does not match!';
      }else{
         $insert_farmer = $conn->prepare("INSERT INTO `farmers`(name, email, password, phone, address) VALUES(?,?,?,?,?)");
         $insert_farmer->execute([$name, $email, $pass, $phone, $address]);
         $message[] = 'Registered successfully! Log in now.';
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

<section class="form-container" style="width: 100%; max-width: 50rem; background: var(--white); padding: 2rem; border-radius: .5rem; box-shadow: var(--box-shadow);">

   <form action="" method="post" style="text-align: center;">
      <h3 style="font-size: 2.5rem; color: var(--black); text-transform: uppercase; margin-bottom: 1rem;">Farmer Register</h3>
      <input type="text" name="name" required placeholder="Enter full name" class="box" maxlength="100">
      <input type="email" name="email" required placeholder="Enter email address" class="box" maxlength="100">
      <input type="text" name="phone" required placeholder="Enter phone number" class="box" maxlength="15">
      <input type="text" name="address" required placeholder="Enter farm location address" class="box" maxlength="200">
      <input type="password" name="pass" required placeholder="Create secure password" class="box" maxlength="50">
      <input type="password" name="cpass" required placeholder="Confirm password" class="box" maxlength="50">
      <input type="submit" value="Register Now" name="submit" class="btn">
      <p style="margin-top: 1.5rem; font-size: 1.6rem; color: var(--light-color);">Already have an account? <a href="farmer_login.php" style="color: var(--purple);">Login here</a></p>
   </form>

</section>

</body>
</html>