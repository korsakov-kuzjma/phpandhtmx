<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Класс Security - функции безопасности
 * 
 * В WordPress аналогичные функции: esc_html(), esc_attr(), esc_url().
 * Всегда используй эти функции при выводе данных в HTML!
 */
class Security
{
    /**
     * Генерация CSRF-токена
     * 
     * CSRF (Cross-Site Request Forgery) - атака, при которой
     * злоумышленник отправляет запрос от имени авторизованного пользователя.
     * 
     * Токен создается один раз и хранится в сессии.
     * При каждой отправке формы токен проверяется.
     * 
     * @return string
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            // Генерируем случайную строку (32 байта = 64 символа)
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Проверка CSRF-токена
     * 
     * Используем hash_equals для защиты от timing-атак
     * (сравнение по времени, чтобы нельзя было подобрать токен)
     * 
     * @return bool
     */
    public static function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Экранирование HTML-символов (защита от XSS)
     * 
     * XSS (Cross-Site Scripting) - атака, при которой
     * вредоносный скрипт внедряется в страницу.
     * 
     * Экранирование преобразует <, >, ", ' в HTML-сущности,
     * поэтому браузер не выполнит их как код.
     * 
     * В WordPress: esc_html() и esc_attr()
     * 
     * @return string
     */
    public static function escape(string $data): string
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Функция-helper для удобного использования в шаблонах
 * 
 * Пример использования: <?= e($post->title) ?>
 */
function e(string $string): string
{
    return Security::escape($string);
}
