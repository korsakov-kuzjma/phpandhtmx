<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Класс ErrorHandler - централизованная обработка ошибок
 * 
 * Перехватывает все ошибки и исключения, логирует их
 * и показывает пользователю понятное сообщение.
 * 
 * В продакшене (debug = false) детали ошибок НЕ показываются -
 * это важно для безопасности!
 * 
 * В WordPress аналог - WP_DEBUG и обработчик в wp-debug.php.
 */
class ErrorHandler
{
    private Logger $logger;
    private bool $debug;

    public function __construct(bool $debug = false)
    {
        $this->logger = new Logger();
        $this->debug = $debug;

        // Регистрируем обработчики
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }

    /**
     * Обработчик ошибок PHP
     */
    public function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): void
    {
        // Логируем ошибку
        $this->logger->error("{$errstr} in {$errfile}:{$errline}");

        if ($this->debug) {
            // В режиме разработки показываем детали
            echo "<h1>Ошибка</h1>";
            echo "<p><strong>{$errstr}</strong></p>";
            echo "<p>Файл: {$errfile}:{$errline}</p>";
        } else {
            // В продакшене - общее сообщение
            http_response_code(500);
            echo "Произошла ошибка. Попробуйте позже.";
        }
    }

    /**
     * Обработчик непойманных исключений
     */
    public function handleException(\Throwable $exception): void
    {
        // Логируем исключение с полной информацией
        $this->logger->error($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        if ($this->debug) {
            echo "<h1>Исключение</h1>";
            echo "<p><strong>" . htmlspecialchars($exception->getMessage()) . "</strong></p>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            http_response_code(500);
            echo "Произошла непредвиденная ошибка.";
        }
    }
}
