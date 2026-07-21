<?php

session_start();

// Unset all session variables
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

// Redirect to admin login page
header('location:../admin/admin_login.php');
exit();

?>