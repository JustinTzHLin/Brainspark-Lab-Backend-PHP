<?php

require_once 'src/controllers/userController.php';
require_once 'src/controllers/tokenController.php';

class UserRoute {

  // Define the endpoints
  private $endpoints = [
    '/signIn' => [
      'method' => 'POST',
      'process' => array(
        ['controller' => 'userController', 'function' => 'verrify_user'],
        ['controller' => 'tokenController', 'function' => 'issue_token'],
      )
    ],
    '/signUp' => [
      'method' => 'POST',
      'process' => array(
        ['controller' => 'userController', 'function' => 'create_user'],
        ['controller' => 'tokenController', 'function' => 'issue_token'],
      )
    ],
    '/confirmRegistration' => [
      'method' => 'POST',
      'process' => array(
        ['controller' => 'userController', 'function' => 'confirm_registration']
      )
    ],
    '/emailConfirm' => [
      'method' => 'POST',
      'process' => array(
        ['controller' => 'userController', 'function' => 'confirm_email']
      )
    ],
    '/tokenConfirm' => [
      'method' => 'POST',
      'process' => array(
        ['controller' => 'tokenController', 'function' => 'confirm_token']
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
          $username = $json_data['username'] ?? null;
          $email = $json_data['email'] ?? null;
          $password = $json_data['password'] ?? null;
          $email_token = $json_data['token'] ?? null;
          $process_array = $this->endpoints[$endpoint]['process'];
          $TEMP_DATA = [];
  
          // Execute the controller functions
          foreach ($process_array as $process) {
            $controller = new $process['controller']();
            match ($process['function']) {
              'verrify_user' => $controller->verrify_user($email, $password, $TEMP_DATA),
              'issue_token' => $controller->issue_token($TEMP_DATA),
              'create_user' => $controller->create_user($username, $email, $password, $TEMP_DATA),
              'confirm_registration' => $controller->confirm_registration($email),
              'confirm_email' => $controller->confirm_email($email, $TEMP_DATA),
              'confirm_token' => $controller->confirm_token($email_token, $TEMP_DATA),
              default => $this->error_handler("Invalid_function", $process['function'])
            };
          }
  
          // Set cookie and return response to frontend
          http_response_code(200);
          switch ($endpoint) {
            case '/signIn':
              setcookie("quiz_user", $TEMP_DATA['quiz_user'], time() + 60 * 60); 
              echo json_encode([
                "success" => true,
                "data" => [
                  "user" => $TEMP_DATA['user']
                ]
              ]);
              break;
            case '/signUp':
              setcookie("quiz_user", $TEMP_DATA['quiz_user'], time() + 60 * 60); 
              echo json_encode([
                "success" => true,
                "data" => [
                  "newUser" => $TEMP_DATA['new_user']
                ]
              ]);
              break;
            case '/confirmRegistration':
              echo json_encode([
                "success" => true,
                "message" => "Email sent successfully"
              ]);
              break;
            case '/emailConfirm':
              echo json_encode([
                "success" => true,
                "data" => [
                  "result" => $TEMP_DATA['email_result']
                ]
              ]);
              break;
            case '/tokenConfirm':
              echo json_encode([
                "success" => true,
                "data" => [
                  "useremail" => $TEMP_DATA['useremail'] 
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