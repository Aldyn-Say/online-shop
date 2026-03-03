<?php
namespace Models;

//use Exception;
use PDO;
//use PDOException;

class User extends Model {

    public function emailExists(string $email, ?int $excludeUserId = null): bool {
        try {
            if ($excludeUserId !== null) {
                $stmt = $this->pdo->prepare("SELECT 1 FROM users WHERE email = :email AND id != :id LIMIT 1");
                $stmt->execute([':email' => $email, ':id' => $excludeUserId]);
            } else {
                $stmt = $this->pdo->prepare("SELECT 1 FROM users WHERE email = :email LIMIT 1");
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
            // Проверяем подключение к БД
            if (!$this->pdo) {
                error_log("User::create: PDO connection is null");
                return false;
            }

            $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            
            if (!$stmt) {
                error_log("User::create: Failed to prepare statement. Error: " . implode(', ', $this->pdo->errorInfo()));
                return false;
            }

            $result = $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':password' => $data['password']
            ]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("User::create: execute() failed. Error: " . implode(' | ', $errorInfo));
                return false;
            }
            
            // Проверяем, что запись действительно была вставлена
            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {
                return true;
            } else {
                // Если execute вернул true, но rowCount = 0, значит запись не была вставлена
                // (например, из-за ограничения UNIQUE на email)
                $errorInfo = $stmt->errorInfo();
                error_log("User::create: execute returned true but rowCount = 0. Email: " . $data['email'] . " | ErrorInfo: " . implode(' | ', $errorInfo));
                return false;
            }
        } catch (PDOException $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            // Проверяем, не является ли это ошибкой дубликата (PostgreSQL код 23505)
            if ($errorCode == '23505' || strpos($errorMessage, 'duplicate key') !== false || strpos($errorMessage, 'unique constraint') !== false) {
                error_log("User::create: Duplicate email detected: " . $data['email']);
                // Возвращаем специальный код для дубликата
                return ['success' => false, 'duplicate_email' => true];
            }
            
            error_log("Database error in User::create: " . $errorMessage . " | Code: " . $errorCode);
            return false;
        } catch (Exception $e) {
            error_log("General error in User::create: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
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