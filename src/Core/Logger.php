<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Класс Logger - простой логгер в файл
 * 
 * Записывает информацию о событиях в приложении.
 * Важно: не записываем пароли, токены и другую чувствительную информацию!
 * 
 * В WordPress аналог - debug.log при включенном WP_DEBUG_LOG.
 */
class Logger
{
    private string $logFile;

    public function __construct()
    {
        // Создаем директорию для логов, если нет
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $this->logFile = $logDir . '/app.log';
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    private function log(string $level, string $message, array $context): void
    {
        $timestamp = date('Y-m-d H:i:s');
        
        // Контекст в JSON (полезно для отладки)
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        
        // Формат: [время] [уровень] сообщение [контекст]
        $logLine = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
        
        // FILE_APPEND - дописывать в конец файла
        // LOCK_EX - блокировать файл при записи (безопасность)
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}
