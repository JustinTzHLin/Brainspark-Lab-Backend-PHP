<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firvase\JWT\ExpiredException;
// use UnexpectedValueException;

Class TokenController {

  private $JWT_SECRET;

  public function __construct(){
    $this->JWT_SECRET = $_ENV['JWT_SECRET'];
  }

  public function issue_token (&$TEMP_DATA) {
    try {
      // Create the JWT token
      $payload = [
        'iat' => time(),
        'exp' => time() + 60 * 60,
        'user_id' => $TEMP_DATA['user_id'],
        'email' => $TEMP_DATA['email']
      ];
      $jwt = JWT::encode($payload, $this->JWT_SECRET, 'HS256');
      $TEMP_DATA['quiz_user'] = $jwt;
    } catch (Exception $e) {
      $this->error_handler($e->getMessage(), array_values($TEMP_DATA));
    }
  }

  public function verify_token ($token, &$TEMP_DATA) {
    try {

      // Decode the JWT token
      if ($token === null) $this->error_handler("Token_not_existed", array($token));
      $payload = JWT::decode($token, new Key($this->JWT_SECRET, 'HS256'));

      // Generate variables for next middleware
      $TEMP_DATA['user_id'] = $payload->user_id;
      $TEMP_DATA['email'] = $payload->email;
    } catch (ExpiredException $e) {
      $this->error_handler("Token_expired", array($token));
    } catch (Exception $e) {
      $this->error_handler($e->getMessage(), array($token));
    }
  }

  public function confirm_token ($email_token, &$TEMP_DATA) {
    try {

      // Decode the JWT token for registration
      if ($email_token = null) $this->error_handler("Token_not_existed", array($email_token));
      $payload = JWT::decode($email_token, new Key($this->JWT_SECRET, 'HS256'));

      // Generate variables for next middleware
      $TEMP_DATA['useremail'] = $payload->email;
    } catch (ExpiredException $e) {
      $this->error_handler("Token_expired", array($email_token));
    } catch (Exception $e) {
      $this->error_handler($e->getMessage(), array($email_token));
    }
  }

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