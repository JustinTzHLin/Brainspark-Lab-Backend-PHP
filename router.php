<?php

// Require all routes
require 'src/routes/userRoute.php';

// Create the router
class Router {

  // Define the routes
  private $routes = [
    '/user' => 'userRoute',
    '/quiz' => 'quizRoute'
  ];

  public function dispatch ($uri, $method, $json_data) {

    // Check if the uri is valid
    $pattern = '/^\/[a-zA-Z]+\/[a-zA-Z]+$/';
    if (preg_match($pattern, $uri)) {

      // Get the route and endpoint
      $route = substr($uri, 0, strpos($uri, '/', 1));
      $endpoint = substr($uri, strpos($uri, '/', 1));

      // Check if the route is valid
      if (array_key_exists($route, $this->routes)) {
        $userRoute = new $this->routes[$route]();
        $userRoute->dispatch($endpoint, $method, $json_data);
      } else $this->error_handler("Invalid_route", $route);
    } else $this->error_handler("Invalid_uri", $uri);
  }

  // Error handler
  public function error_handler ($message, $variable) {
    error_log("Error Message: " . $message . " Variables: " . json_encode($variable));
    http_response_code(200);
    echo json_encode([
      "success" => false,
      "error" => $message
    ]);
    exit;
  }
}

?>