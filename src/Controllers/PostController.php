<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Repositories\PostRepository;
use App\Models\Post;

/**
 * Класс PostController - контроллер публикаций
 * 
 * Обрабатывает HTTP-запросы, связанные с публикациями:
 * - GET /posts - список всех публикаций
 * - GET /posts/{id} - просмотр одной публикации
 * - POST /posts/create - создание новой публикации
 * - PUT /posts/{id}/edit - обновление публикации
 * - DELETE /posts/{id}/delete - удаление публикации
 * 
 * В WordPress аналог - функции темы like the_content(), wp_insert_post().
 */
class PostController extends BaseController
{
    private PostRepository $repository;

    public function __construct()
    {
        $this->repository = new PostRepository();
    }

    /**
     * GET /posts - главная страница со списком публикаций
     * 
     * Если это HTMX-запрос, возвращаем только список постов.
     * Если обычный запрос - полную страницу с layout-ом.
     */
    public function index(): void
    {
        $posts = $this->repository->getAll();

        if ($this->isHtmx()) {
            // HTMX: возвращаем только список постов
            View::renderPartial('partials/post_list', ['posts' => $posts]);
        } else {
            // Обычный запрос: возвращаем полную страницу
            View::render('posts', ['posts' => $posts]);
        }
    }

    /**
     * GET /posts/{id} - просмотр одной публикации
     */
    public function show(int $id): void
    {
        $post = $this->repository->getById($id);

        if (!$post) {
            http_response_code(404);
            echo "Публикация не найдена";
            return;
        }

        if ($this->isHtmx()) {
            View::renderPartial('partials/post_single', ['post' => $post]);
        } else {
            View::render('post_show', ['post' => $post]);
        }
    }

    /**
     * POST /posts/create - создание публикации
     * 
     * Обрабатывает форму создания поста.
     * Валидирует данные и сохраняет в БД.
     * 
     * ВАЖНО: Валидация CSRF вызывает validateCsrf()!
     */
    public function create(): void
    {
        // 1. Проверка CSRF-токена (обязательно для всех POST!)
        $this->validateCsrf();

        // 2. Получение и валидация данных
        $data = $_POST;
        $errors = $this->validate($data);

        if (!empty($errors)) {
            // Ошибки валидации - возвращаем форму с ошибками
            
            if ($this->isHtmx()) {
                // HTMX: возвращаем форму с ошибками
                View::renderPartial('partials/post_form', [
                    'errors' => $errors,
                    'data' => $data,
                    'action' => '/posts/create'
                ]);
            } else {
                // Обычный запрос: рендерим страницу с ошибками
                View::render('post_create', [
                    'errors' => $errors,
                    'data' => $data
                ]);
            }
            return;
        }

        // 3. Создание поста
        $post = new Post([
            'title' => trim($data['title']),
            'content' => trim($data['content'])
        ]);

        $createdPost = $this->repository->create($post);

        // 4. Ответ в зависимости от типа запроса
        if ($this->isHtmx()) {
            // HTMX: добавляем новый пост в начало списка
            // hx-swap="afterbegin" добавит элемент в начало контейнера
            View::htmxTrigger('post-created');
            View::renderPartial('partials/post_row', ['post' => $createdPost]);
        } else {
            // Обычный запрос: редирект на страницу поста
            View::redirect('/posts');
        }
    }

    /**
     * GET /posts/{id}/edit - форма редактирования
     */
    public function edit(int $id): void
    {
        $post = $this->repository->getById($id);

        if (!$post) {
            http_response_code(404);
            echo "Публикация не найдена";
            return;
        }

        if ($this->isHtmx()) {
            View::renderPartial('partials/post_form_edit', [
                'post' => $post,
                'action' => "/posts/{$id}/edit"
            ]);
        } else {
            View::render('post_edit', ['post' => $post]);
        }
    }

    /**
     * PUT /posts/{id}/edit - обновление публикации
     * 
     * PUT используется для обновления данных.
     * В HTML-формах нет поддержки PUT, поэтому используем POST с _method=PUT
     */
    public function update(int $id): void
    {
        // Проверяем _method для эмуляции PUT
        $method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        
        if ($method !== 'PUT') {
            http_response_code(405);
            echo "Метод не поддерживается";
            return;
        }

        $this->validateCsrf();

        $post = $this->repository->getById($id);
        if (!$post) {
            http_response_code(404);
            echo "Публикация не найдена";
            return;
        }

        $data = $_POST;
        $errors = $this->validate($data);

        if (!empty($errors)) {
            if ($this->isHtmx()) {
                View::renderPartial('partials/post_form_edit', [
                    'errors' => $errors,
                    'data' => $data,
                    'post' => $post,
                    'action' => "/posts/{$id}/edit"
                ]);
            }
            return;
        }

        $post->title = trim($data['title']);
        $post->content = trim($data['content']);

        $this->repository->update($post);

        if ($this->isHtmx()) {
            View::htmxTrigger('post-updated');
            View::renderPartial('partials/post_row', ['post' => $post]);
        } else {
            View::redirect('/posts');
        }
    }

    /**
     * DELETE /posts/{id}/delete - удаление публикации
     * 
     * Аналогично PUT, в HTML нет DELETE, используем POST с _method=DELETE
     */
    public function delete(int $id): void
    {
        // Проверяем _method для эмуляции DELETE
        $method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        
        if ($method !== 'DELETE') {
            http_response_code(405);
            echo "Метод не поддерживается";
            return;
        }

        // Проверяем CSRF (из POST, заголовка или URL)
        $this->validateCsrf();

        $post = $this->repository->getById($id);
        if (!$post) {
            http_response_code(404);
            echo "Публикация не найдена";
            return;
        }

        $this->repository->delete($id);

        if ($this->isHtmx()) {
            View::htmxTrigger('post-deleted');
        }
    }

    /**
     * Валидация данных публикации
     * 
     * @return array Массив ошибок (пустой, если валидация прошла)
     */
    private function validate(array $data): array
    {
        $errors = [];

        // Заголовок: обязателен, минимум 3 символа
        if (empty(trim($data['title'] ?? ''))) {
            $errors['title'] = 'Заголовок обязателен';
        } elseif (mb_strlen(trim($data['title'])) < 3) {
            $errors['title'] = 'Заголовок должен быть минимум 3 символа';
        }

        // Контент: обязателен, минимум 10 символов
        if (empty(trim($data['content'] ?? ''))) {
            $errors['content'] = 'Содержание обязательно';
        } elseif (mb_strlen(trim($data['content'])) < 10) {
            $errors['content'] = 'Содержание должно быть минимум 10 символов';
        }

        return $errors;
    }
}
