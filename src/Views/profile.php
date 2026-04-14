<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            padding: 20px;
        }

        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .avatar-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #4CAF50;
            margin-bottom: 15px;
        }

        .avatar-upload {
            margin-top: 10px;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-group {
            margin-bottom: 20px;
        }

        .info-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        .info-group .value {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #ddd;
            min-height: 40px;
        }

        .password-field {
            font-family: monospace;
            letter-spacing: 2px;
        }

        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .btn-edit {
            background: #4CAF50;
            color: white;
        }

        .btn-edit:hover {
            background: #45a049;
        }

        .btn-change-password {
            background: #2196F3;
            color: white;
        }

        .btn-change-password:hover {
            background: #0b7dda;
        }

        .btn-logout {
            background: #f44336;
            color: white;
        }

        .btn-logout:hover {
            background: #d32f2f;
        }

        .btn-save {
            background: #4CAF50;
            color: white;
        }

        .btn-save:hover {
            background: #45a049;
        }

        .btn-cancel {
            background: #757575;
            color: white;
        }

        .btn-cancel:hover {
            background: #616161;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .error-message {
            color: #f44336;
            font-size: 14px;
            margin-top: 5px;
        }

        .success-message {
            color: #4CAF50;
            font-size: 14px;
            margin-top: 5px;
        }

        .edit-form {
            display: none;
            margin-top: 10px;
        }

        .edit-form.active {
            display: block;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        /* Для мобильных устройств */
        @media (max-width: 600px) {
            .profile-container {
                padding: 20px;
            }

            .actions {
                flex-direction: column;
            }

            .avatar {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>
<div class="profile-container">
    <div class="profile-header">
        <h1>Мой профиль</h1>
        <p>Личная информация</p>
    </div>

    <div class="avatar-section">
        <?php if ($user && $user->getAvatar()): ?>
            <img src="uploads/avatars/<?php echo htmlspecialchars($user->getAvatar()); ?>"
                 alt="Аватар <?php echo htmlspecialchars($user->getName()); ?>"
                 class="avatar" id="userAvatar">
        <?php else: ?>
            <img src="images/default-avatar.png"
                 alt="Аватар пользователя"
                 class="avatar" id="userAvatar">
        <?php endif; ?>

        <div class="avatar-upload">
            <?php if (isset($errors['avatar'])): ?>
                <div class="error-message"><?php echo htmlspecialchars(is_array($errors['avatar']) ? implode(', ', $errors['avatar']) : $errors['avatar']); ?></div>
            <?php endif; ?>
            <?php if (isset($success['avatar'])): ?>
                <div class="success-message"><?php echo htmlspecialchars(is_array($success['avatar']) ? implode(', ', $success['avatar']) : $success['avatar']); ?></div>
            <?php endif; ?>
            <form action="/upload_avatar" method="POST" enctype="multipart/form-data" id="avatarForm">
                <input type="file" id="avatarUpload" name="avatar" accept="image/*" style="display: none;"
                       onchange="document.getElementById('avatarForm').submit()">
                <button type="button" class="btn btn-edit" onclick="document.getElementById('avatarUpload').click()">
                    Сменить аватар
                </button>
            </form>
        </div>
    </div>

    <div class="info-section">
        <!-- Имя -->
        <div class="info-group">
            <label>Имя:</label>
            <div class="value" id="nameDisplay">
                <?php echo htmlspecialchars($user ? $user->getName() : 'Не указано'); ?>
            </div>
            <?php if (isset($errors['name'])): ?>
                <div class="error-message"><?php echo htmlspecialchars(is_array($errors['name']) ? implode(', ', $errors['name']) : $errors['name']); ?></div>
            <?php endif; ?>
            <?php if (isset($success['name'])): ?>
                <div class="success-message"><?php echo htmlspecialchars(is_array($success['name']) ? implode(', ', $success['name']) : $success['name']); ?></div>
            <?php endif; ?>
            <div class="edit-form" id="nameEditForm">
                <form action="/profile" method="POST">
                    <div class="form-group">
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user ? $user->getName() : ''); ?>" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_name" class="btn btn-save">Сохранить</button>
                        <button type="button" class="btn btn-cancel" onclick="cancelEdit('name')">Отмена</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Email -->
        <div class="info-group">
            <label>Электронная почта:</label>
            <div class="value" id="emailDisplay">
                <?php echo htmlspecialchars($user ? $user->getEmail() : 'Не указан'); ?>
            </div>
            <?php if (isset($errors['email'])): ?>
                <div class="error-message"><?php echo htmlspecialchars(is_array($errors['email']) ? implode(', ', $errors['email']) : $errors['email']); ?></div>
            <?php endif; ?>
            <?php if (isset($success['email'])): ?>
                <div class="success-message"><?php echo htmlspecialchars(is_array($success['email']) ? implode(', ', $success['email']) : $success['email']); ?></div>
            <?php endif; ?>
            <div class="edit-form" id="emailEditForm">
                <form action="/profile" method="POST">
                    <div class="form-group">
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user ? $user->getEmail() : ''); ?>" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_email" class="btn btn-save">Сохранить</button>
                        <button type="button" class="btn btn-cancel" onclick="cancelEdit('email')">Отмена</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Пароль -->
        <div class="info-group">
            <label>Пароль:</label>
            <div class="value password-field" id="passwordDisplay">
                ••••••••
            </div>
            <?php if (isset($errors['password'])): ?>
                <div class="error-message"><?php echo htmlspecialchars(is_array($errors['password']) ? implode(', ', $errors['password']) : $errors['password']); ?></div>
            <?php endif; ?>
            <?php if (isset($success['password'])): ?>
                <div class="success-message"><?php echo htmlspecialchars(is_array($success['password']) ? implode(', ', $success['password']) : $success['password']); ?></div>
            <?php endif; ?>
            <div class="edit-form" id="passwordEditForm">
                <form action="/profile" method="POST">
                    <div class="form-group">
                        <input type="password" name="current_password" placeholder="Текущий пароль" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="new_password" placeholder="Новый пароль" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="confirm_password" placeholder="Подтвердите новый пароль" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_password" class="btn btn-save">Сохранить</button>
                        <button type="button" class="btn btn-cancel" onclick="cancelEdit('password')">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="actions">
        <button class="btn btn-edit" onclick="editName()">Изменить имя</button>
        <button class="btn btn-edit" onclick="editEmail()">Изменить email</button>
        <button class="btn btn-change-password" onclick="editPassword()">Сменить пароль</button>
        <a href="/logout" class="btn btn-logout" style="text-decoration: none; color: inherit; line-height: 1;">Выйти</a>
    </div>
</div>

<script>
function editName() {
    document.getElementById('nameDisplay').style.display = 'none';
    document.getElementById('nameEditForm').classList.add('active');
}
function editEmail() {
    document.getElementById('emailDisplay').style.display = 'none';
    document.getElementById('emailEditForm').classList.add('active');
}
function editPassword() {
    document.getElementById('passwordDisplay').style.display = 'none';
    document.getElementById('passwordEditForm').classList.add('active');
}
function cancelEdit(field) {
    var display = document.getElementById(field + 'Display');
    var form = document.getElementById(field + 'EditForm');
    if (display) display.style.display = '';
    if (form) form.classList.remove('active');
}
</script>
</body>
</html>