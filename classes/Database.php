<?php
require_once __DIR__ . '/../config.php';

class Database {
    private $db;
    
    public function __construct() {
        $this->db = new PDO('sqlite:' . DB_PATH);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->initDatabase();
    }
    
    private function initDatabase() {
        // Geräte-Tabelle
        $this->db->exec("CREATE TABLE IF NOT EXISTS devices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            device_key TEXT UNIQUE NOT NULL,
            status TEXT NOT NULL DEFAULT 'normal',
            note TEXT,
            parent_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES devices(id) ON DELETE CASCADE
        )");
        
        // Benutzer-Tabelle
        $this->db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            is_admin INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Aktivitäten-Tabelle
        $this->db->exec("CREATE TABLE IF NOT EXISTS activities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            action TEXT NOT NULL,
            table_name TEXT NOT NULL,
            record_id INTEGER,
            old_data TEXT,
            new_data TEXT,
            user_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
        
        // API-Keys-Tabelle
        $this->db->exec("CREATE TABLE IF NOT EXISTS api_keys (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            api_key TEXT UNIQUE NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_active INTEGER DEFAULT 1
        )");
        
        // Index erstellen
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_devices_parent ON devices(parent_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_devices_key ON devices(device_key)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_activities_user ON activities(user_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_activities_created ON activities(created_at)");
        
        // Standard-Admin-Benutzer erstellen (falls nicht vorhanden)
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $password = password_hash('admin', PASSWORD_BCRYPT);
            $this->db->exec("INSERT INTO users (username, password, is_admin) VALUES ('admin', '$password', 1)");
        }
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function execute($sql, $params = []) {
        return $this->query($sql, $params);
    }
}