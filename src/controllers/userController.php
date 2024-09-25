<?php

use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

Class UserController {

  private $conn;

  private $JWT_SECRET;
  
  public function __construct(){
    $this->conn = require __DIR__ . '/../configs/elephantSQL.php';
    $this->JWT_SECRET = $_ENV['JWT_SECRET'];
  }

  public function verrify_user ($email, $password, &$TEMP_DATA) {
    try {

      // Query database for existing user with input email
      $verify_user_SQL = "SELECT * FROM users WHERE email=$1;";
      $user_data = pg_fetch_all(pg_query_params($this->conn, $verify_user_SQL, array($email)));

      // Return error when usrename isn't existed
      if (Count($user_data) === 0) $this->error_handler("User_not_existed", [$email]);
      
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
      } else $this->error_handler("Password_is_not_valid", array($password));
      pg_close($this->conn);
    } catch (Exception $e) {
      pg_close($this->conn);
      $this->error_handler($e->getMessage(), array($email, $password));
    }
  }

  public function create_user ($username, $email, $password, &$TEMP_DATA) {
    try {

      // Check if email is existed in input
      if ($email === null) $this->error_handler("Email_not_existed", array($email));

      // Return error when email existed
      $unique_email_SQL = `SELECT * FROM users WHERE email=$1 AND oauth_provider=$2;`;
      $unique_email_data = pg_fetch_all(pg_query_params($this->conn, $unique_email_SQL, array($email, 'none')));
      if (Count($unique_email_data) !== 0) $this->error_handler("Email_already_existed", array($email));

      // Hash password and create new user
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $create_user_SQL = 'INSERT INTO users (username, email, password, oauth_provider) VALUES ($1, $2, $3, $4) Returning *;';
      $new_user_data = pg_fetch_all(pg_query_params($this->conn, $create_user_SQL, array($username, $email, $hashed_password, 'none')));

      // Generate variables for next middleware
      $TEMP_DATA['new_user'] = $new_user_data[0];
      $TEMP_DATA['email'] = $new_user_data[0]['email'];
      $TEMP_DATA['user_id'] = $new_user_data[0]['id'];
      pg_close($this->conn);
    } catch (Exception $e) {
      pg_close($this->conn);
      $this->error_handler($e->getMessage(), array($email, $password));
    }
  }

  public function confirm_registration ($email) {

    // Get SMTP variables
    $SMTP_EMAIL = $_ENV['SMTP_EMAIL'];
    $SMTP_PASSWORD = $_ENV['SMTP_PASSWORD'];
    $FRONTEND_URL = $_ENV['FRONTEND_URL'];

    // Create the JWT token
    $payload = [
      'iat' => time(),
      'exp' => time() + 60 * 60,
      'email' => $email
    ];
    $token = JWT::encode($payload, $this->JWT_SECRET, 'HS256');

    $mail = new PHPMailer(true);

    try {
      // Server settings
      $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;

      // Sender and recipient settings
      $mail->Username = $SMTP_EMAIL;
      $mail->Password = $SMTP_PASSWORD;
      $mail->setFrom($SMTP_EMAIL, 'Trivioasis');
      $mail->addAddress($email);

      // Content
      $mail->isHTML(true);
      $mail->Subject = 'Welcome to Trivioasis!';
      $mail->Body = "<h1>Welcome to Trivioasis!</h1>" .
        "<p>We're excited to have you join our community of curious minds.</p>" .
        "<p>To complete your signup and start exploring Trivioasis, please click the link below:</p>" .
        "<p><a href='$FRONTEND_URL?token=$token'>Complete Signup</a></p>" .
        "<p>This link is only available for 1 hour. After that, you may need to request a new signup link if the original one expires.</p>" .
        "<p>If you have any questions or need assistance, please don't hesitate to contact us at trivioasis@gmail.com.</p>" .
        "<p>Happy quizzing!</p>";
      $mail->send();
    } catch (PHPMailerException $e) {
      $this->error_handler($mail->ErrorInfo, array($email, $password));
    }
  }

  public function confirm_email ($email, &$TEMP_DATA) {
    try {

      // Check if email is existed in input
      $unique_email_SQL = 'SELECT * FROM users WHERE email=$1 AND oauth_provider=$2;';
      $unique_email_data = pg_fetch_all(pg_query_params($this->conn, $unique_email_SQL, array($email, 'none')));
      if (Count($unique_email_data) === 0) $TEMP_DATA['email_result'] = 'email_not_existed';
      else $TEMP_DATA['email_result'] = 'email_existed';
      pg_close($this->conn);
    } catch (Exception $e) {
      pg_close($this->conn);
      $this->error_handler($e->getMessage(), array($email));
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