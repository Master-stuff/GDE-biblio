<<<<<<< HEAD
<?php 

require './vendor/autoload.php';
=======
<?php

/**
 * Authentication Helper (Legacy)
 * 
 * This file is kept for backward compatibility but should be replaced
 * with AuthMiddleware in new code.
 * 
 * @deprecated Use AuthMiddleware instead
 */

require './vendor/autoload.php';

if (empty($_ENV['SECRET_KEY'])) {
    http_response_code(500);
    echo json_encode(["error" => "Server configuration error"]);
    exit;
}

>>>>>>> be33fc8 (fixing my git)
$secretKey = $_ENV['SECRET_KEY'];
$jwtManager = new JwtManager($secretKey);

$headers = getallheaders();
$auth = $headers["Authorization"] ?? "";

<<<<<<< HEAD
if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
=======
if (!preg_match('/Bearer\s+(\S+)/', $auth, $matches)) {
>>>>>>> be33fc8 (fixing my git)
    http_response_code(401);
    echo json_encode(["error" => "Missing or invalid token"]);
    exit;
}

<<<<<<< HEAD

$token = $matches[1];
=======
$token = $matches[1];

if (!$jwtManager->validateToken($token)) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid or expired token"]);
    exit;
}

>>>>>>> be33fc8 (fixing my git)
$payload = $jwtManager->decodeToken($token);

if (!$payload) {
    http_response_code(401);
<<<<<<< HEAD
    echo json_encode($payload);
    echo json_encode(["error" => "Invalid or expired token"]);
    exit;
}
=======
    echo json_encode(["error" => "Invalid or expired token"]);
    exit;
}
>>>>>>> be33fc8 (fixing my git)
