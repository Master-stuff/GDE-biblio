<?php 
class LoansGateway {
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function createLoanRequest($book_id, $borrower_id, $start_date, $due_date, $message) {
        $sql = "INSERT INTO loans (book_id, borrower_id, owner_id, status, start_date, due_date, message)
                VALUES (
                    :book_id, 
                    :borrower_id, 
                    (SELECT owner_id FROM books WHERE id = :book_id_ref),
                    'pending', 
                    :start_date, 
                    :due_date, 
                    :message
                )";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
        $stmt->bindParam(':book_id_ref', $book_id, PDO::PARAM_INT);
        $stmt->bindParam(':borrower_id', $borrower_id, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':due_date', $due_date);
        $stmt->bindParam(':message', $message);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }
    
    public function getReceivedLoans($owner_id) {
        $sql = "SELECT l.*, b.title AS book_title, u.first_name, u.last_name
                FROM loans l
                JOIN books b ON l.book_id = b.id
                JOIN users u ON l.borrower_id = u.id
                WHERE l.owner_id = :owner_id
                ORDER BY l.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':owner_id' => $owner_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMyLoans($borrower_id) {
        $sql = "SELECT l.*, b.title AS book_title, u.first_name AS owner_name
                FROM loans l
                JOIN books b ON l.book_id = b.id
                JOIN users u ON l.owner_id = u.id
                WHERE l.borrower_id = :borrower_id
                ORDER BY l.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':borrower_id' => $borrower_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approveLoan($loan_id, $owner_id) {
        $sql = "UPDATE loans
                SET status = 'approved', start_date = NOW()
                WHERE id = :loan_id AND owner_id = :owner_id AND status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':loan_id' => $loan_id, ':owner_id' => $owner_id]);
    }

    public function declineLoan($loan_id, $owner_id) {
        $sql = "UPDATE loans
                SET status = 'cancelled'
                WHERE id = :loan_id AND owner_id = :owner_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':loan_id' => $loan_id, ':owner_id' => $owner_id]);
    }

    public function completeLoan($loan_id, $owner_id) {
        $sql = "UPDATE loans
                SET status = 'done', return_date = NOW()
                WHERE id = :loan_id AND owner_id = :owner_id AND status = 'approved'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':loan_id' => $loan_id, ':owner_id' => $owner_id]);
    }
}