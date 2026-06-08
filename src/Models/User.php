<?php

declare(strict_types=1);

namespace Models;

use PDO;
use PDOException;

class User extends Model
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $email = null;
    private ?string $password = null;
    private ?string $avatar = null;

    public static function getTableName(): string
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

    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        try {
            if ($excludeUserId !== null) {
                $stmt = self::getPDO()->prepare("SELECT 1 FROM " . static::getTableName() . " WHERE email = :email AND id != :id LIMIT 1");
                $stmt->execute([':email' => $email, ':id' => $excludeUserId]);
            } else {
                $stmt = self::getPDO()->prepare("SELECT 1 FROM " . static::getTableName() . " WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);
            }

            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            self::logError('Database error in User::emailExists: ' . $e->getMessage());
            return false;
        }
    }

    public function getByEmail(string $email): ?self
    {
        try {
            $stmt = self::getPDO()->prepare("SELECT * FROM " . static::getTableName() . " WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            $obj = new self();
            $obj->fillFromArray($row);
            return $obj;
        } catch (PDOException $e) {
            self::logError('Database error in User::getByEmail: ' . $e->getMessage());
            return null;
        }
    }

    public function getById(int $userId): ?self
    {
        try {
            $stmt = self::getPDO()->prepare("SELECT * FROM " . static::getTableName() . " WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            $obj = new self();
            $obj->fillFromArray($row);
            return $obj;
        } catch (PDOException $e) {
            self::logError('Database error in User::getById: ' . $e->getMessage());
            return null;
        }
    }

    public function create(array $data): bool|array
    {
        try {
            $stmt = self::getPDO()->prepare("INSERT INTO " . static::getTableName() . " (name, email, password) VALUES (:name, :email, :password)");

            if (!$stmt) {
                self::logError('User::create: Failed to prepare statement. Error: ' . implode(', ', self::getPDO()->errorInfo()));
                return false;
            }

            $result = $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':password' => $data['password']
            ]);

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                self::logError('User::create: execute() failed. Error: ' . implode(' | ', $errorInfo));
                return false;
            }

            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {
                return true;
            }

            $errorInfo = $stmt->errorInfo();
            self::logError('User::create: execute returned true but rowCount = 0. Email: ' . $data['email'] . ' | ErrorInfo: ' . implode(' | ', $errorInfo));
            return false;
        } catch (PDOException $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            if ($errorCode == '23505' || strpos($errorMessage, 'duplicate key') !== false || strpos($errorMessage, 'unique constraint') !== false) {
                self::logError('User::create: Duplicate email detected: ' . $data['email']);
                return ['success' => false, 'duplicate_email' => true];
            }

            self::logError('Database error in User::create: ' . $errorMessage . ' | Code: ' . $errorCode);
            return false;
        } catch (\Exception $e) {
            self::logError('General error in User::create: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    public function updateName(int $userId, string $name): bool
    {
        try {
            $stmt = self::getPDO()->prepare("UPDATE " . static::getTableName() . " SET name = :name WHERE id = :id");
            return $stmt->execute([':name' => $name, ':id' => $userId]);
        } catch (PDOException $e) {
            self::logError('Database error in User::updateName: ' . $e->getMessage());
            return false;
        }
    }

    public function updateEmail(int $userId, string $email): bool
    {
        try {
            $stmt = self::getPDO()->prepare("UPDATE " . static::getTableName() . " SET email = :email WHERE id = :id");
            return $stmt->execute([':email' => $email, ':id' => $userId]);
        } catch (PDOException $e) {
            self::logError('Database error in User::updateEmail: ' . $e->getMessage());
            return false;
        }
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        try {
            $stmt = self::getPDO()->prepare("UPDATE " . static::getTableName() . " SET password = :password WHERE id = :id");
            return $stmt->execute([':password' => $hashedPassword, ':id' => $userId]);
        } catch (PDOException $e) {
            self::logError('Database error in User::updatePassword: ' . $e->getMessage());
            return false;
        }
    }

    public function updateAvatar(int $userId, string $avatarFileName): bool
    {
        try {
            $stmt = self::getPDO()->prepare("UPDATE " . static::getTableName() . " SET avatar = :avatar WHERE id = :id");
            return $stmt->execute([':avatar' => $avatarFileName, ':id' => $userId]);
        } catch (PDOException $e) {
            self::logError('Database error in User::updateAvatar: ' . $e->getMessage());
            return false;
        }
    }

    public function verifyPassword(int $userId, string $currentPassword): bool
    {
        try {
            $user = $this->getById($userId);
            return $user && password_verify($currentPassword, $user->getPassword());
        } catch (PDOException $e) {
            self::logError('Database error in User::verifyPassword: ' . $e->getMessage());
            return false;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }
}
