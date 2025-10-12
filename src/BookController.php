<?php

<<<<<<< HEAD
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
        
        if (!$book) {
            http_response_code(404);
            echo json_encode(["message" => "Book not found"]);
            return;
        }

        require './vendor/autoload.php';
        $secretKey = $_ENV['SECRET_KEY'];
        $jwtManager = new JwtManager($secretKey);

        switch ($method) {
            case "GET":
                echo json_encode($book);
                break;
            case "PUT":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                
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
                    echo json_encode(["error" => "Invalid or expired token"]);
                    return;
                }

                $errors = $this->getValidationErrors($data, false);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                // verifying ownership
                if ($book['owner_id'] !== $payload['id']) {
                    http_response_code(401);
                    echo json_encode(["error" => "Unauthorized modification"]);
                    return;
                }

                $rows = $this->gateway->update($book, $data);

                echo json_encode([
                    "message" => "Book $id Updated",
                    "rows" => $rows
                ]);
                break;
            case "DELETE":
                // verifiying auth and decoding JWT payload
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
                    echo json_encode(["error" => "Invalid or expired token"]);
                    return;
                }

                // Checking ownership
                if ($book['owner_id'] !== $payload['id']) {
                    http_response_code(401);
                    echo json_encode(["error" => "Unauthorized modification"]);
                    return;
                }

                // deleting book and confirming
                $this->gateway->delete($id);

                echo json_encode(['message' => "book $id deleted :-("]);

                break;
        }
    }

    private function processCollectionRequest(string $method): void {
        require './vendor/autoload.php';
        $secretKey = $_ENV['SECRET_KEY'];
        $jwtManager = new JwtManager($secretKey);

        switch ($method){
            case "GET":
                echo json_encode($this->gateway->getAll());

                break;
            case "POST":
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

                $data = (array) json_decode(file_get_contents("php://input"), true);
                
                $data['owner_id'] = $payload['id'];

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
=======
/**
 * Book Controller
 * 
 * Handles CRUD operations for books with authentication and authorization.
 */
class BookController {
    private JwtManager $jwtManager;
    private AuthMiddleware $authMiddleware;

    public function __construct(private BookGateway $gateway) {
        $this->jwtManager = new JwtManager($_ENV['SECRET_KEY']);
        $this->authMiddleware = new AuthMiddleware($this->jwtManager);
    }

    /**
     * Routes book requests to appropriate handlers
     * 
     * @param string $method HTTP method
     * @param string|null $id Optional book ID
     */
    public function processRequest(string $method, ?string $id): void {
        try {
            if ($id) {
                $this->processResourceRequest($method, $id);
            } else {
                $this->processCollectionRequest($method);
            }
        } catch (Exception $e) {
            $this->sendError(500, "Internal server error", $e->getMessage());
        }
    }

    /**
     * Handles requests for a specific book resource
     * 
     * @param string $method HTTP method
     * @param string $id Book ID
     */
    private function processResourceRequest(string $method, string $id): void {
        if (!$this->isValidId($id)) {
            $this->sendError(400, "Invalid book ID");
            return;
        }

        $book = $this->gateway->get($id);
        
        if (!$book) {
            $this->sendError(404, "Book not found");
            return;
        }

        match($method) {
            'GET' => $this->handleGetBook($book),
            'PUT' => $this->handleUpdateBook($book),
            'DELETE' => $this->handleDeleteBook($book),
            default => $this->sendError(405, "Method not allowed", "Allowed: GET, PUT, DELETE")
        };
    }

    /**
     * Handles requests for the book collection
     * 
     * @param string $method HTTP method
     */
    private function processCollectionRequest(string $method): void {
        match($method) {
            'GET' => $this->handleGetAllBooks(),
            'POST' => $this->handleCreateBook(),
            default => $this->sendError(405, "Method not allowed", "Allowed: GET, POST")
        };
    }

    /**
     * Handles retrieving a single book
     * 
     * @param array $book Book data
     */
    private function handleGetBook(array $book): void {
        $this->sendJson($book);
    }

    /**
     * Handles retrieving all books
     */
    private function handleGetAllBooks(): void {
        $books = $this->gateway->getAll();
        $this->sendJson($books);
    }

    /**
     * Handles creating a new book
     */
    private function handleCreateBook(): void {
        $payload = $this->authMiddleware->authenticate();
        if (!$payload) return;

        $data = $this->getJsonInput();
        if (!$data) return;

        $data['owner_id'] = $payload['id'];

        // Validate input
        $errors = $this->validateBookData($data, true);
        if (!empty($errors)) {
            $this->sendError(400, "Validation failed", implode(", ", $errors));
            return;
        }

        try {
            $bookId = $this->gateway->create($data);
            $this->sendSuccess(201, [
                "message" => "Book created successfully",
                "book_id" => $bookId
            ]);
        } catch (Exception $e) {
            $this->sendError(500, "Failed to create book", $e->getMessage());
        }
    }

    /**
     * Handles updating an existing book
     * 
     * @param array $book Current book data
     */
    private function handleUpdateBook(array $book): void {
        $payload = $this->authMiddleware->authenticate();
        if (!$payload) return;

        if ($book['owner_id'] !== $payload['id']) {
            $this->sendError(403, "Unauthorized: You can only update your own books");
            return;
        }

        $data = $this->getJsonInput();
        if (!$data) return;

        // Validate input
        $errors = $this->validateBookData($data, false);
        if (!empty($errors)) {
            $this->sendError(400, "Validation failed", implode(", ", $errors));
            return;
        }

        try {
            $rowsUpdated = $this->gateway->update($book, $data);
            $this->sendSuccess(200, [
                "message" => "Book updated successfully",
                "rows_updated" => $rowsUpdated
            ]);
        } catch (Exception $e) {
            $this->sendError(500, "Failed to update book", $e->getMessage());
        }
    }

    /**
     * Handles deleting a book
     * 
     * @param array $book Book data
     */
    private function handleDeleteBook(array $book): void {
        $payload = $this->authMiddleware->authenticate();
        if (!$payload) return;

        if ($book['owner_id'] !== $payload['id']) {
            $this->sendError(403, "Unauthorized: You can only delete your own books");
            return;
        }

        try {
            $this->gateway->delete($book['id']);
            $this->sendSuccess(200, ["message" => "Book deleted successfully"]);
        } catch (Exception $e) {
            $this->sendError(500, "Failed to delete book", $e->getMessage());
        }
    }

    /**
     * Validates book data
     * 
     * @param array $data Book data to validate
     * @param bool $isNew Whether this is a new book (requires all fields)
     * @return array Array of validation errors (empty if valid)
     */
    private function validateBookData(array $data, bool $isNew): array {
        $errors = [];

        if ($isNew) {
            if (empty($data['title'])) {
                $errors[] = "Title is required";
            }
            
            if (empty($data['owner_id'])) {
                $errors[] = "Owner ID is required";
            } elseif (!filter_var($data['owner_id'], FILTER_VALIDATE_INT) || $data['owner_id'] <= 0) {
                $errors[] = "Owner ID must be a positive integer";
            }
        }

        if (isset($data['title']) && strlen($data['title']) > 255) {
            $errors[] = "Title must be 255 characters or less";
        }

        if (isset($data['isbn']) && !empty($data['isbn']) && !$this->isValidISBN($data['isbn'])) {
            $errors[] = "Invalid ISBN format";
        }

        return $errors;
    }

    /**
     * Validates ISBN format (basic validation)
     * 
     * @param string $isbn ISBN to validate
     * @return bool True if valid
     */
    private function isValidISBN(string $isbn): bool {
        // Remove hyphens and spaces
        $isbn = str_replace(['-', ' '], '', $isbn);
        
        // Check if it's ISBN-10 or ISBN-13
        return preg_match('/^(?:\d{9}X|\d{10}|\d{13})$/', $isbn);
    }

    /**
     * Validates ID format
     * 
     * @param string $id ID to validate
     * @return bool True if valid
     */
    private function isValidId(string $id): bool {
        return is_numeric($id) && $id > 0;
    }

    
    /**
     * Parses JSON input from request body
     * 
     * @return array|null Parsed data or null if invalid
     */
    private function getJsonInput(): ?array {
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendError(400, "Invalid JSON format", json_last_error_msg());
            return null;
        }
        
        return $input;
    }

    /**
     * Sends JSON response
     * 
     * @param mixed $data Data to encode
     * @param int $code HTTP status code
     */
    private function sendJson(mixed $data, int $code = 200): void {
        http_response_code($code);
        echo json_encode($data);
    }

    /**
     * Sends success response
     * 
     * @param int $code HTTP status code
     * @param array $data Response data
     */
    private function sendSuccess(int $code, array $data): void {
        $this->sendJson($data, $code);
    }

    /**
     * Sends error response
     * 
     * @param int $code HTTP status code
     * @param string $error Error message
     * @param string|null $details Optional error details
     */
    private function sendError(int $code, string $error, ?string $details = null): void {
        $response = ["error" => $error];
        if ($details !== null && ($_ENV['APP_ENV'] ?? 'production') === 'development') {
            $response["details"] = $details;
        }
        $this->sendJson($response, $code);
    }
}
>>>>>>> be33fc8 (fixing my git)
