<?php

Class UserController {

  private $conn;
  
  public function __construct(){
    $this->conn = require __DIR__ . '/../configs/elephantSQL.php';
  }

  public function verrify_user ($email, $password, &$TEMP_DATA) {
    try {

      // Query database for existing user with input email
      $verify_user_SQL = "SELECT * FROM users WHERE email=$1;";
      $user_data = pg_fetch_all(pg_query_params($this->conn, $verify_user_SQL, array($email)));

      // Return error when usrename isn't existed
      if (Count($user_data) === 0) $this->error_handler("User not existed", [$email]);
      
      // Compare password using compare function
      $compare_password_result = password_verify($password, $user_data[0]['password']);
      if ($compare_password_result) {

        // Update last visited time after logging in
        $update_last_visited_SQL = 'UPDATE users SET last_visited=CURRENT_TIMESTAMP WHERE email=$1 Returning *;';
        $new_user_data = pg_fetch_all(pg_query_params($this->conn, $update_last_visited_SQL, array($email)));

        // Generate variables for next middleware
        $TEMP_DATA['user'] = $new_user_data[0];
        $TEMP_DATA['email'] = $new_user_data[0]['email'];
        $TEMP_DATA['user_id'] = $new_user_data[0]['id'];

        // Return error when password doesn't match
      } else $this->error_handler("Password is not valid", array($password));
      pg_close($this->conn);
    } catch (Exception $e) {
      pg_close($this->conn);
      $this->error_handler($e->getMessage(), array($email, $password));
    }
  }

  public function error_handler ($message, $variable_array) {
    http_response_code(404);
    echo json_encode([
      "success" => false,
      "error" => $message
    ]);
    exit;
  }
}

?>