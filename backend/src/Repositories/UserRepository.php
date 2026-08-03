<?php

namespace App\Repositories;

use App\Core\BaseRepository;
use PDO;

class UserRepository extends BaseRepository
{

    /**
     * Find a user by their Username.
     * 
     * @param string $username
     * @return array|null The user data array, or null if not found
     */
    public function findByUsername(string $username)
    {
        $stmt = $this->db->prepare("SELECT * FROM `users` WHERE `Username` = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find a user by their Email.
     * 
     * @param string $email
     * @return array|null The user data array, or null if not found
     */
    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM `users` WHERE `Email` = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find a user by their ID.
     * 
     * @param int $id
     * @return array|null The user data array, or null if not found
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT `ID`, `Username`, `Email`, `Phone`, `Fullname`, `Address`, `Avatar`, `Role`, `Status`, `created_at` FROM `users` WHERE `ID` = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Update user profile information.
     * 
     * @param int $id User ID
     * @param array $data Data containing phone, fullname, address, and optionally avatar
     * @return bool
     */
    public function updateProfile(int $id, array $data): bool
    {
        $sql = "UPDATE `users` SET 
                `Phone` = :phone, 
                `Fullname` = :fullname, 
                `Address` = :address" .
            (isset($data['avatar']) ? ", `Avatar` = :avatar" : "") .
            " WHERE `ID` = :id";

        $params = [
            'phone' => $data['phone'] ?? null,
            'fullname' => $data['fullname'] ?? null,
            'address' => $data['address'] ?? null,
            'id' => $id
        ];
        if (isset($data['avatar'])) {
            $params['avatar'] = $data['avatar'];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Create and insert a new user into the database.
     * 
     * @param string $username
     * @param string $email
     * @param string $passwordHash Pre-hashed password
     * @param string|null $phone
     * @param string $role Default 'user'
     * @return int The auto-incremented primary key ID of the created user
     */
    public function create(string $username, string $email, string $passwordHash, ?string $phone, string $role = 'user'): int
    {
        $sql = "INSERT INTO `users` (`Username`, `Password`, `Email`, `Phone`, `Role`, `Status`) 
                VALUES (:username, :password, :email, :phone, :role, 'active')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'username' => $username,
            'password' => $passwordHash,
            'email'    => $email,
            'phone'    => $phone,
            'role'     => $role
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update user password
     * 
     * @param int $userId
     * @param string $passwordHash
     * @return bool
     */
    public function updatePassword(int $userId, string $passwordHash): bool
    {
        $sql = "UPDATE `users` SET `Password` = :password WHERE `ID` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'password' => $passwordHash,
            'id'       => $userId
        ]);
    }

    public function findAll()
    {
        $sql = "
            SELECT
                ID,
                Username,
                Email,
                Phone,
                Role,
                Status,
                Created_at
            FROM users
            ORDER BY Created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Lock / unlock a user account (admin action).
     *
     * @param int $id
     * @param string $status 'active' | 'banned'
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool {
        $sql = "UPDATE `users` SET `Status` = :status WHERE `ID` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'id'     => $id
        ]);
    }

    /**
     * Lấy danh sách user kèm thông tin ví (số dư).
     * Lưu ý: bảng `users` hiện chưa có cột số dư riêng, nên Balance được trả về mặc định = 0
     * cho mỗi user. Khi backend bổ sung cột Balance (hoặc bảng wallets riêng), hàm này cần
     * được cập nhật lại để lấy số dư thật.
     *
     * @return array
     */
    public function getWallets()
    {
        $sql = "
            SELECT
                ID,
                Username,
                Email,
                Role,
                0 AS Balance
            FROM users
            ORDER BY Created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
