<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';
$message = [];

// Retain input values across postbacks
$name   = '';
$email  = '';
$number = '';
$msg    = '';

if (isset($_POST['send'])) {

   // Trim raw input values
   $name   = trim($_POST['name'] ?? '');
   $email  = trim($_POST['email'] ?? '');
   $number = trim($_POST['number'] ?? '');
   $msg    = trim($_POST['msg'] ?? '');

   $valid_email = filter_var($email, FILTER_VALIDATE_EMAIL);

   if (!$valid_email) {
      $message[] = 'Please enter a valid email address!';
   } elseif (empty($name) || empty($number) || empty($msg)) {
      $message[] = 'Please fill out all required fields!';
   } else {

      // Check for duplicate messages
      $select_message = $conn->prepare("SELECT id FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
      $select_message->execute([$name, $email, $number, $msg]);

      if ($select_message->rowCount() > 0) {
         $message[] = 'You have already sent this message!';
      } else {

         // Insert message safely with parameterized PDO
         $insert_message = $conn->prepare("INSERT INTO `messages` (user_id, name, email, number, message) VALUES (?, ?, ?, ?, ?)");
         $insert_message->execute([$user_id, $name, $email, $number, $msg]);

         $message[] = 'Message sent successfully!';

         // Reset form inputs after successful submission
         $name = $email = $number = $msg = '';
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
   <title>Contact Us</title>
   
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- Display System Alerts -->
<?php
if (!empty($message) && is_array($message)) {
   foreach ($message as $msg_text) {
      echo '
      <div class="message">
         <span>' . htmlspecialchars($msg_text, ENT_QUOTES, 'UTF-8') . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="contact">

   <form action="" method="post">
      <h3>Get In Touch</h3>
      
      <input type="text" name="name" placeholder="Enter your name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required maxlength="50" class="box">
      
      <input type="email" name="email" placeholder="Enter your email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required maxlength="50" class="box">
      
      <input type="tel" name="number" placeholder="Enter your phone number" value="<?= htmlspecialchars($number, ENT_QUOTES, 'UTF-8'); ?>" required maxlength="15" pattern="[0-9]{7,15}" class="box">
      
      <textarea name="msg" class="box" placeholder="Enter your message..." required maxlength="500" cols="30" rows="10"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></textarea>
      
      <input type="submit" value="Send Message" name="send" class="btn">
   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>