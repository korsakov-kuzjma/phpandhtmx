<?php
/**
 * Страница списка постов (pages/posts.php)
 * 
 * Для обычного запроса рендерится через layout.
 */

$pageTitle = 'Все публикации - Mini Blog';
$templatePath = __DIR__ . '/posts_content.php';

include __DIR__ . '/../layouts/main.php';
