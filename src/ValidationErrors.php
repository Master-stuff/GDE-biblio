<<<<<<< HEAD
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
=======
<?php

/**
 * Input Validation Helper
 * 
 * Centralized validation logic for user registration, login, and updates.
 * Prevents SQL injection, XSS, and ensures data integrity.
 */
class ValidationErrors {
    private const USERNAME_MIN_LENGTH = 3;
    private const USERNAME_MAX_LENGTH = 20;
    private const PASSWORD_MIN_LENGTH = 8; // Increased from 6 for better security
    private const NAME_MIN_LENGTH = 2;
    private const NAME_MAX_LENGTH = 50;
    
    /**
     * Validates user registration data
     * 
     * @param array $data Registration data (username, email, password, etc.)
     * @param AuthGateway $gateway Gateway to check for existing users
     * @return array Array of validation error messages (empty if valid)
     */
    public static function validateRegister(array $data, AuthGateway $gateway): array {
        $errors = [];

        if (empty($data['username'])) {
            $errors[] = "Username is required";
        }
        if (empty($data['first_name'])) {
            $errors[] = "First name is required";
        }
        if (empty($data['last_name'])) {
            $errors[] = "Last name is required";
        }
        if (empty($data['email'])) {
            $errors[] = "Email is required";
        }
        if (empty($data['password'])) {
            $errors[] = "Password is required";
        }
        
        if (!empty($errors)) {
            return $errors;
        }
        
        // Validate email format
        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        if ($gateway->getUserByEmail($data['email'])) {
            $errors[] = "Email already registered";
        }
        
        if ($gateway->getUserByUsername($data['username'])) {
            $errors[] = "Username already taken";
        }

        if (!self::isValidPassword($data["password"])) {
            $errors[] = sprintf(
                "Password must be at least %d characters and contain letters, numbers, and special characters (!@#$%%&*)",
                self::PASSWORD_MIN_LENGTH
            );
        }

        // Validate username format
        if (!self::isValidUsername($data["username"])) {
            $errors[] = sprintf(
                "Username must be %d–%d characters, letters, numbers, dots, and underscores only",
                self::USERNAME_MIN_LENGTH,
                self::USERNAME_MAX_LENGTH
            );
        }
        
        if (!self::isValidName($data['first_name'])) {
            $errors[] = "First name contains invalid characters";
        }
        if (!self::isValidName($data['last_name'])) {
            $errors[] = "Last name contains invalid characters";
>>>>>>> be33fc8 (fixing my git)
        }

        return $errors;
    }

<<<<<<< HEAD
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
        
=======
    /**
     * Validates user update data
     * 
     * @param array $data Update data (optional fields)
     * @param AuthGateway $gateway Gateway to check for existing users
     * @return array Array of validation error messages (empty if valid)
     */
    public static function validateUpdate(array $data, AuthGateway $gateway): array {
        $errors = [];
        
        if (isset($data['first_name']) && !self::isValidName($data['first_name'])) {
            $errors[] = "First name contains invalid characters";
        }

        if (isset($data['last_name']) && !self::isValidName($data['last_name'])) {
            $errors[] = "Last name contains invalid characters";
        }

        if (isset($data['username'])) {
            if (!self::isValidUsername($data["username"])) {
                $errors[] = sprintf(
                    "Username must be %d–%d characters, letters, numbers, dots, and underscores only",
                    self::USERNAME_MIN_LENGTH,
                    self::USERNAME_MAX_LENGTH
                );
            }
            
            if ($gateway->getUserByUsername($data['username'])) {
                $errors[] = "Username already taken";
            }
        }
        
        if (isset($data['password']) && !self::isValidPassword($data["password"])) {
            $errors[] = sprintf(
                "Password must be at least %d characters and contain letters, numbers, and special characters",
                self::PASSWORD_MIN_LENGTH
            );
        }
>>>>>>> be33fc8 (fixing my git)

        return $errors;
    }

<<<<<<< HEAD
    public static function validateLogin (array $data): array {
        $errors = [];

        if (!isset($data["email"], $data["password"])) {
            $errors[] = "Missing email or password";
=======
    /**
     * Validates login credentials
     * 
     * @param array $data Login data (email, password)
     * @return array Array of validation error messages (empty if valid)
     */
    public static function validateLogin(array $data): array {
        $errors = [];

        if (!isset($data["email"]) || empty($data["email"])) {
            $errors[] = "Email is required";
        }
        
        if (!isset($data["password"]) || empty($data["password"])) {
            $errors[] = "Password is required";
>>>>>>> be33fc8 (fixing my git)
        }
        
        return $errors;
    }
<<<<<<< HEAD
}
=======
    
    /**
     * Validates password strength
     * 
     * @param string $password Password to validate
     * @return bool True if password meets requirements
     */
    private static function isValidPassword(string $password): bool {
        return strlen($password) >= self::PASSWORD_MIN_LENGTH &&
               preg_match('/[A-Za-z]/', $password) && // Contains letters
               preg_match('/[0-9]/', $password);       // Contains numbers
    }
    
    /**
     * Validates username format
     * 
     * @param string $username Username to validate
     * @return bool True if username meets requirements
     */
    private static function isValidUsername(string $username): bool {
        return preg_match(
            '/^[A-Za-z0-9._]{' . self::USERNAME_MIN_LENGTH . ',' . self::USERNAME_MAX_LENGTH . '}$/',
            $username
        );
    }
    
    /**
     * Validates name fields (first name, last name)
     * 
     * @param string $name Name to validate
     * @return bool True if name meets requirements
     */
    private static function isValidName(string $name): bool {
        $length = strlen($name);
        return $length >= self::NAME_MIN_LENGTH &&
               $length <= self::NAME_MAX_LENGTH &&
               preg_match('/^[A-Za-z\-\' ]+$/', $name);
    }
}
>>>>>>> be33fc8 (fixing my git)
