<?php
/**
 * Router script для встроенного PHP сервера
 * Запуск: php -S localhost:8000 -t public router.php
 * 
 * Этот скрипт перенаправляет все запросы на index.php,
 * но сначала проверяет, не запрашивается ли статический файл.
 */

if ($_SERVER['SCRIPT_FILENAME'] !== __DIR__ . '/index.php') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    
    if (file_exists($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        
        $allowed = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf'];
        
        if (in_array($ext, $allowed)) {
            return false; // Отдаем файл напрямую (CSS, JS, изображения)
        }
    }
}

// Передаем управление index.php
require __DIR__ . '/index.php';
