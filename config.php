<?php
ini_set("display_errors", true);
date_default_timezone_set("Africa/Nairobi");

// 1. Get database details from Render's environment, or use defaults for local WAMP
$host = getenv('DB_HOST') ?: "localhost";
$dbname = getenv('DB_NAME') ?: "task_manager";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "yourpassword";
$port = getenv('DB_PORT') ?: "3306";

// 2. Dynamically build the DSN string
define("DB_DSN", "mysql:host=$host;port=$port;dbname=$dbname");
define("DB_USERNAME", $user);
define("DB_PASSWORD", $pass);

define("CLASS_PATH", "Classes");
define("TEMPLATE_PATH", "Templates");
define("ADMIN_USERNAME","root");
define("ADMIN_PASSWORD_HASH", '$2y$10$Ff4ErMRAVe0VMWkP5p5auuF9KvMtUCUs5ZMztE/6w6Vo8aRuCgNOS');
define("JWT_SECRET", "random"); 

require(CLASS_PATH . "/User.php");

function handleException( $exception ) {
    echo "Sorry, a problem occurred. Please try again later. ", $exception->getMessage();
    error_log( $exception->getMessage() );
}

set_exception_handler( 'handleException' );
?>