<?php

require 'src/controllers/userController.php';

class UserRoute {

  // Define the endpoints
  private $endpoints = [
    '/signIn' => [
      'methoid' => 'POST',
      'process' => array(
        ['controller' => 'userController', 'method' => 'verrify_user'],
        ['controller' => 'tokenController', 'method' => 'issueToken'],
      )
    ]
  ];

  public function dispatch ($endpoint, $method) {

    // Check if the endpoint is valid
    $pattern = '/^\/[a-zA-Z]+$/';
    if (preg_match($pattern, $endpoint)) {
      echo "$endpoint, $method\n";

      // Check if the endpoint is valid
      if (array_key_exists($endpoint, $this->endpoints)) {
        // Execute the controller functions
      } else $this->error_handler("Invalid endpoint", $endpoint);
    } else $this->error_handler("Invalid endpoint", $endpoint);
  }

  // Error handler
  public function error_handler ($message, $variable) {
    echo "$message: $variable\n";
  }
}

?>