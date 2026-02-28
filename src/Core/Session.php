<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Класс Session - обертка над глобальной переменной $_SESSION
 * 
 * Обеспечивает удобный интерфейс для работы с сессиями:
 * - Безопасная инициализация с настройками cookie
 * - Типизированные методы get/set/has/remove
 * - Flash-сообщения (одноразовые сообщения)
 * 
 * В WordPress сессии используются реже, но механизм похож:
 * там тоже хранят данные в $_SESSION для передачи между запросами.
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,  // JavaScript не имеет доступа к cookie
                'cookie_secure' => false,    // true для HTTPS
                'cookie_samesite' => 'Strict', // Защита от CSRF
                'use_strict_mode' => true,   // Защита от похищения сессии
            ]);
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Flash-сообщения - одноразовые сообщения, которые показываются
     * один раз после какого-либо действия (например, "Пост создан")
     * 
     * @param string $key   Ключ сообщения
     * @param mixed  $value Сообщение (null = получить и удалить)
     */
    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value === null) {
            $value = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $value;
        }

        $_SESSION['flash'][$key] = $value;
    }
}
