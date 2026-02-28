<?php

declare(strict_types=1);

/**
 * Точка входа в приложение (Front Controller)
 * 
 * Все запросы проходят через этот файл - это паттерн Front Controller.
 * В WordPress аналог - wp-blog-header.php -> wp-load.php
 * 
 * Преимущества:
 * - Единая точка входа
 * - Централизованная обработка ошибок
 * - Удобная маршрутизация
 */

// 1. Подключаем автозагрузчик классов
require __DIR__ . '/../vendor/autoload.php';

// 2. Импортируем классы
use App\Core\ErrorHandler;
use App\Core\Session;
use App\Core\Logger;
use App\Core\Router;

// Загружаем конфигурацию
$config = require __DIR__ . '/../config/app.php';

new ErrorHandler($config['debug']);

// 3. Запускаем сессию
Session::start();

// 4. Создаем логгер (для записи событий)
$logger = new Logger();

// 5. Инициализируем маршрутизацию
$router = new Router();

// Подключаем маршруты
require __DIR__ . '/../src/routes.php';

// 6. Диспетчеризация (выполнение) маршрута
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($uri, $method);
