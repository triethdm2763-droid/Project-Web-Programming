<?php
namespace App\Repositories;

use App\Core\BaseRepository;

class CategoryRepository extends BaseRepository
{
    public function findAll(): array
    {
        // ... (Giữ nguyên như source cũ của bạn) ...
        $sql = "SELECT ID, Name, Icon FROM categories ORDER BY Name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        // ... (Giữ nguyên như source cũ của bạn) ...
        $sql = "SELECT ID, Name, Icon FROM categories WHERE ID = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO categories (Name, Icon) VALUES (:name, :icon)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $data['name'] ?? '',
            'icon' => $data['icon'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE categories SET Name = :name WHERE ID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM categories WHERE ID = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}