<?php
/**
 * partials/post_row.php - Одна строка списка постов
 * 
 * Используется в списке постов.
 * Поддерживает inline-редактирование через HTMX.
 * 
 * @var \App\Models\Post $post
 */

// Атрибут id важен для HTMX: по нему определяется какой элемент обновлять
?>
<li class="post-item" id="post-<?= $post->id ?>">
    <h2 class="post-title"><?= e($post->title) ?></h2>
    
    <div class="post-meta">
        Создан: <?= e(date('d.m.Y H:i', strtotime($post->createdAt))) ?>
        <?php if (isset($post->updatedAt) && $post->updatedAt !== $post->createdAt): ?>
            (обновлен: <?= e(date('d.m.Y H:i', strtotime($post->updatedAt))) ?>)
        <?php endif; ?>
    </div>
    
    <div class="post-content"><?= e($post->content) ?></div>
    
    <div class="post-actions">
        <!-- Кнопка редактирования: загружает форму inline -->
        <button class="btn btn-edit"
                hx-get="/posts/<?= $post->id ?>/edit"
                hx-target="#post-<?= $post->id ?>"
                hx-swap="outerHTML">
            Редактировать
        </button>
        
        <!-- Кнопка удаления с подтверждением -->
        <button class="btn btn-delete"
                hx-delete="/posts/<?= $post->id ?>/delete?_csrf=<?= \App\Helpers\Security::generateCsrfToken() ?>"
                hx-target="#post-<?= $post->id ?>"
                hx-swap="outerHTML"
                hx-confirm="Вы уверены, что хотите удалить этот пост?">
            Удалить
        </button>
    </div>
</li>
