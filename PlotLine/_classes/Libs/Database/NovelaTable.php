<?php

namespace Libs\Database;

use PDOException;
use PDO;

class NovelaTable
{
    private $db;

    public function __construct(MySQL $mysql)
    {
        $this->db = $mysql->connect();
    }

    public function getWorksCount($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS count FROM novels WHERE users_id = :id");
            $stmt->execute(['id' => $userId]);
            $result = $stmt->fetch();
            return $result->count ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getByUserId($userId)
    {
        try {
            // We use LEFT JOIN to get the genres associated with the user's novels
            $sql = "SELECT n.*, GROUP_CONCAT(g.name SEPARATOR ', ') as genres
                    FROM novels n
                    LEFT JOIN novel_genres ng ON n.id = ng.novel_id
                    LEFT JOIN genres g ON ng.genre_id = g.id
                    WHERE n.users_id = :id 
                    GROUP BY n.id
                    ORDER BY n.created_at DESC";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
public function searchNovels($term)
{
    try {
        $sql = "SELECT 
                    n.id, 
                    n.title, 
                    n.status, 
                    n.cover_image, 
                    u.name AS author_name
                FROM novels n
                JOIN users u ON n.users_id = u.id
                WHERE n.status != 'draft' 
                AND (n.title LIKE :term OR u.name LIKE :term)
                ORDER BY n.created_at DESC
                LIMIT 8";

        $stmt = $this->db->prepare($sql);
        $searchTerm = "%$term%";
        $stmt->execute(['term' => $searchTerm]);
        
        // Use FETCH_ASSOC to ensure the keys are exactly as named in the SQL
        return $stmt->fetchAll(\PDO::FETCH_ASSOC); 
    } catch (\PDOException $e) {
        return [];
    }
}
}