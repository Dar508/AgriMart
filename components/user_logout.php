<?php

include 'connect.php';

session_start();

// Unset all session variables
$_SESSION = array();

// If session cookie exists, destroy it in the browser as well
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destroy the server-side session
session_destroy();

// Redirect user to home page
header('location:../home.php');
exit();

?>