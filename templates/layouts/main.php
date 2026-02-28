<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Mini Blog') ?></title>
    
    <?php 
    // Подключаем helper functions для представлений
    require_once __DIR__ . '/../helpers.php'; 
    ?>
    
    <!-- Подключаем HTMX из CDN -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    
    <!-- Базовые стили -->
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        h1, h2, h3 {
            color: #333;
        }
        
        a {
            color: #0066cc;
            text-decoration: none;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        /* Формы */
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        button, .btn {
            background: #0066cc;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover, .btn:hover {
            background: #0052a3;
        }
        
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        /* Ошибки валидации */
        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .error input,
        .error textarea {
            border-color: #dc3545;
        }
        
        /* Посты */
        .post-list {
            list-style: none;
            padding: 0;
        }
        
        .post-item {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .post-title {
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .post-meta {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .post-content {
            white-space: pre-wrap;
        }
        
        /* Действия */
        .post-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .btn-edit {
            background: #28a745;
        }
        
        .btn-edit:hover {
            background: #218838;
        }
        
        .btn-delete {
            background: #dc3545;
            margin-left: 10px;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #0066cc;
        }
        
        header h1 {
            margin: 0;
        }
        
        /* Уведомления */
        .flash-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .flash-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* Загрузка */
        .htmx-indicator {
            opacity: 0;
            transition: opacity 200ms;
        }
        
        .htmx-request .htmx-indicator {
            opacity: 1;
        }
        
        /* inline-редактирование */
        .editing {
            background: #fffde7;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <header>
        <h1><a href="/posts" style="color: #0066cc; text-decoration: none;">Mini Blog</a></h1>
        <button hx-get="/posts/create/form" 
                hx-target="#post-form-container"
                hx-swap="innerHTML">
            + Новый пост
        </button>
    </header>

    <!-- Контейнер для формы создания (появляется по клику) -->
    <div id="post-form-container"></div>
    
    <!-- Flash-сообщения -->
    <?php if (\App\Core\Session::flash('success')): ?>
        <div class="flash-message flash-success">
            <?= e(\App\Core\Session::flash('success')) ?>
        </div>
    <?php endif; ?>

    <!-- Основной контент -->
    <main>
        <?php require $templatePath ?? ''; ?>
    </main>
    
    <!-- HTMX: обрабатываем триггеры -->
    <script>
        // Обработка события post-created
        document.body.addEventListener('post-created', function() {
            // Очищаем форму после создания
            const form = document.querySelector('#post-form-container form');
            if (form) form.reset();
            
            // Показываем уведомление
            showFlash('Пост успешно создан!');
        });
        
        // Обработка события post-updated
        document.body.addEventListener('post-updated', function() {
            showFlash('Пост успешно обновлен!');
        });
        
        // Обработка события post-deleted
        document.body.addEventListener('post-deleted', function() {
            showFlash('Пост удален.');
        });
        
        function showFlash(message) {
            // Можно реализовать красивое уведомление
            console.log(message);
        }
    </script>
</body>
</html>
