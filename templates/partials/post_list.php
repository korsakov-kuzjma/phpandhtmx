<?php
/**
 * partials/post_list.php - Список постов (для HTMX)
 * 
 * Используется при HTMX-запросах для обновления списка.
 * 
 * @param \App\Models\Post[] $posts
 */

if (!isset($posts) || empty($posts)):
    echo '<p>Пока нет публикаций. Создайте первую!</p>';
else:
    echo '<ul class="post-list">';
    foreach ($posts as $post) {
        // Подключаем строку поста
        ob_start();
        include __DIR__ . '/post_row.php';
        echo ob_get_clean();
    }
    echo '</ul>';
endif;
