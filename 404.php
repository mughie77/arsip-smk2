<?php
// 404.php
// Set the HTTP response code to 404 Not Found
http_response_code(404);

// Set the 'code' parameter for the error page
$_GET['code'] = '404';

// Include the generic error page template
require 'error.php';
?>
