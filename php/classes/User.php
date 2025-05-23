<?php
class User {
    private $id;
    private $username;
    private $email;
    private $userLevel;
    private $createdAt;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->userLevel = $data['user_level'] ?? 'user';
        $this->createdAt = $data['created_at'] ?? null;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getUserLevel() {
        return $this->userLevel;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    // Check if user has specific permission level
    public function hasPermission($level) {
        $levels = [
            'admin' => 3,
            'editor' => 2,
            'user' => 1
        ];

        $userLevelValue = $levels[$this->userLevel] ?? 0;
        $requiredLevelValue = $levels[$level] ?? 0;

        return $userLevelValue >= $requiredLevelValue;
    }

    // Convert user object to array
    public function toArray() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'user_level' => $this->userLevel,
            'created_at' => $this->createdAt
        ];
    }
}
