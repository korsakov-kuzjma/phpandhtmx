<?php

declare(strict_types=1);

/**
 * Скрипт миграции - создание таблиц в БД
 * 
 * Запускать: php storage/migrate.php
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "Запуск миграции...\n";

try {
    $db = Database::getInstance();
    
    // Читаем SQL-файл
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    // Выполняем SQL (для SQLite каждая команда должна выполняться отдельно)
    $db->exec($sql);
    
    echo "Таблица 'posts' создана успешно!\n";
    
} catch (Exception $e) {
    echo "Ошибка миграции: " . $e->getMessage() . "\n";
    exit(1);
}
