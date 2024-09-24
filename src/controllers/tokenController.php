<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

Class TokenController {

  private $JWT_SECRET;

  public function __construct(){
    $this->JWT_SECRET = $_ENV['JWT_SECRET'];
  }

  public function issue_token (&$TEMP_DATA) {
    echo json_encode($TEMP_DATA) . "\n";

    // Create the JWT token
    $payload = [
      'iat' => time(),
      'exp' => time() + 60 * 60,
      'user_id' => $TEMP_DATA['user_id'],
      'email' => $TEMP_DATA['email']
    ];
    $jwt = JWT::encode($payload, $this->JWT_SECRET, 'HS256');
    echo json_encode($jwt) . "\n";
    $TEMP_DATA['quiz_user'] = $jwt;
  }
}

?>