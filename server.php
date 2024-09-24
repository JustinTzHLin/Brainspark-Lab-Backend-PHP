<?php

// Set the CORS headers and Require the router
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Content-Type: application/json');
require 'vendor/autoload.php';

// Load the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env.local');
$dotenv->load();

// Require the router
require 'router.php';

// Get the raw POST data and Decode the JSON data
$json_data = json_decode(file_get_contents('php://input'), true);
echo json_encode($json_data) . "\n";

// Get the request URI and request method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];
// echo "<script>console.log(".json_encode($_SERVER).");</script>\n";
echo "$requestUri, $requestMethod\n";

// Dispatch the request
$router = new Router();
$router->dispatch($requestUri, $requestMethod, $json_data);
echo "finish\n";
?>