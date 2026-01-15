<?php
// Получаем путь без query string и нормализуем его
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Обработка корневого пути
if ($requestUri === '/' || $requestUri === '') {
    header('Location: /catalog');
    exit();
}

if ($requestUri === '/registration') {
    if ($requestMethod === 'GET') {
        require_once './registration_form.php';
    }
    elseif ($requestMethod === 'POST') {
        require_once './handle_registration_form.php';
    }
}
elseif ($requestUri === '/login') {
    if ($requestMethod === 'GET') {
        require_once './login.php';
    }
    elseif ($requestMethod === 'POST') {
        require_once './handle_login.php';
    }
}
elseif ($requestUri === '/catalog') {
    if ($requestMethod === 'GET') {
        require_once './catalog.php';
    }
}
elseif ($requestUri === '/profile') {
    if ($requestMethod === 'GET') {
        require_once './profile.php';
    }
    elseif ($requestMethod === 'POST') {
        require_once './handle_profile_update.php';
    }
}
elseif ($requestUri === '/upload_avatar') {
    if ($requestMethod === 'POST') {
        require_once './upload_avatar.php';
    }
}
elseif ($requestUri === '/cart') {
    if ($requestMethod === 'GET') {
        require_once './cart.php';
    }
}
elseif ($requestUri === '/add-to-cart') {
    if ($requestMethod === 'POST') {
        require_once './handle_add_to_cart.php';
    }
}
elseif ($requestUri === '/update-cart') {
    if ($requestMethod === 'POST') {
        require_once './handle_update_cart.php';
    }
}
elseif ($requestUri === '/remove-from-cart') {
    if ($requestMethod === 'POST') {
        require_once './handle_remove_from_cart.php';
    }
}
else {
    http_response_code(404);
    require_once './404.php';
}