<?php 

class ValidationErrors {
    public static function validateRegister (array $data, AuthGateway $gateway): array {
        $errors = [];

        if (empty($data['username']) ||
            empty($data['first_name']) ||
            empty($data['last_name']) ||
            empty($data['email']) ||
            empty($data['password'])) {
                
                $errors[] = "Data missing";
                $errors[] = print_r($data);
                return $errors;
            }
        
        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
            return $errors;
        }

        if ($gateway->getUserByEmail($data['email']) ?? false) {
            $errors[] = "Email already registered";
        }

        if ($gateway->getUserByUsername($data['username']) ?? false) {
            $errors[] = "Username already registered";
        }

        if (!preg_match('/^[A-Za-z0-9!@#$%&*]{6,}$/', $data["password"])) {
            $errors[] = "Password can only contain letters, numbers, and !@#$%&* (min 6 chars)";
        }

        if (!preg_match('/^[A-Za-z0-9._]{3,20}$/', $data["username"]) ) {
            $errors[] = "Username must be 3–20 characters, letters/numbers/._ only";
        }

        return $errors;
    }

    public static function validateUpdate (array $data, AuthGateway $gateway): array {
        $errors = [];
        
        if (!empty($data['first_name'])) {
            if (!preg_match('/^[A-Za-z\-\' ]{2,20}$/', $data['first_name'])) {
                $errors[] = "First name must be 2–20 characters, letters, hyphens, apostrophes, and spaces only";
            }
        }

        if (!empty($data['last_name'])) {
            if (!preg_match('/^[A-Za-z\-\' ]{2,20}$/', $data['last_name'])) {
                $errors[] = "Last name must be 2–20 characters, letters, hyphens, apostrophes, and spaces only";
            }
        }

        if (!empty($data['username'])){
            if ($gateway->getUserByUsername($data['username']) ?? false) {
                $errors[] = "Username already registered";
            }

            if (!preg_match('/^[A-Za-z0-9._]{3,20}$/', $data["username"]) ) {
                $errors[] = "Username must be 3–20 characters, letters/numbers/._ only";
            }
        }
        
        if (!empty($data['password'])){
            if (!preg_match('/^[A-Za-z0-9!@#$%&*]{6,}$/', $data["password"])) {
                $errors[] = "Password can only contain letters, numbers, and !@#$%&* (min 6 chars)";
            }
        }
        

        return $errors;
    }

    public static function validateLogin (array $data): array {
        $errors = [];

        if (!isset($data["email"], $data["password"])) {
            $errors[] = "Missing email or password";
        }
        
        return $errors;
    }
}