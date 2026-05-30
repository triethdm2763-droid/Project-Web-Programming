<?php
namespace App\Repositories;

use App\Core\BaseRepository;
use PDO;

class UserRepository extends BaseRepository {

    /**
     * Find a user by their Username.
     * 
     * @param string $username
     * @return array|null The user data array, or null if not found
     */
    public function findByUsername(string $username) {
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
    public function findByEmail(string $email) {
        $stmt = $this->db->prepare("SELECT * FROM `users` WHERE `Email` = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
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
    public function create(string $username, string $email, string $passwordHash, ?string $phone, string $role = 'user'): int {
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
}
