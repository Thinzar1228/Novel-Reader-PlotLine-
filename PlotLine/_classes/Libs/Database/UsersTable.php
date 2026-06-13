<?php

namespace Libs\Database;

use PDOException;

class UsersTable
{
    private $db;

    public function __construct(MySQL $mysql)
    {
        $this->db = $mysql->connect();
    }

    // Register
    public function insert($data)
    {
        try {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            $statement = $this->db->prepare(
                "INSERT INTO users (name, email, password, created_at) VALUES (:name, :email,:password, NOW())"
            );

            $statement->execute($data);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }

    // Login
    public function find($email, $password)
{
    try {
        $statement = $this->db->prepare("SELECT * FROM users WHERE email=:email");
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if(!$user) {
            return "email_not_found"; // Specific error
        }

        if(password_verify($password, $user->password)) {
            return $user; // Success
        }

        return "wrong_password"; // Specific error

    } catch (PDOException $e) {
        echo $e->getMessage();
        exit();
    }
}

    public function checkDuplicate($name, $email)
{
    $stmt = $this->db->prepare("
        SELECT
            SUM(name = :name) AS name_exists,
            SUM(email = :email) AS email_exists
        FROM users
    ");

    $stmt->execute([
        'name' => $name,
        'email' => $email
    ]);

    return $stmt->fetch();
}

public function updateProfile($id, $data) {
    try {
        $sql = "UPDATE users SET bio = :bio, profile_image = :profile_image, cover_image = :cover_image WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Inside class UsersTable
public function getFollowerCount($userId) {
    // Counts how many people are following THIS user
    $stmt = $this->db->prepare("SELECT COUNT(*) AS count FROM follows WHERE following_id = :id");
    $stmt->execute(['id' => $userId]);
    $result = $stmt->fetch();
    return $result->count ?? 0;
}

public function getFollowingCount($userId) {
    // Counts how many people THIS user is following
    $stmt = $this->db->prepare("SELECT COUNT(*) AS count FROM follows WHERE follower_id = :id");
    $stmt->execute(['id' => $userId]);
    $result = $stmt->fetch();
    return $result->count ?? 0;
}

public function getRecentFollowers($userId, $limit = 5) {
    try {
        // We join with the users table to get the follower's details
        $sql = "SELECT u.id, u.name, u.profile_image 
                FROM follows f
                JOIN users u ON f.follower_id = u.id
                WHERE f.following_id = :id
                ORDER BY f.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch (\PDOException $e) {
        return [];
    }
}

public function findById($id) {
    $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

public function isFollowing($follower_id, $following_id) {
    // Check if a specific link exists in the follows table
    $stmt = $this->db->prepare("SELECT id FROM follows WHERE follower_id = :f1 AND following_id = :f2");
    $stmt->execute(['f1' => $follower_id, 'f2' => $following_id]);
    return $stmt->fetch() ? true : false;
}

public function search($term, $auth_id = 0)
{
    $stmt = $this->db->prepare("
        SELECT id, name, profile_image, bio 
        FROM users 
        WHERE name LIKE :term 
        AND id != :auth_id
        LIMIT 5
    ");
    $stmt->execute([
        'term' => "%$term%",
        'auth_id' => $auth_id
    ]);
    return $stmt->fetchAll();
}

public function updatePassword($id, $hashedPassword)
{
    try {
        $query = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'password' => $hashedPassword,
            'id' => $id
        ]);
        return $stmt->rowCount();
    } catch (\PDOException $e) {
        return false;
    }
}

public function getFollowers($user_id) {
    // Joins the 'follows' table with 'users' to get people following $user_id
    $stmt = $this->db->prepare("
        SELECT u.* FROM users u
        JOIN follows f ON u.id = f.follower_id 
        WHERE f.following_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

public function getFollowing($user_id) {
    // Joins the 'follows' table with 'users' to get people $user_id follows
    $stmt = $this->db->prepare("
        SELECT u.* FROM users u
        JOIN follows f ON u.id = f.following_id 
        WHERE f.follower_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}
}