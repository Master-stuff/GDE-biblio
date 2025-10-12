<<<<<<< HEAD
<?php 
class BookGateway {
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAll(): array {
        $sql = "SELECT * FROM books";

        $stmt = $this->conn->query($sql);

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $data;
    }

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
=======
<?php

/**
 * Book Data Gateway
 * 
 * Handles all database operations related to books.
 * Uses prepared statements to prevent SQL injection.
 */
class BookGateway {
    private PDO $conn;

    public function __construct(Database $database) {
        $this->conn = $database->getConnection();
    }

    /**
     * Retrieves all books from the database
     * 
     * @return array Array of all books
     */
    public function getAll(): array {
        $sql = "SELECT 
                    b.*,
                    u.username AS owner_username,
                    u.first_name AS owner_first_name,
                    u.last_name AS owner_last_name
                FROM books b
                LEFT JOIN users u ON b.owner_id = u.id
                ORDER BY b.created_at DESC";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new book
     * 
     * @param array $data Book data
     * @return string The ID of the newly created book
     */
    public function create(array $data): string {
        $sql = "INSERT INTO books (
                    title, owner_id, language, isbn, 
                    genre, description, author, cover_image
                )
                VALUES (
                    :title, :owner_id, :language, :isbn,
                    :genre, :description, :author, :cover_image
                )";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->execute([
            ':title' => $data['title'],
            ':owner_id' => (int)$data['owner_id'],
            ':language' => $data['language'] ?? 'English',
            ':isbn' => $data['isbn'] ?? null,
            ':genre' => $data['genre'] ?? null,
            ':description' => $data['description'] ?? null,
            ':author' => $data['author'] ?? 'Unknown',
            ':cover_image' => $data['cover_image'] ?? null
        ]);
>>>>>>> be33fc8 (fixing my git)

        return $this->conn->lastInsertId();
    }

<<<<<<< HEAD
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
=======
    /**
     * Retrieves a single book by ID
     * 
     * @param string|int $id Book ID
     * @return array|false Book data or false if not found
     */
    public function get(string|int $id): array|false {
        $sql = "SELECT 
                    b.*,
                    u.username AS owner_username,
                    u.first_name AS owner_first_name,
                    u.last_name AS owner_last_name,
                    u.email AS owner_email
                FROM books b
                LEFT JOIN users u ON b.owner_id = u.id
                WHERE b.id = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates an existing book
     * 
     * @param array $current Current book data
     * @param array $new New book data (only provided fields will be updated)
     * @return int Number of rows affected
     */
    public function update(array $current, array $new): int {
        $sql = "UPDATE books
                SET title = :title,
                    author = :author,
                    language = :language,
                    isbn = :isbn,
                    genre = :genre,
                    description = :description,
                    cover_image = :cover_image
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        
        $stmt->execute([
            ':title' => $new['title'] ?? $current['title'],
            ':author' => $new['author'] ?? $current['author'],
            ':language' => $new['language'] ?? $current['language'],
            ':isbn' => $new['isbn'] ?? $current['isbn'],
            ':genre' => $new['genre'] ?? $current['genre'],
            ':description' => $new['description'] ?? $current['description'],
            ':cover_image' => $new['cover_image'] ?? $current['cover_image'],
            ':id' => $current['id']
        ]);
>>>>>>> be33fc8 (fixing my git)

        return $stmt->rowCount();
    }

<<<<<<< HEAD
    public function getByOwner($owner_id): array {
        $sql = "SELECT * FROM books where owner_id = :owner_id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":owner_id", (int) $owner_id, PDO::PARAM_INT);

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function delete($id): void {
        $sql = "DELETE FROM books WHERE id = :id;";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue("id", $id, PDO::PARAM_INT);

        $stmt->execute();
    }
}
=======
    /**
     * Retrieves all books owned by a specific user
     * 
     * @param int $ownerId Owner's user ID
     * @return array Array of books
     */
    public function getByOwner(int $ownerId): array {
        $sql = "SELECT * FROM books 
                WHERE owner_id = :owner_id
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':owner_id' => $ownerId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Deletes a book
     * 
     * @param string|int $id Book ID to delete
     * @return int Number of rows affected
     */
    public function delete(string|int $id): int {
        $this->conn->beginTransaction();
        
        try {
            // Delete related loans first (foreign key constraint)
            $sqlLoans = "DELETE FROM loans WHERE book_id = :id";
            $stmtLoans = $this->conn->prepare($sqlLoans);
            $stmtLoans->execute([':id' => (int)$id]);
            
            // Delete the book
            $sqlBook = "DELETE FROM books WHERE id = :id";
            $stmtBook = $this->conn->prepare($sqlBook);
            $stmtBook->execute([':id' => (int)$id]);
            
            $rowCount = $stmtBook->rowCount();
            
            $this->conn->commit();
            return $rowCount;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
>>>>>>> be33fc8 (fixing my git)
