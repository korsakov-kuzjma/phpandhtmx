<?php

declare(strict_types=1);

/**
 * Маршрутизация приложения
 * 
 * Определяет соответствие URL и методов контроллера.
 * Использует методы $router->get(), post(), put(), delete().
 */

use App\Controllers\PostController;

$router->get('/', function() {
    // Перенаправляем на страницу постов
    header('Location: /posts');
    exit;
});

// Список постов (главная страница)
$router->get('/posts', function() {
    $controller = new PostController();
    $controller->index();
});

// Просмотр одного поста
$router->get('/posts/{id}', function(int $id) {
    $controller = new PostController();
    $controller->show($id);
});

// Форма создания поста (HTMX)
$router->get('/posts/create/form', function() {
    \App\Core\View::renderPartial('partials/post_form', [
        'action' => '/posts/create'
    ]);
});

// Создание поста
$router->post('/posts/create', function() {
    $controller = new PostController();
    $controller->create();
});

// Форма редактирования поста
$router->get('/posts/{id}/edit', function(int $id) {
    $controller = new PostController();
    $controller->edit($id);
});

// Обновление поста (PUT)
$router->put('/posts/{id}/edit', function(int $id) {
    $controller = new PostController();
    $controller->update($id);
});

// Удаление поста (DELETE)
$router->delete('/posts/{id}/delete', function(int $id) {
    $controller = new PostController();
    $controller->delete($id);
});
