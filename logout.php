<?php 

header("Content-Security-Policy: script-src 'self';");//This limits the sources from which scripts can be loaded. preventing xss
// Without MIME Sniffing Prevention: The browser might accept a file that appears to be one type based on its content, even if its declared type is something else.
// With MIME Sniffing Prevention: The browser will only accept the file if the content and the declared type match exactly, enhancing security.
header("X-Content-Type-Options: nosniff");

// Start the session
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: login.php"); // Change this to your login page
exit();



?>