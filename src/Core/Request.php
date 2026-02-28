<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Класс Request - работа с HTTP-запросом
 * 
 * Инкапсулирует работу с суперглобальными массивами $_GET, $_POST,
 * $_SERVER и предоставляет удобные методы для определения типа запроса.
 * 
 * Особое внимание уделено HTMX - это ключевая часть нашего приложения.
 */
class Request
{
    /**
     * Определяет, является ли запрос HTMX-ом
     * 
     * HTMX отправляет специальный заголовок HTTP_HX_REQUEST = 'true'
     * Это позволяет серверу понять, что нужно вернуть не полную страницу,
     * а только HTML-фрагмент для обновления части страницы.
     * 
     * @return bool
     */
    public function isHtmxRequest(): bool
    {
        return isset($_SERVER['HTTP_HX_REQUEST']) && 
               $_SERVER['HTTP_HX_REQUEST'] === 'true';
    }

    /**
     * Получает заголовок HTMX-Trigger
     * 
     * HTMX отправляет событие, которое вызвало запрос
     * (например, 'click', 'submit', 'reveal')
     */
    public function getHtmxTrigger(): ?string
    {
        return $_SERVER['HTTP_HX_TRIGGER'] ?? null;
    }

    /**
     * Получает заголовок HTMX-Target
     * 
     * Указывает, какой элемент нужно обновить на странице
     */
    public function getHtmxTarget(): ?string
    {
        return $_SERVER['HTTP_HX_TARGET'] ?? null;
    }

    /**
     * Получает текущий URI
     */
    public function getUri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Получает метод запроса (GET, POST, PUT, DELETE)
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Получает данные POST или пустой массив
     */
    public function getPost(): array
    {
        return $_POST;
    }

    /**
     * Получает данные GET или пустой массив
     */
    public function getQuery(): array
    {
        return $_GET;
    }
}
