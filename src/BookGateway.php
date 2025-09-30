<?php 
class BookGateway {
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    // Lists All books //

    public function getAll(): array {
        $sql = "SELECT * FROM books";

        $stmt = $this->conn->query($sql);

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $data;
    }

    // Adds a book to books table //

    public function create(array $data): string {
        $sql = "INSERT INTO books (title, owner_id, language, isbn, genre, description, author, cover_image)
                VALUES (:title, :owner_id, :language, :isbn, :genre, :description, :author, :cover_image)";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":title", $data['title'], PDO::PARAM_STR);
        $stmt->bindValue(":owner_id", (int) $data['owner_id'], PDO::PARAM_INT);
        $stmt->bindValue(":language", $data['language'] ?? "French", PDO::PARAM_STR);
        $stmt->bindValue(":isbn", $data['isbn'] ?? NULL, PDO::PARAM_STR);
        $stmt->bindValue(":genre", $data['genre'] ?? NULL, PDO::PARAM_STR);
        $stmt->bindValue(":description", $data['description'] ?? NULL, PDO::PARAM_STR);
        $stmt->bindValue(":author", $data['author'], PDO::PARAM_STR);
        $stmt->bindValue(":cover_image", $data['cover_image'] ?? NULL, PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function get(string $id): array | false {
        $sql = "SELECT * FROM books WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    public function update(array $current, array $new): int {
        $sql = "UPDATE books
                SET title = :title, author = :author, language = :language, isbn = :isbn, genre = :genre, description = :description, cover_image = :cover_image
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue("title", $new['title'] ?? $current['title'] , PDO::PARAM_STR);
        $stmt->bindValue("author", $new['author'] ?? $current['author'], PDO::PARAM_STR);
        $stmt->bindValue("language", $new['language'] ?? $current['language'], PDO::PARAM_STR);
        $stmt->bindValue("isbn", $new['isbn'] ?? $current['isbn'], PDO::PARAM_STR);
        $stmt->bindValue("genre", $new['genre'] ?? $current['genre'], PDO::PARAM_STR);
        $stmt->bindValue("description", $new['description'] ?? $current['description'], PDO::PARAM_STR);
        $stmt->bindValue("cover_image", $new['cover_image'] ?? $current['cover_image'], PDO::PARAM_STR);
        $stmt->bindValue("id", $current['id'], PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
}