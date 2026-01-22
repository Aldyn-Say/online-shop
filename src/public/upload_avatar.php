<?php
session_start();
require_once 'User.php';

// Проверка авторизации
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

$userId = $_SESSION['user_id'];
$user = new User();
$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];

    // Проверка на ошибки загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors['avatar'] = 'Ошибка при загрузке файла';
    } else {
        // Проверка типа файла
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $errors['avatar'] = 'Разрешены только изображения (JPEG, PNG, GIF, WebP)';
        } else {
            // Проверка размера файла (максимум 5MB)
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $maxSize) {
                $errors['avatar'] = 'Размер файла не должен превышать 5MB';
            } else {
                // Создание директории для аватаров, если её нет
                $uploadDir = __DIR__ . '/uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Генерация уникального имени файла
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
                $filePath = $uploadDir . $fileName;

                // Удаление старого аватара, если он существует
                $userData = $user->getUserById($userId);
                if ($userData && !empty($userData['avatar'])) {
                    $oldAvatarPath = $uploadDir . $userData['avatar'];
                    if (file_exists($oldAvatarPath)) {
                        unlink($oldAvatarPath);
                    }
                }

                // Загрузка файла
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    // Обновление в базе данных
                    $result = $user->updateAvatar($userId, $fileName);
                    if ($result['success']) {
                        $success['avatar'] = $result['message'];
                    } else {
                        $errors['avatar'] = $result['message'];
                        // Удаляем загруженный файл, если не удалось обновить БД
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                } else {
                    $errors['avatar'] = 'Не удалось сохранить файл';
                }
            }
        }
    }
} else {
    $errors['avatar'] = 'Файл не был загружен';
}

// Перенаправление обратно на страницу профиля с сообщениями
$_SESSION['profile_errors'] = $errors;
$_SESSION['profile_success'] = $success;
header('Location: /profile');
exit();
