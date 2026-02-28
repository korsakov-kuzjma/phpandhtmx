<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Post;
use PDO;

/**
 * Класс PostRepository - паттерн Repository
 * 
 * Отвечает за всю работу с таблицей posts в базе данных.
 * Инкапсулирует SQL-запросы и логику работы с данными.
 * 
 * В WordPress аналог - класс WP_Query или методы $wpdb.
 * 
 * ВАЖНО: Все запросы используют подготовленные выражения (prepared statements)
 * с плейсхолдерами (:title, :content). Это защищает от SQL Injection!
 */
class PostRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Получить все публикации (отсортированные по дате)
     * 
     * @return Post[]
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM posts 
            ORDER BY created_at DESC
        ");
        
        $posts = [];
        while ($row = $stmt->fetch()) {
            $posts[] = new Post($row);
        }
        
        return $posts;
    }

    /**
     * Получить одну публикацию по ID
     * 
     * @return Post|null
     */
    public function getById(int $id): ?Post
    {
        // Подготовленный запрос с плейсхолдером :id
        // Это защищает от SQL Injection!
        $stmt = $this->db->prepare("
            SELECT * FROM posts WHERE id = :id
        ");
        
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? new Post($row) : null;
    }

    /**
     * Создать новую публикацию
     * 
     * @return Post
     */
    public function create(Post $post): Post
    {
        $stmt = $this->db->prepare("
            INSERT INTO posts (title, content, created_at, updated_at)
            VALUES (:title, :content, :created_at, :updated_at)
        ");

        $now = date('Y-m-d H:i:s');
        
        $stmt->execute([
            'title' => $post->title,
            'content' => $post->content,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Получаем ID созданной записи
        $post->id = (int) $this->db->lastInsertId();
        $post->createdAt = $now;
        $post->updatedAt = $now;

        return $post;
    }

    /**
     * Обновить публикацию
     * 
     * @return bool
     */
    public function update(Post $post): bool
    {
        $stmt = $this->db->prepare("
            UPDATE posts 
            SET title = :title, 
                content = :content, 
                updated_at = :updated_at
            WHERE id = :id
        ");

        $stmt->execute([
            'title' => $post->title,
            'content' => $post->content,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $post->id,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Удалить публикацию
     * 
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Подсчитать количество публикаций
     * 
     * @return int
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM posts");
        return (int) $stmt->fetchColumn();
    }
}
