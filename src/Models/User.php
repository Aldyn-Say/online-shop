<?php
require_once 'database.php';

class User {
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?? getDBConnection();
    }

    // Метод для проверки существования email
    public function emailExists($email, $excludeUserId = null) {
        try {
            if ($excludeUserId !== null) {
                $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
                $stmt->execute([':email' => $email, ':user_id' => $excludeUserId]);
            } else {
                $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);
            }
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Database error in User::emailExists: " . $e->getMessage());
            return false;
        }
    }

    // Метод для получения пользователя по email
    public function getByEmail($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in User::getByEmail: " . $e->getMessage());
            return null;
        }
    }

    // Метод для получения пользователя по ID
    public function getById($userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in User::getById: " . $e->getMessage());
            return null;
        }
    }

    // Метод для создания пользователя
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            return $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':password' => $data['password']
            ]);
        } catch (PDOException $e) {
            error_log("Database error in User::create: " . $e->getMessage());
            return false;
        }
    }

    // Метод для обновления имени
    public function updateName($userId, $name) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET name = :name WHERE id = :id");
            return $stmt->execute([':name' => $name, ':id' => $userId]);
        } catch (PDOException $e) {
            error_log("Database error in User::updateName: " . $e->getMessage());
            return false;
        }
    }

    // Метод для обновления email
    public function updateEmail($userId, $email) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET email = :email WHERE id = :id");
            return $stmt->execute([':email' => $email, ':id' => $userId]);
        } catch (PDOException $e) {
            error_log("Database error in User::updateEmail: " . $e->getMessage());
            return false;
        }
    }

    // Метод для обновления пароля
    public function updatePassword($userId, $hashedPassword) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            return $stmt->execute([':password' => $hashedPassword, ':id' => $userId]);
        } catch (PDOException $e) {
            error_log("Database error in User::updatePassword: " . $e->getMessage());
            return false;
        }
    }

    // Метод для обновления аватара
    public function updateAvatar($userId, $avatarFileName) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
            return $stmt->execute([':avatar' => $avatarFileName, ':id' => $userId]);
        } catch (PDOException $e) {
            error_log("Database error in User::updateAvatar: " . $e->getMessage());
            return false;
        }
    }

    // Метод для проверки пароля
    public function verifyPassword($userId, $currentPassword) {
        try {
            $user = $this->getById($userId);
            if ($user && password_verify($currentPassword, $user['password'])) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Database error in User::verifyPassword: " . $e->getMessage());
            return false;
        }
    }
}