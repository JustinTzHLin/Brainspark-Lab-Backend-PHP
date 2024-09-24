<?php

// Create the connection string
$HOST = $_ENV['HOST'];
$DBNAME = $_ENV['DBNAME'];
$USER = $_ENV['USER'];
$PASSWORD = $_ENV['PASSWORD'];

echo "$HOST, $DBNAME, $USER, $PASSWORD\n";
// Connect to the database
$conn = pg_connect("host=$HOST dbname=$DBNAME user=$USER password=$PASSWORD")
  or die('Could not connect: ' . pg_last_error());

// Check if the connection was successful
if (!$conn) {
  die("An error occurred while connecting to the ElephantSQL database.");
}

// Return the connection resource
return $conn;

?>