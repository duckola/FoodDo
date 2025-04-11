<?php
// Start the session first to access session variables
session_start();

$_SESSION = array(); // Clears the $_SESSION array

// 2. Destroy the session cookie (optional but recommended)
// If you are using cookies to store the session ID (default behavior),
// this step helps ensure the cookie is removed from the user's browser.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, // Set expiry time in the past
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session data on the server
session_destroy();

// 4. Redirect the user to the login page (or landing page)
// Redirecting to index.php which contains the login form
header("Location: index.php");
exit(); // Ensure no further code is executed after redirection
?>
