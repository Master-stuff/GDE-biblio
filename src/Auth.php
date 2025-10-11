<?php 

require './vendor/autoload.php';
$secretKey = $_ENV['SECRET_KEY'];
$jwtManager = new JwtManager($secretKey);

$headers = getallheaders();
$auth = $headers["Authorization"] ?? "";

if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode(["error" => "Missing or invalid token"]);
    exit;
}


$token = $matches[1];
$payload = $jwtManager->decodeToken($token);

if (!$payload) {
    http_response_code(401);
    echo json_encode($payload);
    echo json_encode(["error" => "Invalid or expired token"]);
    exit;
}