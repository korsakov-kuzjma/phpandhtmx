<?php

declare(strict_types=1);

/**
 * Глобальные helper functions для представлений
 * 
 * Эти функции должны быть доступны во всех шаблонах.
 */

use App\Helpers\Security;

/**
 * Экранирование HTML (защита от XSS)
 * 
 * Аналог WordPress: esc_html()
 * 
 * @param string $string
 * @return string
 */
if (!function_exists('e')) {
    function e(string $string): string
    {
        return Security::escape($string);
    }
}
