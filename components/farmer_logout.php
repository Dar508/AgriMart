<?php

include 'connect.php';

session_start();
session_unset();
session_destroy();

header('location:../farmer/farmer_login.php');
exit();

?>