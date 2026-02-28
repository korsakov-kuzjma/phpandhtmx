<?php
/**
 * partials/post_single.php - Просмотр одного поста
 * 
 * Используется для детального просмотра публикации (если понадобится).
 * 
 * @var \App\Models\Post $post
 */
?>
<article class="post-item">
    <h1><?= e($post->title) ?></h1>
    
    <div class="post-meta">
        Опубликовано: <?= e(date('d.m.Y H:i', strtotime($post->createdAt))) ?>
    </div>
    
    <div class="post-content"><?= e($post->content) ?></div>
    
    <div class="post-actions">
        <a href="/posts" style="margin-right: 15px;">&larr; К списку</a>
        
        <button class="btn btn-edit"
                hx-get="/posts/<?= $post->id ?>/edit"
                hx-target="body"
                hx-swap="beforeend">
            Редактировать
        </button>
        
        <button class="btn btn-delete"
                hx-delete="/posts/<?= $post->id ?>/delete"
                hx-target="body"
                hx-swap="outerHTML"
                hx-confirm="Удалить этот пост?">
            Удалить
        </button>
    </div>
</article>
