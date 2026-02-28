# Руководство для AI-ассистентов по проекту PHP + HTMX

## Общая информация о проекте

Это веб-приложение на чистом PHP с использованием HTMX для динамических
обновлений интерфейса. Проект запускается на встроенном PHP-сервере для
разработки.

### Технологический стек

- **Backend**: PHP 8.1+ (встроенный сервер)
- **Frontend**: HTMX для AJAX-запросов
- **Стилизация**: CSS (vanilla или Tailwind)
- **База данных**: SQLite/MySQL (на выбор)
- **Шаблонизация**: Нативный PHP с разделением логики и представления

## Архитектура проекта

### Структура директорий

```text
project/
├── public/                 # Единственная публичная директория
│   ├── index.php          # Точка входа (Front Controller)
│   ├── router.php         # Скрипт маршрутизации для встроенного сервера
│   ├── assets/            # Статические файлы
│   │   ├── css/
│   │   ├── js/            # Минимальный JS, только HTMX
│   │   └── images/
│   └── .htaccess          # Правила для Apache (опционально)
├── src/                   # Исходный код приложения
│   ├── Controllers/       # Обработчики запросов
│   ├── Services/          # Бизнес-логика
│   ├── Models/            # Работа с данными
│   ├── Middleware/        # Промежуточное ПО
│   ├── Helpers/           # Вспомогательные функции
│   └── Core/              # Ядро приложения
├── templates/             # HTML-шаблоны
│   ├── layouts/           # Основные макеты
│   ├── pages/             # Полные страницы
│   ├── partials/          # Фрагменты для HTMX
│   └── components/        # Переиспользуемые компоненты
├── config/                # Конфигурация
│   ├── app.php            # Настройки приложения
│   ├── database.php       # Настройки БД
│   └── security.php       # Настройки безопасности
├── storage/               # Хранилище (недоступно из веба)
│   ├── logs/              # Лог-файлы
│   ├── cache/             # Кэш
│   └── uploads/           # Загруженные файлы
├── tests/                 # Тесты
└── vendor/                # Зависимости Composer
```

### 1. Именование и организация кода

**Классы и файлы:**

- Используйте PSR-12 стандарт
- Один класс на файл
- Имена классов в PascalCase: `UserController`, `AuthService`
- Имена файлов должны совпадать с именем класса
- Пространства имен: `App\Controllers`, `App\Services`

**Методы и переменные:**

- camelCase для методов и переменных: `getUserData()`, `$userName`
- snake_case для констант: `MAX_UPLOAD_SIZE`
- Частные свойства с префиксом `$`: `$databaseConnection`

### 2. Контроллеры

Контроллеры должны быть тонкими и делегировать бизнес-логику сервисам.

**Правильно:**

```php
<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Core\View;

class UserController extends BaseController
{
    private UserService $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index(): void
    {
        $users = $this->userService->getAllUsers();
        View::render('pages/users', ['users' => $users]);
    }
    
    public function create(): void
    {
        // Обработка HTMX запроса
        if ($this->isHtmxRequest()) {
            $errors = $this->validateUserData($_POST);
            if (empty($errors)) {
                $user = $this->userService->createUser($_POST);
                View::renderPartial('partials/user_row', ['user' => $user]);
            } else {
                View::renderPartial('partials/user_form', [
                    'errors' => $errors,
                    'data' => $_POST
                ]);
            }
        } else {
            View::render('pages/user_create');
        }
    }
}
```

**Неправильно:**

```php
// Не делайте так - бизнес-логика в контроллере
public function create(): void
{
    $pdo = new PDO(...); // Создание подключения в контроллере
    $stmt = $pdo->prepare("INSERT INTO users..."); // SQL в контроллере
    // Валидация размазана по коду
}
```

### 3. Модели и работа с данными

Используйте паттерн Repository или Active Record.

**Пример модели:**

```php
<?php

namespace App\Models;

class User
{
    public int $id;
    public string $name;
    public string $email;
    public \DateTime $createdAt;
    
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->createdAt = isset($data['created_at']) 
            ? new \DateTime($data['created_at']) 
            : new \DateTime();
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s')
        ];
    }
}
```

**Пример Repository:**

```php
<?php

namespace App\Repositories;

use App\Models\User;
use PDO;

class UserRepository
{
    private PDO $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $data ? new User($data) : null;
    }
    
    public function save(User $user): bool
    {
        if ($user->id === 0) {
            return $this->insert($user);
        }
        return $this->update($user);
    }
    
    private function insert(User $user): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, created_at) 
            VALUES (:name, :email, :created_at)
        ");
        return $stmt->execute($user->toArray());
    }
}
```

### 4. Безопасность

#### CSRF защита

**Обязательно для всех форм:**

```php
// В Helper/Security.php
namespace App\Helpers;

class Security
{
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function escape(string $data): string
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}
```

**Использование в шаблонах:**

```php
<!-- templates/partials/form.php -->
<form hx-post="/users/create" hx-target="#user-list">
    <input type="hidden" name="csrf_token" 
           value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
    
    <input type="text" name="name" required>
    <input type="email" name="email" required>
    
    <button type="submit">Создать</button>
</form>
```

**Проверка в контроллере:**

```php
public function create(): void
{
    if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Invalid CSRF token');
    }
    
    // Дальнейшая обработка
}
```

#### XSS защита

**Всегда экранируйте вывод:**

```php
// Правильно
echo Security::escape($userInput);

// В шаблонах создайте helper функцию
function e(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Использование в шаблонах
<h1><?= e($pageTitle) ?></h1>
<p><?= e($user->name) ?></p>
```

#### Валидация входных данных

```php
// Services/ValidationService.php
namespace App\Services;

class ValidationService
{
    private array $errors = [];
    
    public function validate(array $data, array $rules): bool
    {
        foreach ($rules as $field => $ruleSet) {
            $rules_array = explode('|', $ruleSet);
            
            foreach ($rules_array as $rule) {
                $this->applyRule($field, $data[$field] ?? null, $rule);
            }
        }
        
        return empty($this->errors);
    }
    
    private function applyRule(string $field, $value, string $rule): void
    {
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = "Поле {$field} обязательно";
                }
                break;
                
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "Некорректный email";
                }
                break;
                
            case 'min:5':
                if (strlen($value) < 5) {
                    $this->errors[$field][] = "Минимум 5 символов";
                }
                break;
        }
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
}
```

### 5. HTMX интеграция

#### Определение HTMX запроса

```php
// Core/Request.php
namespace App\Core;

class Request
{
    public function isHtmxRequest(): bool
    {
        return isset($_SERVER['HTTP_HX_REQUEST']) && 
               $_SERVER['HTTP_HX_REQUEST'] === 'true';
    }
    
    public function getHtmxTrigger(): ?string
    {
        return $_SERVER['HTTP_HX_TRIGGER'] ?? null;
    }
    
    public function getHtmxTarget(): ?string
    {
        return $_SERVER['HTTP_HX_TARGET'] ?? null;
    }
}
```

#### Ответы для HTMX

```php
// Core/View.php
namespace App\Core;

class View
{
    public static function render(string $template, array $data = []): void
    {
        extract($data);
        include __DIR__ . '/../templates/layouts/main.php';
    }
    
    public static function renderPartial(string $template, array $data = []): void
    {
        // Установка заголовков для HTMX
        header('Content-Type: text/html; charset=utf-8');
        
        extract($data);
        include __DIR__ . '/../templates/' . $template . '.php';
    }
    
    public static function htmxRedirect(string $url): void
    {
        header('HX-Redirect: ' . $url);
        exit;
    }
    
    public static function htmxTrigger(string $eventName): void
    {
        header('HX-Trigger: ' . json_encode($eventName));
    }
    
    public static function htmxReselect(string $selector): void
    {
        header('HX-Reselect: ' . $selector);
    }
}
```

#### Примеры шаблонов для HTMX

**partials/user_list.php:**

```php
<div id="user-list">
    <?php foreach ($users as $user): ?>
        <?= $this->renderPartial('partials/user_row', ['user' => $user]) ?>
    <?php endforeach; ?>
</div>
```

**partials/user_row.php:**

```php
<div class="user-row" id="user-<?= $user->id ?>">
    <span><?= e($user->name) ?></span>
    <span><?= e($user->email) ?></span>
    
    <button hx-delete="/users/<?= $user->id ?>"
            hx-target="#user-<?= $user->id ?>"
            hx-swap="outerHTML"
            hx-confirm="Удалить пользователя?">
        Удалить
    </button>
</div>
```

### 6. Middleware

```php
// Middleware/AuthMiddleware.php
namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            if ($this->isHtmxRequest()) {
                header('HX-Redirect: /login');
                exit;
            }
            header('Location: /login');
            exit;
        }
    }
    
    private function isHtmxRequest(): bool
    {
        return isset($_SERVER['HTTP_HX_REQUEST']);
    }
}

// Middleware/CsrfMiddleware.php
namespace App\Middleware;

use App\Helpers\Security;

class CsrfMiddleware
{
    public function handle(): void
    {
        // Проверяем CSRF для опасных методов
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
            $token = $_POST['csrf_token'] ?? 
                     $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            
            if (!Security::validateCsrfToken($token)) {
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }
    }
}
```

### 7. Работа с сессиями

```php
// Core/Session.php
namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => false, // true для HTTPS
                'cookie_samesite' => 'Strict',
                'use_strict_mode' => true
            ]);
        }
    }
    
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    public static function get(string $key, $default = null)
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
    
    public static function flash(string $key, $message = null)
    {
        if ($message === null) {
            $value = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $value;
        }
        
        $_SESSION['flash'][$key] = $message;
    }
}
```

### 8. Логирование

```php
// Core/Logger.php
namespace App\Core;

class Logger
{
    private string $logFile;
    
    public function __construct()
    {
        $this->logFile = __DIR__ . '/../storage/logs/app.log';
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
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        
        $logLine = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}
```

### 9. Обработка ошибок

```php
// Core/ErrorHandler.php
namespace App\Core;

class ErrorHandler
{
    private Logger $logger;
    private bool $debug;
    
    public function __construct(bool $debug = false)
    {
        $this->logger = new Logger();
        $this->debug = $debug;
        
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }
    
    public function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): void
    {
        $this->logger->error("{$errstr} in {$errfile}:{$errline}");
        
        if ($this->debug) {
            echo "<h1>Error</h1><p>{$errstr}</p><p>File: {$errfile}:{$errline}</p>";
        } else {
            http_response_code(500);
            echo "Произошла ошибка. Попробуйте позже.";
        }
    }
    
    public function handleException(\Throwable $exception): void
    {
        $this->logger->error($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        if ($this->debug) {
            echo "<h1>Exception</h1>";
            echo "<p>{$exception->getMessage()}</p>";
            echo "<pre>{$exception->getTraceAsString()}</pre>";
        } else {
            http_response_code(500);
            echo "Произошла непредвиденная ошибка.";
        }
    }
}
```

### 10. База данных

```php
// Core/Database.php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';
            
            try {
                self::$instance = new PDO(
                    $config['dsn'],
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new \Exception("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
    
    // Запрещаем клонирование
    private function __clone() {}
    
    // Запрещаем десериализацию
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
```

### 11. Маршрутизация

```php
// Core/Router.php
namespace App\Core;

class Router
{
    private array $routes = [];
    private Request $request;
    
    public function __construct()
    {
        $this->request = new Request();
    }
    
    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    private function addRoute(
        string $method,
        string $path,
        callable $handler
    ): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->convertToRegex($path)
        ];
    }
    
    private function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    public function dispatch(string $uri, string $method): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                call_user_func_array($route['handler'], $params);
                return;
            }
        }
        
        http_response_code(404);
        echo "404 - Not Found";
    }
}
```

### 12. Точка входа (public/index.php)

```php
<?php

declare(strict_types=1);

// Автозагрузка классов
require __DIR__ . '/../vendor/autoload.php';

// Инициализация обработчика ошибок
use App\Core\ErrorHandler;
use App\Core\Session;
use App\Core\Router;

$debug = true; // В продакшене false
new ErrorHandler($debug);

// Инициализация сессии
Session::start();

// Создание роутера
$router = new Router();

// Регистрация маршрутов
$router->get('/', function() {
    \App\Core\View::render('pages/home');
});

$router->get('/users', function() {
    $controller = new \App\Controllers\UserController();
    $controller->index();
});

$router->post('/users/create', function() {
    $controller = new \App\Controllers\UserController();
    $controller->create();
});

$router->delete('/users/{id}', function(int $id) {
    $controller = new \App\Controllers\UserController();
    $controller->delete($id);
});

// Диспетчеризация запроса
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($uri, $method);
```

### 13. Скрипт маршрутизации для встроенного сервера (public/router.php)

```php
<?php

/**
 * Router script для встроенного PHP сервера
 * Запуск: php -S localhost:8000 -t public router.php
 */

// Если файл существует и это не PHP файл - отдаем его
if ($_SERVER['SCRIPT_FILENAME'] !== __DIR__ . '/index.php') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    
    if (file_exists($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        
        // Разрешенные расширения для статических файлов
        $allowed = [
            'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico',
            'woff', 'woff2', 'ttf'
        ];
        
        if (in_array($ext, $allowed)) {
            return false; // Отдаем файл как есть
        }
    }
}

// Иначе передаем управление index.php
require __DIR__ . '/index.php';
```

### 14. Конфигурация

**config/app.php:**

```php
<?php

return [
    'name' => 'My PHP HTMX App',
    'debug' => true,
    'url' => 'http://localhost:8000',
    'timezone' => 'Europe/Moscow',
    'locale' => 'ru',
];
```

**config/database.php:**

```php
<?php

return [
    'driver' => 'sqlite', // или mysql
    'dsn' => 'sqlite:' . __DIR__ . '/../storage/database.sqlite',
    // Для MySQL:
    // 'dsn' => 'mysql:host=localhost;dbname=myapp;charset=utf8mb4',
    'username' => '',
    'password' => '',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]
];
```

### 15. Composer настройки

**composer.json:**

```json
{
    "name": "myapp/php-htmx",
    "description": "PHP + HTMX Application",
    "type": "project",
    "require": {
        "php": ">=8.1"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "scripts": {
        "start": "php -S localhost:8000 -t public public/router.php",
        "migrate": "php src/Console/Migrate.php"
    }
}
```

### 16. .htaccess для Apache (опционально)

```apache
# public/.htaccess
RewriteEngine On

# Перенаправление на HTTPS (в продакшене)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Если файл или директория существуют - отдаем их
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Иначе передаем index.php
RewriteRule ^ index.php [QSA,L]

# Защита файлов
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Отключаем просмотр директорий
Options -Indexes

# Установка заголовков безопасности
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "DENY"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

### 17. .gitignore

```gitignore
# Зависимости
/vendor/

# Конфигурация с чувствительными данными
/config/local.php

# База данных
/storage/database.sqlite

# Логи
/storage/logs/*.log

# Кэш
/storage/cache/*
!storage/cache/.gitkeep

# Загруженные файлы
/storage/uploads/*
!storage/uploads/.gitkeep

# IDE
.idea/
.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Environment
.env
```

### 18. Пример полного рабочего flow

#### Сценарий: Создание пользователя с валидацией

1. **Шаблон формы** (`templates/partials/user_form.php`):

```php
<form hx-post="/users/create" 
      hx-target="#user-list" 
      hx-swap="beforeend"
      hx-on::after-request="this.reset()">
    
    <input type="hidden" name="csrf_token" 
           value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
    
    <div>
        <label>Имя:</label>
        <input type="text" name="name" value="<?= e($data['name'] ?? '') ?>" required>
        <?php if (isset($errors['name'])): ?>
            <span class="error"><?= e($errors['name'][0]) ?></span>
        <?php endif; ?>
    </div>
    
    <div>
        <label>Email:</label>
        <input type="email" name="email"
               value="<?= e($data['email'] ?? '') ?>" required>
        <?php if (isset($errors['email'])): ?>
            <span class="error"><?= e($errors['email'][0]) ?></span>
        <?php endif; ?>
    </div>
    
    <button type="submit">Создать</button>
</form>
```

1. **Контроллер** (`src/Controllers/UserController.php`):

```php
public function create(): void
{
    $validator = new ValidationService();
    $isValid = $validator->validate($_POST, [
        'name' => 'required|min:3',
        'email' => 'required|email'
    ]);
    
    if (!$isValid) {
        View::renderPartial('partials/user_form', [
            'errors' => $validator->getErrors(),
            'data' => $_POST
        ]);
        return;
    }
    
    $userService = new UserService();
    $user = $userService->createUser([
        'name' => $_POST['name'],
        'email' => $_POST['email']
    ]);
    
    View::renderPartial('partials/user_row', ['user' => $user]);
}
```

1. **Сервис** (`src/Services/UserService.php`):

```php
public function createUser(array $data): User
{
    $user = new User([
        'name' => $data['name'],
        'email' => $data['email']
    ]);
    
    $this->userRepository->save($user);
    
    $this->logger->info("User created", ['id' => $user->id]);
    
    return $user;
}
```

### 19. Чеклист перед коммитом

- [ ] Все формы имеют CSRF-токен
- [ ] Весь пользовательский ввод экранирован при выводе
- [ ] Используются подготовленные выражения для SQL
- [ ] Нет прямого доступа к файлам в storage/
- [ ] Логи не содержат чувствительных данных
- [ ] Валидация на сервере (не только на клиенте)
- [ ] Обработаны все возможные ошибки
- [ ] Код соответствует PSR-12
- [ ] Добавлены комментарии для сложной логики

### 20. Полезные команды

```bash
# Запуск сервера
composer start

# Или вручную
php -S localhost:8000 -t public public/router.php

# Установка зависимостей
composer install

# Генерация autoload
composer dump-autoload

# Проверка синтаксиса PHP
find src -name "*.php" -exec php -l {} \;

# Создание БД (SQLite)
touch storage/database.sqlite

# Просмотр логов
tail -f storage/logs/app.log
```

### 21. Рекомендации по развитию

1. **Добавьте тесты**: Используйте PHPUnit для модульного тестирования
2. **Кэширование**: Реализуйте кэширование запросов к БД
3. **Аутентификация**: Добавьте систему регистрации/входа
4. **API**: Создайте REST API endpoints
5. **Очереди**: Реализуйте систему фоновых задач
6. **Docker**: Контейнеризируйте приложение

### 22. Ресурсы для изучения

- [HTMX Documentation](https://htmx.org/docs/)
- [PHP The Right Way](https://phptherightway.com/)
- [PSR Standards](https://www.php-fig.org/psr/)
- [OWASP Security Guidelines](https://owasp.org/www-project-top-ten/)

---

**Важно**: При генерации кода всегда:

1. Проверяйте безопасность (CSRF, XSS, SQL Injection)
2. Используйте типизацию (declare(strict_types=1))
3. Следуйте принципу единственной ответственности
4. Добавляйте обработку ошибок
5. Документируйте сложные участки кода
