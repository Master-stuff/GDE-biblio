<?php

class BookController {
    public function __construct(private BookGateway $gateway)
    {
        
    }

    public function processRequest(string $method, ?string $id): void {
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    private function processResourceRequest(string $method, string $id): void {
        $book = $this->gateway->get($id);
        
        if (! $book) {
            http_response_code(404);
            echo json_encode(["message" => "Book not found"]);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($book);
                break;
            case "PUT":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                
                $errors = $this->getValidationErrors($data, false);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $rows = $this->gateway->update($book, $data);

                echo json_encode([
                    "message" => "Book $id Updated",
                    "rows" => $rows
                ]);
                break;
            case "DELETE":
                break;
        }
    }

    private function processCollectionRequest(string $method): void {
        switch ($method){
            case "GET":
                echo json_encode($this->gateway->getAll());

                break;
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                
                $errors = $this->getValidationErrors($data);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $id = $this->gateway->create($data);
                http_response_code(201);
                echo json_encode([
                    "message" => "New Book Created",
                    "id" => $id
                ]);
                break;
            default:
                http_response_code("405");
                header("Allow: POST, GET");
        }
    }

    private function getValidationErrors (array $data, bool $is_new = true): array {
        $errors = [];

        if ($is_new) {
            if (empty($data['title'])) {
                $errors[] = "title is required !";
            }
    
            if (empty($data['owner_id'])) {
                $errors[] = "owner_id is required !";
            } else {
                if (filter_var($data['owner_id'], FILTER_VALIDATE_INT) === false) {
                    $errors[] = "owner_id must be an integer";
            }
            }
        } 

        return $errors;
    } 

    
}