<?php 
class AuthController {
    public function __construct(private AuthGateway $gateway, private BookGateway $gatebook)
    {
        
    }

    public function processRequest(array $request, string $method) {
        require './vendor/autoload.php';
        $secretKey = $_ENV['SECRET_KEY'];
        $jwtManager = new JwtManager($secretKey);

        header('Content-Type: application/json');

        switch ($request[0]) {
            case "register":
                if ($method !== "POST") {
                    http_response_code(405);
                    echo json_encode(["error" => "Method not allowed"]);
                    return;
                }

                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = ValidationErrors::validateRegister($data, $this->gateway);
                if (!empty($errors)) {
                    http_response_code(400);
                    echo json_encode(["errors" => $errors]);
                    return;
                }

                // Hash password
                $data["password"] = password_hash($data["password"], PASSWORD_BCRYPT);

                // TODO: add in AuthGateway: createUser($data)
                $created = $this->gateway->createUser($data);

                if ($created) {
                    http_response_code(201);
                    echo json_encode(["message" => "User registered successfully"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "User registration failed"]);
                }
                break;

            case "login":
                if ($method !== "POST") {
                    http_response_code(405);
                    echo json_encode(["error" => "Method not allowed"]);
                    return;
                }

                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = ValidationErrors::validateLogin($data);
                if (!empty($errors)) {
                    http_response_code(400);
                    echo json_encode(["errors" => $errors]);
                    return;
                }

                // TODO: in AuthGateway implement verifyUser($username, $password)
                $user = $this->gateway->verifyUser($data);

                if ($user) {
                    $token = $jwtManager->createToken([
                        "id"       => $user["id"],
                        "email" => $user["email"]
                    ]);

                    echo json_encode([
                        "message" => "Login successful",
                        "token"   => $token
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(["error" => "Invalid credentials"]);
                }
                break;

            case "me":
                if ($method !== "GET") {
                    http_response_code(405);
                    echo json_encode(["error" => "Method not allowed"]);
                    return;
                }

                $headers = getallheaders();
                $auth = $headers["Authorization"] ?? "";

                if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
                    http_response_code(401);
                    echo json_encode(["error" => "Missing or invalid token"]);
                    return;
                }


                $token = $matches[1];
                $payload = $jwtManager->decodeToken($token);

                if (!$payload) {
                    http_response_code(401);
                    echo json_encode($payload);
                    echo json_encode(["error" => "Invalid or expired token"]);
                    return;
                }

                if ($request[1] === "books") {
                    echo json_encode($this->gatebook->getByOwner($payload['id']));
                } else {
                    echo json_encode($this->gateway->getUserById($payload['id']));
                }
                
                break;

            default:
                if (is_numeric($request[0])) {
                    if ($method !== "GET") {
                        http_response_code(405);
                        echo json_encode(["error" => "Method not allowed"]);
                        return;
                    }

                    $id = (int)$request;

                    // TODO: implement getUserById in AuthGateway
                    $user = $this->gateway->getUserById($id);

                    if ($user) {
                        echo json_encode($user);
                    } else {
                        http_response_code(404);
                        echo json_encode(["error" => "User not found"]);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Not found"]);
                }
        }
    }
    private function getValidationErrors(array $data, bool $is_new) {
        $errors = [];

        if ($is_new) {
            if (empty($data['email']) ||
                empty($data['username']) ||
                empty($data['first_name']) ||
                empty($data['last_name']) ||
                empty($data['password'])) {

                $errors[] = "Data Missing";
            }

            if (strlen($data['password'])) {
                $errors[] = "password too short";
            }
        }
    }
}