<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';

class UserManager {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Register a new user
    public function register($username, $password, $email, $userLevel = 'user') {
        try {
            // Check if username already exists
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username already exists'];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $this->conn->prepare(
                "INSERT INTO users (username, password, email, user_level, created_at) 
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$username, $hashedPassword, $email, $userLevel]);

            return ['success' => true, 'message' => 'User registered successfully'];
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    // Login user
    public function login($username, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $userData = $stmt->fetch();

            if ($userData && password_verify($password, $userData['password'])) {
                // Set session data
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['username'] = $userData['username'];
                $_SESSION['user_level'] = $userData['user_level'];
                $_SESSION['last_activity'] = time();

                return ['success' => true, 'user' => new User($userData)];
            }

            return ['success' => false, 'message' => 'Invalid credentials'];
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed'];
        }
    }

    // Logout user
    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }

    // Get user by ID
    public function getUserById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $userData = $stmt->fetch();

            return $userData ? new User($userData) : null;
        } catch (PDOException $e) {
            error_log("Error fetching user: " . $e->getMessage());
            return null;
        }
    }

    // Update user
    public function updateUser($id, $data) {
        try {
            $allowedFields = ['email', 'user_level'];
            $updates = [];
            $values = [];

            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updates[] = "$field = ?";
                    $values[] = $value;
                }
            }

            if (empty($updates)) {
                return ['success' => false, 'message' => 'No valid fields to update'];
            }

            $values[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);

            return ['success' => true, 'message' => 'User updated successfully'];
        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Update failed'];
        }
    }

    // Change password
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            $stmt = $this->conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            return ['success' => true, 'message' => 'Password changed successfully'];
        } catch (PDOException $e) {
            error_log("Password change error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Password change failed'];
        }
    }

    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Check session timeout
    public function checkSessionTimeout() {
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            $this->logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
}
