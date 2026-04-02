<?php
namespace Models;

use PDO;
use PDOException;

class User extends Model
{
    private $id;
    private $name;
    private $email;
    private $password;
    private $avatar;


    public function getTableName(): string
    {
        return 'users';
    }

    private function fillFromArray(array $row): void
    {
        $this->id = isset($row['id']) ? (int) $row['id'] : null;
        $this->name = $row['name'] ?? null;
        $this->email = $row['email'] ?? null;
        $this->password = $row['password'] ?? null;
        $this->avatar = $row['avatar'] ?? null;
    }

    public function emailExists(string $email, ?int $excludeUserId = null): bool {
        try {
            if ($excludeUserId !== null) {
                $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->getTableName()} WHERE email = :email AND id != :id LIMIT 1");
                $stmt->execute([':email' => $email, ':id' => $excludeUserId]);
            } else {
                $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->getTableName()} WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);
            }
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            $this->logError('Database error in User::emailExists: ' . $e->getMessage());
            return false;
        }
    }

    public function getByEmail(string $email): ?self
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->getTableName()} WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            $obj = new self($this->pdo);
            $obj->fillFromArray($row);
            return $obj;
        } catch (PDOException $e) {
            $this->logError('Database error in User::getByEmail: ' . $e->getMessage());
            return null;
        }
    }

    public function getById($userId): ?self
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->getTableName()} WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            $obj = new self($this->pdo);
            $obj->fillFromArray($row);
            return $obj;
        } catch (PDOException $e) {
            $this->logError('Database error in User::getById: ' . $e->getMessage());
            return null;
        }
    }

    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO {$this->getTableName()} (name, email, password) VALUES (:name, :email, :password)");
            
            if (!$stmt) {
                $this->logError('User::create: Failed to prepare statement. Error: ' . implode(', ', $this->pdo->errorInfo()));
                return false;
            }

            $result = $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':password' => $data['password']
            ]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                $this->logError('User::create: execute() failed. Error: ' . implode(' | ', $errorInfo));
                return false;
            }
            
            // Проверяем, что запись действительно была вставлена
            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {
                return true;
            } else {
                // Если execute вернул true, но rowCount = 0, значит запись не была вставлена
                $errorInfo = $stmt->errorInfo();
                $this->logError('User::create: execute returned true but rowCount = 0. Email: ' . $data['email'] . ' | ErrorInfo: ' . implode(' | ', $errorInfo));
                return false;
            }
        } catch (PDOException $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            if ($errorCode == '23505' || strpos($errorMessage, 'duplicate key') !== false || strpos($errorMessage, 'unique constraint') !== false) {
                $this->logError('User::create: Duplicate email detected: ' . $data['email']);
                // Возвращаем специальный код для дубликата
                return ['success' => false, 'duplicate_email' => true];
            }
            
            $this->logError('Database error in User::create: ' . $errorMessage . ' | Code: ' . $errorCode);
            return false;
        } catch (\Exception $e) {
            $this->logError('General error in User::create: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    // Метод для обновления имени
    public function updateName($userId, $name) {
        try {
            $stmt = $this->pdo->prepare("UPDATE {$this->getTableName()} SET name = :name WHERE id = :id");
            return $stmt->execute([':name' => $name, ':id' => $userId]);
        } catch (PDOException $e) {
            $this->logError('Database error in User::updateName: ' . $e->getMessage());
            return false;
        }
    }

    // Метод для обновления email
    public function updateEmail($userId, $email) {
        try {
            $stmt = $this->pdo->prepare("UPDATE {$this->getTableName()} SET email = :email WHERE id = :id");
            return $stmt->execute([':email' => $email, ':id' => $userId]);
        } catch (PDOException $e) {
            $this->logError('Database error in User::updateEmail: ' . $e->getMessage());
            return false;
        }
    }

    // Метод для обновления пароля
    public function updatePassword($userId, $hashedPassword) {
        try {
            $stmt = $this->pdo->prepare("UPDATE {$this->getTableName()} SET password = :password WHERE id = :id");
            return $stmt->execute([':password' => $hashedPassword, ':id' => $userId]);
        } catch (PDOException $e) {
            $this->logError('Database error in User::updatePassword: ' . $e->getMessage());
            return false;
        }
    }

    // Метод для обновления аватара
    public function updateAvatar($userId, $avatarFileName) {
        try {
            $stmt = $this->pdo->prepare("UPDATE {$this->getTableName()} SET avatar = :avatar WHERE id = :id");
            return $stmt->execute([':avatar' => $avatarFileName, ':id' => $userId]);
        } catch (PDOException $e) {
            $this->logError('Database error in User::updateAvatar: ' . $e->getMessage());
            return false;
        }
    }

    public function verifyPassword($userId, $currentPassword): bool
    {
        try {
            $user = $this->getById($userId);
            return $user && password_verify($currentPassword, $user->getPassword());
        } catch (PDOException $e) {
            $this->logError('Database error in User::verifyPassword: ' . $e->getMessage());
            return false;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }
}
