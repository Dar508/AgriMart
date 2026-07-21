<?php

include 'connect.php';

session_start();

// Clear all session variables
$_SESSION = array();

// Remove the session cookie from the browser if present
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

// Destroy server-side session data
session_destroy();

// Redirect to farmer login page
header('location:../farmer/farmer_login.php');
exit();

?>