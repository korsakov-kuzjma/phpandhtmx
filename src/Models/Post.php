<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Класс Post - модель публикации
 * 
 * Представляет одну запись из таблицы posts.
 * Использует Data Mapping паттерн - преобразует данные из БД в объект.
 * 
 * В WordPress аналог - класс WP_Post.
 */
class Post
{
    public int $id;
    public string $title;
    public string $content;
    public string $createdAt;
    public string $updatedAt;

    /**
     * @param array $data Данные из БД или формы
     */
    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->title = $data['title'] ?? '';
        $this->content = $data['content'] ?? '';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    /**
     * Преобразование объекта в массив для сохранения в БД
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Проверка, является ли пост новым (не сохранен в БД)
     */
    public function isNew(): bool
    {
        return $this->id === 0;
    }
}
