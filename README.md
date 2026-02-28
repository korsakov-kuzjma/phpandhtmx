# Mini Blog with PHP and HTMX

This is a mini blog application built with PHP and HTMX.

## Description

Mini Blog with PHP and HTMX is a lightweight blogging platform that uses PHP for backend processing and HTMX for dynamic frontend interactions without requiring complex JavaScript.

## Features

- PHP-based backend
- HTMX-powered dynamic UI interactions
- Lightweight and fast
- PSR-4 autoloading structure
- Built-in development server support

## Requirements

- PHP >= 8.1

## Installation

1. Clone the repository
2. Install dependencies with Composer:
   ```bash
   composer install
   ```

## Usage

To start the development server:

```bash
composer start
```

Or run directly with PHP:

```bash
php -S localhost:8000 -t public public/router.php
```

The application will be accessible at http://localhost:8000

## Project Structure

- `public/` - Public web root with index.php and router.php
- `src/` - PHP source code organized in subdirectories
- `templates/` - HTML templates
- `config/` - Configuration files
- `storage/` - Storage for cache, migrations, uploads, etc.