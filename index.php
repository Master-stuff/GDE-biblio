<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

require './bootstrap.php';

header("Content-type: application/json; charset=UTF-8");

$parts = explode("/",$_SERVER['REQUEST_URI']);

if ($parts[2] == "books") {
    
    $id = $parts[3] ?? null;
    
    $gateway = new BookGateway($database);
    
    $controller = new BookController($gateway);
    
    $controller->processRequest($_SERVER['REQUEST_METHOD'], $id);

} else if ($parts[2] == "users") {

    $request = [$parts[3] ?? null, $parts[4] ?? null];
    
    $gateway = new AuthGateway($database);

    $gatebook = new BookGateway($database);
    
    $controller = new AuthController($gateway, $gatebook);
    
    $controller->processRequest($request, $_SERVER['REQUEST_METHOD']);

}else if ($parts[2] == "loans"){
    $request = [$parts[3] ?? null, $parts[4] ?? null];
    
    $gateway = new LoansGateway($database);

    $gatebook = new BookGateway($database);
    
    $controller = new LoansController($gateway, $gatebook);
    
    $controller->processRequest($request, $_SERVER['REQUEST_METHOD']);
} else {
    http_response_code(404);
    exit;
}