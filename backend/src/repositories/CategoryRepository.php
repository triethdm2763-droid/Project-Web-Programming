<?php

namespace App\Repositories;

use App\Core\BaseRepository;

class CategoryRepository extends BaseRepository
{
    /**
     * Lấy toàn bộ danh mục
     */
    public function findAll(): array
    {
        $sql = "
            SELECT
                ID,
                Name,
                Icon
            FROM categories
            ORDER BY Name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Lấy danh mục theo ID
     */
    public function findById(int $id)
    {
        $sql = "
            SELECT
                ID,
                Name,
                Icon
            FROM categories
            WHERE ID = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }
}