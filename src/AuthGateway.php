<?php 
class AuthGateway {
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }
    
    public function createUser ($data): string {
        $sql = "INSERT INTO users (first_name, last_name, username, email, pwd)
                VALUES (:first_name, :last_name, :username, :email, :pwd)";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":first_name", $data['first_name'], PDO::PARAM_STR);
        $stmt->bindValue(":last_name", $data['last_name'], PDO::PARAM_STR);
        $stmt->bindValue(":username", $data['username'], PDO::PARAM_STR);
        $stmt->bindValue(":email", $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(":pwd", $data['password'], PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":email", $email, PDO::PARAM_INT);

        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    public function getUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :usern";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":usern", $username, PDO::PARAM_INT);

        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    public function verifyUser($data) {
        $user = $this->getUserByEmail($data['email']);

        $email = $user['email'] ?? '';
        $pwd = $user['pwd'] ?? '';

        if ($email === $data['email'] && password_verify($data['password'], $pwd)) {
            return ["id" => $user['id'],"email" => $user['email']];
        } else {
            return NULL;
        }
    }
    
    public function updateUser ($current, $new) {
        $sql = "UPDATE users
            SET first_name = :first_name,
                last_name = :last_name,
                username = :username,
                pwd = :pwd
            WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":first_name", $new['first_name'] ?? $current['first_name'], PDO::PARAM_STR);
        $stmt->bindValue(":last_name", $new['last_name'] ?? $current['last_name'], PDO::PARAM_STR);
        $stmt->bindValue(":username", $new['username'] ?? $current['username'], PDO::PARAM_STR);
        $stmt->bindValue(":pwd", $new['password'] ?? $current['password'], PDO::PARAM_STR);
        $stmt->bindValue(":id", $current['id'], PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
}