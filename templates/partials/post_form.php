<?php
/**
 * partials/post_form.php - Форма создания поста
 * 
 * Используется для создания новой публикации.
 * Включает CSRF-токен для защиты от атак.
 * 
 * Атрибуты HTMX:
 * - hx-post: URL для отправки формы
 * - hx-target: куда поместить ответ (список постов)
 * - hx-swap: как обработать ответ (добавить в начало)
 * - hx-on::after-request: действия после отправки
 * 
 * @param string $action URL для отправки
 * @param array  $data   Данные формы (при ошибках валидации)
 * @param array  $errors Ошибки валидации
 */

// Используем переданные данные или пустой массив
$data = $data ?? [];
$errors = $errors ?? [];
$action = $action ?? '/posts/create';
?>

<div class="post-item" id="post-form">
    <h3>Создание новой публикации</h3>
    
    <form hx-post="<?= e($action) ?>" 
          hx-target="#post-list-container"
          hx-swap="afterbegin"
          hx-on::after-request="if(event.detail.successful) this.reset()">
        
        <!-- CSRF-токен: обязательное поле для безопасности! -->
        <input type="hidden" name="csrf_token" 
               value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
        
        <div class="form-group <?= isset($errors['title']) ? 'error' : '' ?>">
            <label for="title">Заголовок</label>
            <input type="text" 
                   id="title" 
                   name="title" 
                   value="<?= e($data['title'] ?? '') ?>"
                   required
                   minlength="3">
            <?php if (isset($errors['title'])): ?>
                <div class="error"><?= e($errors['title']) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group <?= isset($errors['content']) ? 'error' : '' ?>">
            <label for="content">Содержание</label>
            <textarea id="content" 
                      name="content" 
                      required
                      minlength="10"><?= e($data['content'] ?? '') ?></textarea>
            <?php if (isset($errors['content'])): ?>
                <div class="error"><?= e($errors['content']) ?></div>
            <?php endif; ?>
        </div>
        
        <button type="submit">Создать</button>
        
        <!-- Кнопка отмены: скрывает форму -->
        <button type="button" 
                onclick="this.closest('.post-item').remove()"
                style="background: #6c757d; margin-left: 10px;">
            Отмена
        </button>
    </form>
</div>
