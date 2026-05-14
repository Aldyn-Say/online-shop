<?php
// TODO (PSR-12 §3): добавить declare(strict_types=1) после <?php — строгая типизация обязательна
// PSR-12 §3: отсутствует пустая строка между <?php и namespace
namespace Models;

use PDO;
use PDOException;

class User extends Model
{
    // PSR-1 §4.3: у свойств отсутствуют типы — нужно объявить ?int, ?string
    private $id;
    private $name;
    private $email;
    private $password;
    private $avatar;


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

    // PSR-12 §4.4: открывающая { должна быть на следующей строке
    public function emailExists(string $email, ?int $excludeUserId = null): bool {
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

    // PSR-1 §4.3: параметр $userId без типа — нужно int
    public function getById($userId): ?self
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

    // PSR-1 §4.3: параметр $data без типа (array), нет return type; PSR-12 §4.4: { на той же строке
    public function create($data) {
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
            
            // Проверяем, что запись действительно была вставлена
            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {
                return true;
            } else {
                // Если execute вернул true, но rowCount = 0, значит запись не была вставлена
                $errorInfo = $stmt->errorInfo();
                self::logError('User::create: execute returned true but rowCount = 0. Email: ' . $data['email'] . ' | ErrorInfo: ' . implode(' | ', $errorInfo));
                return false;
            }
        } catch (PDOException $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            if ($errorCode == '23505' || strpos($errorMessage, 'duplicate key') !== false || strpos($errorMessage, 'unique constraint') !== false) {
                self::logError('User::create: Duplicate email detected: ' . $data['email']);
                // Возвращаем специальный код для дубликата
                return ['success' => false, 'duplicate_email' => true];
            }
            
            self::logError('Database error in User::create: ' . $errorMessage . ' | Code: ' . $errorCode);
            return false;
        } catch (\Exception $e) {
            self::logError('General error in User::create: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    // PSR-1 §4.3: параметры без типов (int, string), нет return type (bool); PSR-12 §4.4: { на той же строке
    public function updateName($userId, $name) {
        try {
            $stmt = self::getPDO()->prepare("UPDATE " . static::getTableName() . " SET name = :name WHERE id = :id");
            return $stmt->execute([':name' => $name, ':id' => $userId]);
        } catch (PDOException $e) {
            self::logError('Database error in User::updateName: ' . $e->getMessage());
            return false;
        }
    }

    // PSR-1 §4.3: параметры без типов (int, string), нет return type (bool); PSR-12 §4.4: { на той же строке
    public function updateEmail($userId, $email) {
        try {
            $stmt = self::getPDO()->prepare("UPDATE " . static::getTableName() . " SET email = :email WHERE id = :id");
            return $stmt->execute([':email' => $email, ':id' => $userId]);
        } catch (PDOException $e) {
            self::logError('Database error in User::updateEmail: ' . $e->getMessage());
            return false;
        }
    }

    // PSR-1 §4.3: параметры без типов (int, string), нет return type (bool); PSR-12 §4.4: { на той же строке
    public function updatePassword($userId, $hashedPassword) {
        try {
            $stmt = self::getPDO()->prepare("UPDATE " . static::getTableName() . " SET password = :password WHERE id = :id");
            return $stmt->execute([':password' => $hashedPassword, ':id' => $userId]);
        } catch (PDOException $e) {
            self::logError('Database error in User::updatePassword: ' . $e->getMessage());
            return false;
        }
    }

    // PSR-1 §4.3: параметры без типов (int, string), нет return type (bool); PSR-12 §4.4: { на той же строке
    public function updateAvatar($userId, $avatarFileName) {
        try {
            $stmt = self::getPDO()->prepare("UPDATE " . static::getTableName() . " SET avatar = :avatar WHERE id = :id");
            return $stmt->execute([':avatar' => $avatarFileName, ':id' => $userId]);
        } catch (PDOException $e) {
            self::logError('Database error in User::updateAvatar: ' . $e->getMessage());
            return false;
        }
    }

    // PSR-1 §4.3: параметры $userId и $currentPassword без типов (int, string)
    public function verifyPassword($userId, $currentPassword): bool
    {
        try {
            $user = $this->getById($userId);
            return $user && password_verify($currentPassword, $user->getPassword());
        } catch (PDOException $e) {
            self::logError('Database error in User::verifyPassword: ' . $e->getMessage());
            return false;
        }
    }

    // PSR-1 §4.3: геттерам не хватает return type (?int / ?string)
    // TODO (PSR-1 §4.3): добавить return type к каждому геттеру:
    //   getId(): ?int, getEmail(): ?string, getPassword(): ?string,
    //   getName(): ?string, getAvatar(): ?string
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
