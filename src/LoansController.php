<?php 
class LoansController {
    public function __construct(private LoansGateway $gateway, private BookGateway $gatebook)
    {
        
    }

    public function processRequest(array $request, string $method) { 
        require "./src/Auth.php"; // Checks authentication
        $user_id = $payload['id'];
        $input = (array) json_decode(file_get_contents("php://input"), true);

        // /loans/request
        if ($method === 'POST' && isset($request[0]) && $request[0] === 'request') {
            $this->handleRequestLoan($input, $user_id);
        }

        // /loans/received
        else if ($method === 'GET' && isset($request[0]) && $request[0] === 'received') {
            $this->handleReceivedLoans($user_id);
        }

        // /loans/my-borrowed
        else if ($method === 'GET' && isset($request[0]) && $request[0] === 'my-borrowed') {
            $this->handleMyLoans($user_id);
        }

        // /loans/{id}/approve
        else if ($method === 'PUT' && isset($request[1]) && $request[1] === 'approve') {
            $loan_id = (int)$request[0];
            $this->handleApproveLoan($loan_id, $user_id);
        }

        // /loans/{id}/decline
        else if ($method === 'PUT' && isset($request[1]) && $request[1] === 'decline') {
            $loan_id = (int)$request[0];
            $this->handleDeclineLoan($loan_id, $user_id);
        }

        // /loans/{id}/complete
        else if ($method === 'PUT' && isset($endpointParts[1]) && $endpointParts[1] === 'complete') {
            $loan_id = (int)$endpointParts[0];
            $this->handleCompleteLoan($loan_id, $user_id);
        }

        else {
            http_response_code(404);
            echo json_encode(["error" => "Endpoint not found"]);
        }
    }

    private function handleRequestLoan($data, $user_id) {
        if (!isset($data["book_id"])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing book_id"]);
            return;
        }

        $owner_id = $this->gatebook->get($data['book_id'])['owner_id'];
        if ($owner_id === $user_id){
            http_response_code(400);
            echo json_encode(["error" => "Wtf ? you want to borrow your own book ???"]);
            return;
        }
        $id = $this->gateway->createLoanRequest(
            $data["book_id"],
            $user_id,
            $data["start_date"] ?? null,
            $data["due_date"] ?? null,
            $data["message"] ?? null
        );

        echo json_encode(["message" => "Loan request created", "loan_id" => $id]);
    }

    private function handleReceivedLoans($userId) {
        $loans = $this->gateway->getReceivedLoans($userId);
        echo json_encode($loans);
    }

    private function handleMyLoans($userId) {
        $loans = $this->gateway->getMyLoans($userId);
        echo json_encode($loans);
    }

    private function handleApproveLoan($loan_id, $userId) {
        $success = $this->gateway->approveLoan($loan_id, $userId);
        echo json_encode(["message" => $success ? "Loan approved" : "Action failed"]);
    }

    private function handleDeclineLoan($loan_id, $userId) {
        $success = $this->gateway->declineLoan($loan_id, $userId);
        echo json_encode(["message" => $success ? "Loan declined" : "Action failed"]);
    }

    private function handleCompleteLoan($loan_id, $userId) {
        $success = $this->gateway->completeLoan($loan_id, $userId);
        echo json_encode(["message" => $success ? "Loan marked as completed" : "Action failed"]);
    }
}