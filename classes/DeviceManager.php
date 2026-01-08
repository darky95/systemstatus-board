<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/../config.php';

class DeviceManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getAllDevices() {
        return $this->db->fetchAll(
            "SELECT d.*, 
                    (SELECT COUNT(*) FROM devices WHERE parent_id = d.id) as child_count
             FROM devices d
             ORDER BY d.name"
        );
    }
    
    public function getDeviceTree($parentId = null) {
        $devices = $this->db->fetchAll(
            "SELECT * FROM devices WHERE parent_id " . ($parentId === null ? "IS NULL" : "= ?") . " ORDER BY name",
            $parentId === null ? [] : [$parentId]
        );
        
        foreach ($devices as &$device) {
            $device['children'] = $this->getDeviceTree($device['id']);
        }
        
        return $devices;
    }
    
    public function getDeviceById($id) {
        return $this->db->fetchOne("SELECT * FROM devices WHERE id = ?", [$id]);
    }
    
    public function getDeviceByKey($key) {
        return $this->db->fetchOne("SELECT * FROM devices WHERE device_key = ?", [$key]);
    }
    
    public function createDevice($name, $status, $note = '', $parentId = null) {
        $deviceKey = Security::generateDeviceKey();
        
        $this->db->execute(
            "INSERT INTO devices (name, device_key, status, note, parent_id) VALUES (?, ?, ?, ?, ?)",
            [$name, $deviceKey, $status, $note, $parentId]
        );
        
        $deviceId = $this->db->getConnection()->lastInsertId();
        $this->updateParentStatus($parentId);
        
        return $deviceId;
    }
    
    public function updateDevice($id, $name, $status, $note = '', $parentId = null) {
        $oldDevice = $this->getDeviceById($id);
        
        $this->db->execute(
            "UPDATE devices SET name = ?, status = ?, note = ?, parent_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$name, $status, $note, $parentId, $id]
        );
        
        $this->updateParentStatus($parentId);
        if ($oldDevice && $oldDevice['parent_id'] != $parentId) {
            $this->updateParentStatus($oldDevice['parent_id']);
        }
        
        return true;
    }
    
    public function updateDeviceStatus($id, $status, $note = '') {
        $device = $this->getDeviceById($id);
        if (!$device) return false;
        
        $this->db->execute(
            "UPDATE devices SET status = ?, note = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$status, $note, $id]
        );
        
        $this->updateParentStatus($device['parent_id']);
        
        return true;
    }
    
    public function updateDeviceStatusByKey($deviceKey, $status, $note = '') {
        $device = $this->getDeviceByKey($deviceKey);
        if (!$device) return false;
        
        return $this->updateDeviceStatus($device['id'], $status, $note);
    }
    
    public function deleteDevice($id) {
        $device = $this->getDeviceById($id);
        if (!$device) return false;
        
        $parentId = $device['parent_id'];
        
        $this->db->execute("DELETE FROM devices WHERE id = ?", [$id]);
        
        $this->updateParentStatus($parentId);
        
        return true;
    }
    
    public function updateParentStatus($parentId) {
        if ($parentId === null) return;
        
        global $status_priorities;
        
        // Hole alle Kind-Geräte
        $children = $this->db->fetchAll(
            "SELECT status FROM devices WHERE parent_id = ?",
            [$parentId]
        );
        
        if (empty($children)) {
            // Keine Kinder mehr, Status auf normal setzen
            $this->db->execute(
                "UPDATE devices SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [STATUS_NORMAL, $parentId]
            );
            return;
        }
        
        // Finde den kritischsten Status
        $maxPriority = 0;
        $criticalStatus = STATUS_NORMAL;
        
        foreach ($children as $child) {
            $priority = $status_priorities[$child['status']] ?? 1;
            if ($priority > $maxPriority) {
                $maxPriority = $priority;
                $criticalStatus = $child['status'];
            }
        }
        
        // Aktualisiere Eltern-Gerät
        $this->db->execute(
            "UPDATE devices SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$criticalStatus, $parentId]
        );
        
        // Rekursiv für Großeltern
        $parent = $this->getDeviceById($parentId);
        if ($parent && $parent['parent_id']) {
            $this->updateParentStatus($parent['parent_id']);
        }
    }
    
    public function getStatusStatistics() {
        $stats = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM devices GROUP BY status"
        );
        
        $result = [
            STATUS_NORMAL => 0,
            STATUS_EINGESCHRAENKT => 0,
            STATUS_WARTUNG => 0,
            STATUS_GESTOERT => 0
        ];
        
        foreach ($stats as $stat) {
            $result[$stat['status']] = (int)$stat['count'];
        }
        
        $result['total'] = array_sum($result);
        
        return $result;
    }
    
    public function bulkUpdateStatus($deviceIds, $status, $note = '') {
        foreach ($deviceIds as $deviceId) {
            $device = $this->getDeviceById($deviceId);
            if ($device) {
                $this->updateDeviceStatus($deviceId, $status, $note);
            }
        }
        return true;
    }
}