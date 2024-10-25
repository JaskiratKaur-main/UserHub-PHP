<?php
//on applying these headers on this config page which is being included in all other pages so it will automatically be applied to all pages of the project
//for adding external scripts like bootstrap, jquery scripts and preventing content security policy to block them, so to avoid this follow these steps:
  //a.) move inline scripts to external js file
  //b.) make an .htaccess file in your project directory where your index file is located
  //c.) write :
    // <IfModule mod_headers.c>
        # Correct redirect syntax - relative path just for checking if htaccess file is working
        // Redirect 301 /day-13/day-13/test.html http://www.google.com

        # Correct Content Security Policy for external scripts
        // Header set Content-Security-Policy "script-src 'self' https://cdn.jsdelivr.net https://ajax.googleapis.com https://code.jquery.com https://cdnjs.cloudflare.com;"
    // </IfModule>
  //d.) now check if these lines are commented in wamp->apache->httpd.conf file if yes uncomment them i.e. no hash in front of them
    //LoadModule headers_module modules/mod_headers.so
    //LoadModule rewrite_module modules/mod_rewrite.so
  //e.) add this in httpd.conf file => 
    //this is for redirect specifically
    //<Directory "C:/wamp64/www/">
      //AllowOverride All
    //</Directory>
    //this is for htaccess
    //<Directory "C:/wamp64/www/day-13/day-13/">
      //AllowOverride All
    //</Directory>
  //f.) lastly restart the wamp or apache services
header("Content-Security-Policy: script-src 'self';");//This limits the sources from which scripts can be loaded. preventing xss
// Without MIME Sniffing Prevention: The browser might accept a file that appears to be one type based on its content, even if its declared type is something else.
// With MIME Sniffing Prevention: The browser will only accept the file if the content and the declared type match exactly, enhancing security.
header("X-Content-Type-Options: nosniff");

define("MYSQL_HOST", "localhost");
define("MYSQL_USERNAME", "root");
define("MYSQL_PASSWORD", "waheguru@4417");
define("MYSQL_DATABASE", "crud_php");

// Create connection
$conn = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
