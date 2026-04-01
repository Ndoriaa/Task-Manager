<?php
ini_set("display errors ",true);
date_default_timezone_set("Africa/Nairobi");
define("DB_DSN", "mysql:host=localhost;dbname=task_manager");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "yourpassword");
define("CLASS_PATH", "Classes");
define("TEMPLATE_PATH", "Templates");
define("ADMIN_USERNAME","root");
define("ADMIN_PASSWORD_HASH", '$2y$10$Ff4ErMRAVe0VMWkP5p5auuF9KvMtUCUs5ZMztE/6w6Vo8aRuCgNOS');
define("JWT_SECRET", "random"); 
require(CLASS_PATH . "/User.php");

function handleException( $exception ) {
    echo "Sorry, a problem occurred. Please try again later.", $exception->getMessage();
    
    error_log( $exception->getMessage() );
}

set_exception_handler( 'handleException' );
?>