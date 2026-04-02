<?php

namespace Models;

use PDOException;

class ApplicationLog extends Model
{
    protected function getTableName(): string
    {
        return 'application_logs';
    }

    public function write(string $level, string $message): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO application_logs (level, message, created_at) VALUES (:level, :message, CURRENT_TIMESTAMP)'
            );

            return $stmt->execute([
                ':level' => $level,
                ':message' => $message,
            ]);
        } catch (PDOException) {
            return false;
        }
    }
}
