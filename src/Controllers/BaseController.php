<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Базовый класс контроллера
 * 
 * Все контроллеры должны наследовать этот класс.
 * Содержит общие методы для всех контроллеров.
 */
abstract class BaseController
{
    /**
     * Проверка CSRF-токена для POST/PUT/DELETE запросов
     * 
     * Токен может быть передан:
     * - В POST-данных (form submission)
     * - В заголовке X-CSRF-Token (AJAX/HTMX)
     * - В URL параметре _csrf
     */
    protected function validateCsrf(): void
    {
        // Пробуем разные способы передачи токена
        $token = $_POST['csrf_token'] ?? 
                 $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 
                 ($_GET['_csrf'] ?? '');
        
        if (!\App\Helpers\Security::validateCsrfToken($token)) {
            http_response_code(403);
            die('Ошибка безопасности: неверный CSRF-токен');
        }
    }

    /**
     * Проверка, является ли запрос HTMX
     */
    protected function isHtmx(): bool
    {
        return isset($_SERVER['HTTP_HX_REQUEST']) && 
               $_SERVER['HTTP_HX_REQUEST'] === 'true';
    }
}
