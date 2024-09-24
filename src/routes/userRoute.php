<?php

require 'src/controllers/userController.php';
require 'src/controllers/tokenController.php';

class UserRoute {

  // Define the endpoints
  private $endpoints = [
    '/signIn' => [
      'methoid' => 'POST',
      'process' => array(
        ['controller' => 'userController', 'method' => 'verrify_user'],
        ['controller' => 'tokenController', 'method' => 'issue_token'],
      )
    ]
  ];

  public function dispatch ($endpoint, $method, $json_data) {

    // Check if the endpoint is valid
    $pattern = '/^\/[a-zA-Z]+$/';
    if (preg_match($pattern, $endpoint)) {
      echo "$endpoint, $method\n";

      // Check if the endpoint is valid
      if (array_key_exists($endpoint, $this->endpoints)) {

        // Get needed data
        $email = $json_data['email'];
        $password = $json_data['password'];
        $process_array = $this->endpoints[$endpoint]['process'];
        $TEMP_DATA = [];

        // Execute the controller functions
        foreach ($process_array as $process) {
          $controller = new $process['controller']();
          match ($process['method']) {
            'verrify_user' => $controller->verrify_user($email, $password, $TEMP_DATA),
            'issue_token' => $controller->issue_token($TEMP_DATA),
          };
        }

        // Execute the controller functions
        http_response_code(200);
        setcookie("quiz_user", $TEMP_DATA['quiz_user'], time() + 60 * 60); 
        echo json_encode([
          "success" => true,
          "data" => [
            "user" => $TEMP_DATA['user']
          ]
        ]);
        exit;

      } else $this->error_handler("Invalid endpoint", $endpoint);
    } else $this->error_handler("Invalid endpoint", $endpoint);
  }

  // Error handler
  public function error_handler ($message, $variable) {
    echo "$message: $variable\n";
    http_response_code(404);
    echo json_encode([
      "success" => false,
      "error" => $message
    ]);
    exit;
  }
}

?>