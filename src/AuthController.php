<?php 
class AuthController {
    public function __construct(private AuthGateway $gateway)
    {
        
    }

    public function processRequest(string $method, ?string $uri_2) {
        if ($method === "POST") {
            switch ($uri_2) {
                case "register":
                    $data = (array) json_decode(file_get_contents("php://input"), true);
                    if (empty($data['email']) ||
                        empty($data['username']) ||
                        empty($data['first_name']) ||
                        empty($data['last_name']) ||
                        empty($data['password'])) {
                            
                    } else {

                    }
                    break;
                case "login":
                    $data = (array) json_decode(file_get_contents("php://input"), true);
                    break;
                default:
                    http_response_code(404);
                    return;
            }
        } else {
            http_response_code("405");
            header("Allow: POST");
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