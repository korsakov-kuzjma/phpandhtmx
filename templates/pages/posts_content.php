<?php
/**
 * Контент страницы списка постов
 * Этот файл подключается из layout main.php
 */

// Если это HTMX-запрос, используем только содержимое списка
// Если обычный запрос - оборачиваем в контейнер
$isHtmx = isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] === 'true';

if (!$isHtmx): ?>
    <!-- Контейнер для списка постов (обновляется через HTMX) -->
    <div id="post-list-container">
<?php endif; ?>

        <?php if (empty($posts)): ?>
            <p>Пока нет публикаций. Создайте первую!</p>
        <?php else: ?>
            <ul class="post-list">
                <?php foreach ($posts as $post): ?>
                    <?php require __DIR__ . '/../partials/post_row.php'; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

<?php if (!$isHtmx): ?>
    </div>
<?php endif; ?>
