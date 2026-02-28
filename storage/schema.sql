-- Mini Blog Database Schema
-- Создание таблицы публикаций

CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Индекс для сортировки по дате (ускоряет запросы)
CREATE INDEX IF NOT EXISTS idx_posts_created_at ON posts(created_at DESC);
