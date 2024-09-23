<?php

// Set the CORS headers and Require the router
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Content-Type: application/json');
require 'router.php';

// Get the raw POST data and Decode the JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Get the request URI and request method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];
// echo "<script>console.log(".json_encode($_SERVER).");</script>\n";
echo "$requestUri, $requestMethod\n";

// Dispatch the request
$router = new Router();
$router->dispatch($requestUri, $requestMethod);
echo "finish\n";
?>