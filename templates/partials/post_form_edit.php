<?php
/**
 * partials/post_form_edit.php - Форма редактирования поста
 * 
 * Используется для редактирования существующей публикации.
 * Работает через HTMX: заменяет строку поста на форму.
 * 
 * Использует POST с _method=PUT для эмуляции HTTP PUT.
 * 
 * @param \App\Models\Post $post   Пост для редактирования
 * @param string           $action URL для отправки
 * @param array            $data   Данные формы (при ошибках)
 * @param array            $errors Ошибки валидации
 */

$post = $post ?? null;
$action = $action ?? '/posts/' . ($post->id ?? 0) . '/edit';
$data = $data ?? [];
$errors = $errors ?? [];
?>

<div class="post-item editing" id="post-<?= $post->id ?>">
    <h3>Редактирование публикации</h3>
    
    <!-- PUT-запрос через POST с _method=PUT -->
    <form hx-put="<?= e($action) ?>" 
          hx-target="#post-<?= $post->id ?>"
          hx-swap="outerHTML">
        
        <!-- Эмуляция PUT -->
        <input type="hidden" name="_method" value="PUT">
        
        <!-- CSRF-токен -->
        <input type="hidden" name="csrf_token" 
               value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
        
        <div class="form-group <?= isset($errors['title']) ? 'error' : '' ?>">
            <label for="title-<?= $post->id ?>">Заголовок</label>
            <input type="text" 
                   id="title-<?= $post->id ?>" 
                   name="title" 
                   value="<?= e($data['title'] ?? $post->title) ?>"
                   required
                   minlength="3">
            <?php if (isset($errors['title'])): ?>
                <div class="error"><?= e($errors['title']) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group <?= isset($errors['content']) ? 'error' : '' ?>">
            <label for="content-<?= $post->id ?>">Содержание</label>
            <textarea id="content-<?= $post->id ?>" 
                      name="content" 
                      required
                      minlength="10"><?= e($data['content'] ?? $post->content) ?></textarea>
            <?php if (isset($errors['content'])): ?>
                <div class="error"><?= e($errors['content']) ?></div>
            <?php endif; ?>
        </div>
        
        <button type="submit">Сохранить</button>
        
        <!-- Кнопка отмены: возвращает строку поста -->
        <button type="button" 
                hx-get="/posts/<?= $post->id ?>"
                hx-target="#post-<?= $post->id ?>"
                hx-swap="outerHTML"
                style="background: #6c757d; margin-left: 10px;">
            Отмена
        </button>
    </form>
</div>
