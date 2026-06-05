<?php
namespace App\Repositories;

use App\Core\BaseRepository;

class CategoryRepository extends BaseRepository {

    /**
     * Return all categories
     * @return array
     */
    public function findAll(): array {
        $sql = "SELECT ID, Name FROM categories ORDER BY Name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a category by id
     * @param int $id
     * @return array|null
     */
    public function findById(int $id) {
        $sql = "SELECT ID, Name FROM categories WHERE ID = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
