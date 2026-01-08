<?php
require_once __DIR__ . '/Database.php';

class ActivityLogger {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function log($action, $tableName, $recordId = null, $oldData = null, $newData = null, $userId = null) {
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        
        $this->db->execute(
            "INSERT INTO activities (action, table_name, record_id, old_data, new_data, user_id) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $action,
                $tableName,
                $recordId,
                $oldData ? json_encode($oldData) : null,
                $newData ? json_encode($newData) : null,
                $userId
            ]
        );
    }
    
    public function getActivities($limit = 100) {
        return $this->db->fetchAll(
            "SELECT a.*, u.username 
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
}