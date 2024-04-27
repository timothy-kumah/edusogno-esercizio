<?php
declare(strict_types=1);

spl_autoload_register(function($class){
    require __DIR__."/Controllers/$class.php";
});

set_exception_handler("CustomExceptionHandler::handleException");
set_error_handler("CustomExceptionHandler::handleError");

header("Content-type:application/json; charset=UTF-8");
$parts = explode("/", $_SERVER['REQUEST_URI']);
$database = new Database("localhost", "eventdb","root","");

$eventRepository = new EventRepository($database);
$authRepository = new AuthRepository($database);
$eventController = new EventController($eventRepository,$authRepository);

$authController = new AuthController($authRepository);

if ($parts[4] == "events"){
    $id = $parts[5] ?? null; 
    $eventController->processRequest($_SERVER["REQUEST_METHOD"],$id);
}elseif ($parts[4] == "auth") {
    $authController->processRequest($_SERVER["REQUEST_METHOD"], $parts[5]);
}else{
    http_response_code(404);
    exit;
}

