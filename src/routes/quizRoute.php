<?php

require_once 'src/controllers/quizController.php';
require_once 'src/controllers/tokenController.php';

class QuizRoute {

  // Define the endpoints
  private $endpoints = [
    '/storeResult' => [
      'method' => 'POST',
      'process' => array(
        ['controller' => 'tokenController', 'function' => 'verify_token'],
        ['controller' => 'quizController', 'function' => 'store_result'],
      )
    ]
  ];

  public function dispatch ($endpoint, $method, $json_data) {

    // Check if the endpoint is valid
    $pattern = '/^\/[a-zA-Z]+$/';
    if (preg_match($pattern, $endpoint)) {

      // Check if the endpoint is valid
      if (array_key_exists($endpoint, $this->endpoints)) {

        // Check if the method is valid
        if ($method === $this->endpoints[$endpoint]['method']) {
          
          // Get needed data
          $token = $_COOKIE['quiz_user'] ?? null;
          $process_array = $this->endpoints[$endpoint]['process'];
          $TEMP_DATA = [];
  
          // Execute the controller functions
          foreach ($process_array as $process) {
            $controller = new $process['controller']();
            match ($process['function']) {
              'verify_token' => $controller->verify_token($token, $TEMP_DATA),
              'store_result' => $controller->store_result($TEMP_DATA),
              default => $this->error_handler("Invalid_function", $process['function'])
            };
          }

          // Set cookie and return response to frontend
          http_response_code(200);
          switch ($endpoint) {
            case '/storeResult':
              echo json_encode([
                "success" => true,
                "data" => [
                  "newRecord" => $TEMP_DATA['new_record']
                ]
              ]);
              break;
            default: $this->error_handler("Invalid_endpoint", $endpoint);
          };
          exit;
        } else $this->error_handler("Invalid_method", $method);
      } else $this->error_handler("Invalid_endpoint", $endpoint);
    } else $this->error_handler("Invalid_endpoint", $endpoint);
  }

  // Error handler
  public function error_handler ($message, $variable_array) {
    error_log("Error Message: " . $message . " Variables: " . json_encode($variable_array));
    http_response_code(200);
    echo json_encode([
      "success" => false,
      "error" => $message,
      "variables" => $variable_array
    ]);
    exit;
  }
}

?>