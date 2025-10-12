<?php
<<<<<<< HEAD
require dirname(__DIR__)  . '/api/vendor/autoload.php';
=======

require dirname(__DIR__) . '/api/vendor/autoload.php';
>>>>>>> be33fc8 (fixing my git)

set_error_handler('ErrorHandler::handleError');
set_exception_handler('ErrorHandler::handleException');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__ . "/api/"));
$dotenv->load();

<<<<<<< HEAD
header("Content-type: application/json; charset=UTF-8");

=======
$required_env = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'SECRET_KEY'];
/*foreach ($required_env as $var) {
    if (empty($_ENV[$var])) {
        throw new RuntimeException("Missing required environment variable: {$var}");
    }
}*/

if (strlen($_ENV['SECRET_KEY']) < 32) {
    throw new RuntimeException("SECRET_KEY must be at least 32 characters for security");
}

header("Content-type: application/json; charset=UTF-8");
>>>>>>> be33fc8 (fixing my git)

$database = new Database(
    $_ENV["DB_HOST"],
    $_ENV["DB_NAME"],
    $_ENV["DB_USER"],
    $_ENV["DB_PASS"]
);
