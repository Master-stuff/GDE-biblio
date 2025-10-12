# Book Lending API

A modern RESTful API for managing a book lending system where users can share books with each other. Built with PHP 8+ and MySQL featuring comprehensive security, extensive comments, and production-ready error handling.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [API Documentation](#api-documentation)
  - [Authentication](#authentication)
  - [Users](#users)
  - [Books](#books)
  - [Loans](#loans)
- [Error Handling](#error-handling)
- [Database Schema](#database-schema)
- [Security](#security)
- [Code Architecture](#code-architecture)

## Features

- **Modern PHP 8+**: Uses match expressions, constructor property promotion, and union types
- **JWT Authentication**: Secure token-based authentication with expiration validation
- **Comprehensive Security**: Password hashing, SQL injection prevention, input validation, CORS support
- **Book Management**: Full CRUD operations with ownership validation
- **Loan System**: Complete workflow for borrowing books with status transitions
- **Robust Error Handling**: Production/development modes with detailed logging
- **Well-Documented Code**: Extensive PHPDoc comments and inline explanations
- **Centralized Middleware**: Reusable authentication logic via AuthMiddleware
- **Transaction Support**: Database transactions for data integrity
- **Optimized Queries**: Efficient JOINs and indexed queries

## Requirements

- PHP 8.0 or higher (uses modern PHP features like match expressions)
- MySQL 5.7 or higher
- Composer
- Apache/Nginx web server
- PDO extension enabled
- OpenSSL extension (for JWT token generation)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd <project-directory>
```

2. Install dependencies:
```bash
composer install
```

3. Create a `.env` file in the root directory:
```env
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
SECRET_KEY=your_jwt_secret_key_minimum_32_characters
APP_ENV=development
```

**Important:** The `SECRET_KEY` must be at least 32 characters for security. Generate a strong random key:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

4. Import the database schema (see [Database Schema](#database-schema))

5. Configure your web server to point to the project directory

6. Ensure proper file permissions for the web server

## Configuration

The API uses environment variables for configuration. All required variables are validated on startup.

### Required Variables

- `DB_HOST`: Database host (default: localhost)
- `DB_NAME`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password
- `SECRET_KEY`: Secret key for JWT token generation (minimum 32 characters)

### Optional Variables

- `APP_ENV`: Environment mode (`development` or `production`, default: production)
  - **Development**: Shows detailed error messages and stack traces
  - **Production**: Shows generic error messages and logs details

## API Documentation

Base URL: `http://your-domain.com/api`

All responses are in JSON format with UTF-8 encoding.

### Authentication

All authenticated endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer <your-jwt-token>
```

Tokens expire after 24 hours (configurable in JwtManager).

#### Register User

**POST** `/users/register`

Request body:
```json
{
  "username": "johndoe",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "password": "SecurePass123!"
}
```

**Validation Rules:**
- `username`: 3-20 characters, letters/numbers/dots/underscores only
- `first_name`: 2-50 characters, letters/hyphens/apostrophes/spaces only
- `last_name`: 2-50 characters, letters/hyphens/apostrophes/spaces only
- `email`: Valid email format
- `password`: Minimum 8 characters, must contain letters and numbers

Response (201):
```json
{
  "message": "User registered successfully",
  "user_id": "1"
}
```

#### Login

**POST** `/users/login`

Request body:
```json
{
  "email": "john@example.com",
  "password": "SecurePass123!"
}
```

Response (200):
```json
{
  "message": "Login successful",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "email": "john@example.com",
    "username": "johndoe"
  }
}
```

### Users

#### Get Current User Profile

**GET** `/users/me`

Headers: `Authorization: Bearer <token>`

Response (200):
```json
{
  "id": 1,
  "username": "johndoe",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "created_at": "2025-01-10 10:30:00"
}
```

#### Get Current User's Books

**GET** `/users/me/books`

Headers: `Authorization: Bearer <token>`

Returns all books owned by the authenticated user.

#### Update Current User Profile

**PUT** `/users/me`

Headers: `Authorization: Bearer <token>`

Request body (all fields optional):
```json
{
  "first_name": "Jonathan",
  "last_name": "Doe",
  "username": "johndoe2",
  "password": "NewSecurePass123!"
}
```

Response (200):
```json
{
  "message": "Profile updated successfully",
  "rows_updated": 1
}
```

#### Get User by ID

**GET** `/users/{id}`

Public endpoint - no authentication required.

Response (200):
```json
{
  "id": 1,
  "username": "johndoe",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com"
}
```

#### Get User's Books by ID

**GET** `/users/{id}/books`

Public endpoint - returns all books owned by the specified user.

### Books

#### Get All Books

**GET** `/books`

Public endpoint - returns all books with owner information.

Response (200):
```json
[
  {
    "id": 1,
    "title": "The Great Gatsby",
    "author": "F. Scott Fitzgerald",
    "isbn": "978-0-7432-7356-5",
    "genre": "Fiction",
    "language": "English",
    "description": "A classic American novel",
    "cover_image": "https://example.com/cover.jpg",
    "owner_id": 1,
    "owner_username": "johndoe",
    "owner_first_name": "John",
    "owner_last_name": "Doe",
    "created_at": "2025-01-10 10:30:00"
  }
]
```

#### Get Book by ID

**GET** `/books/{id}`

Public endpoint - returns detailed book information with owner details.

Response (200):
```json
{
  "id": 1,
  "title": "The Great Gatsby",
  "author": "F. Scott Fitzgerald",
  "isbn": "978-0-7432-7356-5",
  "genre": "Fiction",
  "language": "English",
  "description": "A classic American novel",
  "cover_image": "https://example.com/cover.jpg",
  "owner_id": 1,
  "owner_username": "johndoe",
  "owner_first_name": "John",
  "owner_last_name": "Doe",
  "owner_email": "john@example.com",
  "created_at": "2025-01-10 10:30:00"
}
```

#### Create Book

**POST** `/books`

Headers: `Authorization: Bearer <token>`

Request body:
```json
{
  "title": "The Great Gatsby",
  "author": "F. Scott Fitzgerald",
  "isbn": "978-0-7432-7356-5",
  "genre": "Fiction",
  "language": "English",
  "description": "A classic American novel",
  "cover_image": "https://example.com/cover.jpg"
}
```

**Validation Rules:**
- `title`: Required, maximum 255 characters
- `author`: Optional, defaults to "Unknown"
- `isbn`: Optional, must be valid ISBN-10 or ISBN-13 format
- `language`: Optional, defaults to "English"
- Other fields are optional

Response (201):
```json
{
  "message": "Book created successfully",
  "book_id": "1"
}
```

#### Update Book

**PUT** `/books/{id}`

Headers: `Authorization: Bearer <token>`

**Authorization:** Only the book owner can update the book.

Request body (all fields optional):
```json
{
  "title": "Updated Title",
  "description": "Updated description",
  "genre": "Classic Fiction"
}
```

Response (200):
```json
{
  "message": "Book updated successfully",
  "rows_updated": 1
}
```

#### Delete Book

**DELETE** `/books/{id}`

Headers: `Authorization: Bearer <token>`

**Authorization:** Only the book owner can delete the book.

**Note:** Deleting a book will also delete all associated loan records (cascade delete).

Response (200):
```json
{
  "message": "Book deleted successfully"
}
```

### Loans

The loans system provides a complete workflow for borrowing books with comprehensive validation, authorization, and status management.

#### Request a Loan

**POST** `/loans/request`

Headers: `Authorization: Bearer <token>`

Request body:
```json
{
  "book_id": 1,
  "start_date": "2025-01-15",
  "due_date": "2025-02-15",
  "message": "I would love to read this book!"
}
```

Response (201):
```json
{
  "message": "Loan request created successfully",
  "loan_id": "1"
}
```

**Validation Rules:**
- `book_id`: **Required**, must be a positive integer
- Book must exist in the database
- Cannot borrow your own book
- `start_date`: Optional, must be in `YYYY-MM-DD` format
- `due_date`: Optional, must be in `YYYY-MM-DD` format
- `due_date` must be after `start_date` if both are provided
- `message`: Optional, maximum 500 characters

**Error Responses:**
- `400`: Missing book_id, invalid format, or validation failure
- `404`: Book not found
- `500`: Database error

#### Get Received Loan Requests

**GET** `/loans/received`

Headers: `Authorization: Bearer <token>`

Returns all loan requests for books you own, sorted by status priority (pending first) and creation date.

Response (200):
```json
[
  {
    "id": 1,
    "book_id": 1,
    "book_title": "The Great Gatsby",
    "book_author": "F. Scott Fitzgerald",
    "book_cover": "https://example.com/cover.jpg",
    "borrower_id": 2,
    "borrower_first_name": "Jane",
    "borrower_last_name": "Smith",
    "borrower_email": "jane@example.com",
    "borrower_username": "janesmith",
    "owner_id": 1,
    "status": "pending",
    "start_date": "2025-01-15",
    "due_date": "2025-02-15",
    "return_date": null,
    "message": "I would love to read this book!",
    "created_at": "2025-01-10 10:30:00"
  }
]
```

**Status Priority Order:**
1. `pending` - Awaiting your approval
2. `approved` - Currently borrowed
3. `done` - Completed/returned
4. `cancelled` - Declined requests

#### Get My Borrowed Books

**GET** `/loans/my-borrowed`

Headers: `Authorization: Bearer <token>`

Returns all loans you've requested, sorted by status priority (approved first) and creation date.

Response (200):
```json
[
  {
    "id": 1,
    "book_id": 1,
    "book_title": "The Great Gatsby",
    "book_author": "F. Scott Fitzgerald",
    "book_cover": "https://example.com/cover.jpg",
    "book_isbn": "978-0-7432-7356-5",
    "borrower_id": 2,
    "owner_id": 1,
    "owner_first_name": "John",
    "owner_last_name": "Doe",
    "owner_email": "john@example.com",
    "owner_username": "johndoe",
    "status": "approved",
    "start_date": "2025-01-15",
    "due_date": "2025-02-15",
    "return_date": null,
    "message": "I would love to read this book!",
    "created_at": "2025-01-10 10:30:00"
  }
]
```

#### Approve Loan Request

**PUT** `/loans/{id}/approve`

Headers: `Authorization: Bearer <token>`

Approves a pending loan request for your book. Automatically sets the start date to the current timestamp.

Response (200):
```json
{
  "message": "Loan approved successfully"
}
```

**Validation:**
- Loan ID must be a positive integer
- Loan must exist
- You must be the book owner (authorization check)
- Loan status must be `pending` (cannot approve already approved/cancelled/completed loans)

**Error Responses:**
- `400`: Invalid loan ID or cannot approve loan with current status
- `403`: Unauthorized - not the book owner
- `404`: Loan not found
- `500`: Database error

#### Decline Loan Request

**PUT** `/loans/{id}/decline`

Headers: `Authorization: Bearer <token>`

Declines a pending loan request for your book. Changes status to `cancelled`.

Response (200):
```json
{
  "message": "Loan declined successfully"
}
```

**Validation:**
- Loan ID must be a positive integer
- Loan must exist
- You must be the book owner (authorization check)
- Loan status must be `pending`

**Error Responses:**
- `400`: Invalid loan ID or cannot decline loan with current status
- `403`: Unauthorized - not the book owner
- `404`: Loan not found
- `500`: Database error

#### Complete Loan

**PUT** `/loans/{id}/complete`

Headers: `Authorization: Bearer <token>`

Marks an approved loan as completed (book returned). Automatically sets the return date to the current timestamp.

Response (200):
```json
{
  "message": "Loan completed successfully"
}
```

**Validation:**
- Loan ID must be a positive integer
- Loan must exist
- You must be the book owner (authorization check)
- Loan status must be `approved` (cannot complete pending/cancelled/already completed loans)

**Error Responses:**
- `400`: Invalid loan ID or cannot complete loan with current status
- `403`: Unauthorized - not the book owner
- `404`: Loan not found
- `500`: Database error

## Error Handling

The API implements comprehensive error handling with environment-aware responses and consistent formatting.

### Environment Modes

**Development Mode** (`APP_ENV=development`):
- Shows detailed error messages
- Includes stack traces
- Displays file paths and line numbers
- Helpful for debugging

**Production Mode** (`APP_ENV=production`):
- Shows generic error messages
- Hides sensitive information
- Logs detailed errors to server logs
- Secure for public deployment

### Common Status Codes

- `200 OK`: Request successful
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request data, validation failure, or malformed JSON
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: Insufficient permissions (e.g., trying to modify someone else's resource)
- `404 Not Found`: Resource not found (book, loan, user)
- `405 Method Not Allowed`: HTTP method not supported for this endpoint
- `422 Unprocessable Entity`: Validation errors (legacy support)
- `500 Internal Server Error`: Database error or unexpected server error

### Error Response Format

Standard error response:
```json
{
  "error": "Error message describing what went wrong"
}
```

Error with additional details (development mode only):
```json
{
  "error": "Failed to create loan request",
  "details": "Database connection timeout"
}
```

### Validation Error Format

```json
{
  "error": "Validation failed",
  "details": "Username must be 3-20 characters, Email already registered"
}
```

### JSON Parsing Errors

If the request body contains invalid JSON:
```json
{
  "error": "Invalid JSON format",
  "details": "Syntax error"
}
```

### Authentication Errors

**Missing Token:**
```json
{
  "error": "Missing or invalid authorization header"
}
```

**Invalid/Expired Token:**
```json
{
  "error": "Invalid or expired token"
}
```

### Authorization Errors

**Insufficient Permissions:**
```json
{
  "error": "Unauthorized: You can only update your own books"
}
```

```json
{
  "error": "Unauthorized: You can only modify loans for your own books"
}
```

### Loan-Specific Errors

**Invalid Status Transitions:**
```json
{
  "error": "Cannot approve loan with status: approved"
}
```

**Self-Borrowing Prevention:**
```json
{
  "error": "Cannot borrow your own book"
}
```

**Date Validation:**
```json
{
  "error": "Due date must be after start date"
}
```

## Database Schema

### Users Table
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(20) UNIQUE NOT NULL,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  pwd VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Books Table
```sql
CREATE TABLE books (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) DEFAULT 'Unknown',
  isbn VARCHAR(20),
  genre VARCHAR(50),
  language VARCHAR(50) DEFAULT 'English',
  description TEXT,
  cover_image VARCHAR(500),
  owner_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_owner (owner_id),
  INDEX idx_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Loans Table
```sql
CREATE TABLE loans (
  id INT PRIMARY KEY AUTO_INCREMENT,
  book_id INT NOT NULL,
  borrower_id INT NOT NULL,
  owner_id INT NOT NULL,
  status ENUM('pending', 'approved', 'cancelled', 'done') DEFAULT 'pending',
  start_date DATE,
  due_date DATE,
  return_date DATE,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  FOREIGN KEY (borrower_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_loans_owner (owner_id, status),
  INDEX idx_loans_borrower (borrower_id, status),
  INDEX idx_loans_book (book_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Loan Status Flow:**
1. `pending` → Initial state when loan is requested
2. `approved` → Owner approves the loan (start_date is automatically set)
3. `done` → Book is returned (return_date is automatically set)
4. `cancelled` → Owner declines the loan request

**Performance Indexes:**
- Composite indexes on frequently queried columns
- Optimized for sorting by status and date
- Efficient lookups for owner and borrower queries

## Security

### Authentication & Authorization

**JWT Tokens:**
- Tokens expire after 24 hours (configurable)
- Include user ID, email, and username in payload
- Validated on every protected endpoint
- Signature verification using HMAC-SHA256
- Minimum 32-character secret key required

**Authorization Checks:**
- Users can only modify their own resources
- Book owners control loan approvals/declines/completions
- Borrowers cannot approve their own loan requests
- Users cannot borrow their own books
- Centralized via AuthMiddleware for consistency

### Password Security

**Requirements:**
- Minimum 8 characters (increased from 6)
- Must contain letters and numbers
- Can include special characters
- Stored using bcrypt hashing (PASSWORD_BCRYPT)
- Never stored or transmitted in plain text

**Hashing:**
- Uses PHP's `password_hash()` with bcrypt
- Automatic salt generation
- Resistant to rainbow table attacks
- Verification via `password_verify()`

### Input Validation

**Username:**
- 3-20 characters
- Letters, numbers, dots, and underscores only
- Prevents XSS and injection attacks

**Email:**
- Validated using PHP's `FILTER_VALIDATE_EMAIL`
- Prevents malformed email addresses

**Names:**
- 2-50 characters
- Letters, hyphens, apostrophes, and spaces only
- Prevents special character injection

**Dates:**
- Strict format validation (YYYY-MM-DD)
- Prevents invalid date submissions
- Logical validation (due date after start date)

### SQL Injection Prevention

**Prepared Statements:**
- All database queries use PDO prepared statements
- Parameters bound with proper types
- No string concatenation in SQL queries
- Automatic escaping of user input

**Example:**
```php
$stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
```

### CORS Configuration

**Headers:**
- `Access-Control-Allow-Origin: *` (configure for production)
- `Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS`
- `Access-Control-Allow-Headers: Content-Type, Authorization`
- Preflight request handling (OPTIONS method)

**Production Recommendation:**
Replace `*` with specific trusted domains:
```php
header("Access-Control-Allow-Origin: https://yourdomain.com");
```

### Error Handling Security

**Production Mode:**
- Generic error messages to users
- Detailed errors logged server-side
- No stack traces or file paths exposed
- Prevents information leakage

**Development Mode:**
- Detailed error messages for debugging
- Stack traces included
- File paths and line numbers shown
- Should never be used in production

### Best Practices

1. **Always use HTTPS in production** - Encrypts all data in transit
2. **Keep SECRET_KEY secure** - Never commit to version control
3. **Use strong database passwords** - Minimum 16 characters, mixed case, numbers, symbols
4. **Regularly update dependencies** - Run `composer update` periodically
5. **Implement rate limiting** - Prevent brute force attacks (use nginx/Apache modules)
6. **Enable CORS only for trusted domains** - Don't use `*` in production
7. **Monitor logs** - Set up log monitoring for security events
8. **Use environment variables** - Never hardcode credentials
9. **Validate all inputs** - Never trust user input
10. **Keep PHP updated** - Use latest stable PHP version for security patches

## Code Architecture

### Project Structure
```
.
├── src/
│   ├── AuthMiddleware.php    # Centralized authentication (NEW)
│   ├── Auth.php              # Legacy auth helper (deprecated)
│   ├── AuthController.php    # User authentication endpoints (refactored)
│   ├── AuthGateway.php       # User database operations (refactored)
│   ├── BookController.php    # Book endpoints (refactored)
│   ├── BookGateway.php       # Book database operations (refactored)
│   ├── LoansController.php   # Loan endpoints (refactored)
│   ├── LoansGateway.php      # Loan database operations (refactored)
│   ├── Database.php          # Database connection (enhanced)
│   ├── ErrorHandler.php      # Global error handling (enhanced)
│   ├── JwtManager.php        # JWT token management (enhanced)
│   └── ValidationErrors.php  # Input validation (enhanced)
├── bootstrap.php             # Environment setup (enhanced)
├── index.php                 # Main entry point & routing (refactored)
├── composer.json             # Dependencies
├── .env                      # Environment variables (not in repo)
└── README.md                 # This file
```

### Design Patterns

**Controller Pattern:**
- Controllers handle HTTP requests and responses
- Validation logic extracted into private methods
- Consistent error handling across all endpoints
- Match expressions for clean routing (PHP 8)
- Dependency injection via constructor

**Gateway Pattern:**
- Gateways handle all database operations
- Separation of concerns between business logic and data access
- Reusable query methods
- Transaction support for data integrity
- Optimized queries with JOINs

**Middleware Pattern:**
- AuthMiddleware centralizes authentication logic
- Eliminates code duplication
- Consistent token validation
- Reusable across all controllers

**Dependency Injection:**
- Controllers receive dependencies via constructor
- Easier testing and maintenance
- Loose coupling between components

### Key Improvements

**Security Enhancements:**
- JWT token expiration validation
- Stronger password requirements (8+ characters)
- SQL injection prevention with prepared statements
- CORS headers for cross-origin requests
- Environment variable validation on startup
- Production/development error modes
- Centralized authentication via AuthMiddleware

**Code Quality:**
- Extensive PHPDoc comments on every method
- Inline comments explaining complex logic
- Consistent naming conventions
- Type hints for all parameters and return values
- Modern PHP 8 features (match, union types, constructor promotion)

**Efficiency:**
- Eliminated code duplication across controllers
- Optimized database queries with JOINs
- Transaction support for data integrity
- Reusable helper methods
- Efficient status validation

**Maintainability:**
- Clear separation of concerns
- Consistent error handling patterns
- Centralized validation logic
- Well-documented codebase
- Easy to extend and modify

### Loans System Architecture

The refactored loans system features:

**Constants for Magic Strings:**
```php
private const STATUS_PENDING = 'pending';
private const STATUS_APPROVED = 'approved';
private const STATUS_CANCELLED = 'cancelled';
private const STATUS_DONE = 'done';
private const MAX_MESSAGE_LENGTH = 500;
private const DATE_FORMAT = 'Y-m-d';
```

**Unified Routing with Match Expressions:**
```php
match(true) {
    $method === 'POST' && $endpoint === 'request'
        => $this->handleRequestLoan(),
    $method === 'PUT' && $action === 'approve'
        => $this->handleLoanStatusChange($endpoint, 'approve'),
    // ...
}
```

**Consolidated Validation:**
- `validateLoanRequest()` - Comprehensive input validation
- `validateStatusTransition()` - Status change validation
- `canModifyLoan()` - Authorization checks
- Consistent error messages
- Early return pattern for cleaner code

**DRY Principles:**
- Single `handleLoanStatusChange()` method for approve/decline/complete
- Shared validation logic across endpoints
- Unified response handling methods
- Generic `updateLoanStatus()` in gateway

**Enhanced Gateway:**
```php
private function updateLoanStatus(
    int $loan_id,
    int $owner_id,
    string $new_status,
    array $additional_fields = [],
    ?string $required_current_status = null
): bool
```
- Flexible status update method
- Automatic timestamp handling
- Authorization enforcement
- Status transition validation

## Development

### Running Locally

1. Start your local MySQL server
2. Configure `.env` with local database credentials
3. Set `APP_ENV=development` for detailed error messages
4. Start PHP built-in server:
```bash
php -S localhost:8000
```
5. Access API at `http://localhost:8000/api`

### Testing

Test endpoints using tools like:
- **Postman** - GUI-based API testing
- **cURL** - Command-line HTTP client
- **HTTPie** - User-friendly command-line HTTP client
- **Insomnia** - REST API client

Example cURL requests:

**Register:**
```bash
curl -X POST http://localhost:8000/api/users/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "first_name": "Test",
    "last_name": "User",
    "email": "test@example.com",
    "password": "TestPass123"
  }'
```

**Login:**
```bash
curl -X POST http://localhost:8000/api/users/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "TestPass123"
  }'
```

**Create Loan Request:**
```bash
curl -X POST http://localhost:8000/api/loans/request \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "book_id": 1,
    "message": "Can I borrow this?"
  }'
```

### Common Issues

**"Invalid JSON format" error:**
- Ensure `Content-Type: application/json` header is set
- Validate JSON syntax before sending (use a JSON validator)
- Check for trailing commas or missing quotes

**"Unauthorized" error:**
- Check that JWT token is valid and not expired (24-hour expiration)
- Ensure Authorization header format: `Bearer <token>` (note the space)
- Verify token was obtained from a successful login

**"Missing required environment variable" error:**
- Check that `.env` file exists in the correct location
- Verify all required variables are set (DB_HOST, DB_NAME, DB_USER, DB_PASS, SECRET_KEY)
- Ensure SECRET_KEY is at least 32 characters

**Database connection errors:**
- Verify `.env` credentials are correct
- Ensure MySQL server is running (`sudo service mysql status`)
- Check database exists: `mysql -u root -p -e "SHOW DATABASES;"`
- Verify user has proper permissions: `GRANT ALL ON database.* TO 'user'@'localhost';`

**CORS errors in browser:**
- Check that CORS headers are properly set in `index.php`
- For production, configure specific allowed origins
- Ensure preflight OPTIONS requests are handled

**"Secret key must be at least 32 characters" error:**
- Generate a new strong secret key: `php -r "echo bin2hex(random_bytes(32));"`
- Update SECRET_KEY in `.env` file

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Follow existing code style and conventions
4. Add PHPDoc comments to all new methods
5. Test your changes thoroughly
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

## License

[Your License Here]

## Support

For issues and questions:
- Open an issue on the repository
- Contact: [your-email@example.com]
- Documentation: This README

## Changelog

### Version 2.0.0 (Current)

**Major Refactoring:**
- Complete codebase refactoring with modern PHP 8 features
- Extensive PHPDoc comments and inline documentation
- Centralized authentication via AuthMiddleware
- Enhanced security with token expiration and stronger validation
- Improved error handling with production/development modes
- Optimized database queries with JOINs and indexes
- Transaction support for data integrity
- Eliminated code duplication across all controllers

**New Features:**
- JWT token expiration validation
- Environment variable validation on startup
- CORS support for cross-origin requests
- Comprehensive input validation
- Status transition validation for loans
- Automatic timestamp handling for loans

**Breaking Changes:**
- Password minimum length increased from 6 to 8 characters
- API responses now include more detailed information
- Error response format standardized across all endpoints

### Version 1.0.0

- Initial release
- Basic CRUD operations for books
- User authentication with JWT
- Loan request system
