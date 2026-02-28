<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Класс Router - маршрутизация запросов
 * 
 * Простой маршрутизатор, который сопоставляет URL с функциями-обработчиками.
 * Поддерживает параметры в URL (например, /posts/{id})
 * 
 * В WordPress аналог - WP_Rewrite для красивых URL (pretty permalinks).
 * Там используются регулярные выражения для сопоставления URL с шаблонами.
 */
class Router
{
    private array $routes = [];

    /**
     * Регистрация GET-маршрута
     */
    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Регистрация POST-маршрута
     */
    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Регистрация PUT-маршрута
     */
    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Регистрация DELETE-маршрута
     */
    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Добавление маршрута в коллекцию
     */
    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->convertToRegex($path)
        ];
    }

    /**
     * Преобразование пути в регулярное выражение
     * 
     * {id} становится (?P<id>[^/]+)
     * Это позволяет захватить параметр и передать в обработчик
     */
    private function convertToRegex(string $path): string
    {
        // Заменяем {param} на именованную группу захвата
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Диспетчеризация - выполнение маршрута
     * 
     * Проходит по всем маршрутам и ищет совпадение по методу и URL.
     * При совпадении вызывает обработчик с параметрами.
     */
    public function dispatch(string $uri, string $method): void
    {
        foreach ($this->routes as $route) {
            // Проверяем метод
            if ($route['method'] !== $method) {
                continue;
            }

            // Проверяем URL
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Извлекаем только именованные параметры
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Вызываем обработчик с параметрами
                call_user_func_array($route['handler'], $params);
                return;
            }
        }

        // Маршрут не найден - 404
        http_response_code(404);
        echo "<h1>404 - Страница не найдена</h1>";
        echo "<p>Запрошенная страница не существует.</p>";
    }
}
