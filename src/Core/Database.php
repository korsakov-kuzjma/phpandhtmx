<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Класс Database реализует паттерн Singleton (Одиночка)
 * 
 * Паттерн Singleton гарантирует, что у нас будет только одно
 * подключение к базе данных во всем приложении.
 * Это важно для экономии ресурсов сервера.
 * 
 * В WordPress похожий подход используется в $wpdb - там тоже
 * создается одно подключение на весь запрос.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    /**
     * Получение экземпляра PDO
     * 
     * @throws \Exception если не удалось подключиться к БД
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';

            try {
                self::$instance = new PDO(
                    $config['dsn'],
                    $config['username'],
                    $config['password'],
                    [
                        // Режим обработки ошибок: выбрасывать исключения
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        // По умолчанию возвращать ассоциативные массивы
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        // Не эмулировать подготовленные выражения (безопаснее)
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                // Логируем ошибку, но не показываем детали пользователю
                throw new \Exception("Ошибка подключения к базе данных");
            }
        }

        return self::$instance;
    }

    // Запрещаем клонирование
    private function __clone() {}

    // Запрещаем десериализацию
    public function __wakeup()
    {
        throw new \Exception("Нельзя десериализовать Singleton");
    }
}
