<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Класс View - рендеринг шаблонов
 * 
 * Отвечает за генерацию HTML-страниц:
 * - Полные страницы (layout + content)
 * - Частичные шаблоны (partials) для HTMX
 * - Специальные заголовки для HTMX (редиректы, триггеры)
 * 
 * В WordPress аналог - функция get_template_part() и
 * различные template functions.
 */
class View
{
    /**
     * Рендер полной страницы с layout-ом
     * 
     * @param string $template Имя шаблона страницы (pages/...)
     * @param array  $data     Данные для шаблона
     */
    public static function render(string $template, array $data = []): void
    {
        // Подключаем helper functions
        require_once __DIR__ . '/../../templates/helpers.php';
        
        extract($data);
        
        // Определяем путь к контенту страницы
        $templatePath = __DIR__ . '/../../templates/pages/' . $template . '_content.php';
        
        // Если нет отдельного файла контента, пробуем основной файл
        if (!file_exists($templatePath)) {
            $templatePath = __DIR__ . '/../../templates/pages/' . $template . '.php';
        }
        
        include __DIR__ . '/../../templates/layouts/main.php';
    }

    /**
     * Рендер частичного шаблона (partial)
     * 
     * Используется для HTMX-ответов - возвращает только
     * фрагмент HTML, который вставится в страницу.
     * 
     * @param string $template Имя шаблона (partials/...)
     * @param array  $data     Данные для шаблона
     */
    public static function renderPartial(string $template, array $data = []): void
    {
        // Устанавливаем заголовок для HTMX
        header('Content-Type: text/html; charset=utf-8');

        // Подключаем helper functions
        require_once __DIR__ . '/../../templates/helpers.php';

        extract($data);
        include __DIR__ . '/../../templates/' . $template . '.php';
    }

    /**
     * HTMX-редирект
     * 
     * В отличие от обычного Location-заголовка,
     * HTMX понимает заголовок HX-Redirect и обновляет
     * страницу без полной перезагрузки.
     */
    public static function htmxRedirect(string $url): void
    {
        header('HX-Redirect: ' . $url);
        exit;
    }

    /**
     * HTMX-триггер
     * 
     * Позволяет отправить клиенту событие, на которое
     * можно повесить дополнительные действия (например, показать уведомление)
     */
    public static function htmxTrigger(string $eventName): void
    {
        header('HX-Trigger: ' . json_encode($eventName));
    }

    /**
     * Редирект (обычный)
     */
    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Рендер JSON-ответа (для API)
     */
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
