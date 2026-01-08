<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/DeviceManager.php';
require_once __DIR__ . '/../classes/Security.php';

$deviceManager = new DeviceManager();
$stats = $deviceManager->getStatusStatistics();

// Funktion zur Berechnung der Hierarchie-Ebene
function getDeviceLevel($deviceId, $devices, $level = 0) {
    foreach ($devices as $device) {
        if ($device['id'] == $deviceId) {
            if ($device['parent_id'] === null) {
                return $level;
            }
            return getDeviceLevel($device['parent_id'], $devices, $level + 1);
        }
    }
    return $level;
}

// Funktion zur hierarchischen Sortierung
function sortDevicesHierarchical($devices) {
    $sorted = [];
    $deviceMap = [];
    
    // Erstelle eine Map für schnellen Zugriff
    foreach ($devices as $device) {
        $deviceMap[$device['id']] = $device;
    }
    
    // Sortiere: Eltern zuerst, dann Kinder
    function addDeviceAndChildren($parentId, $devices, $deviceMap, &$sorted) {
        foreach ($devices as $device) {
            if ($device['parent_id'] == $parentId) {
                $device['level'] = getDeviceLevel($device['id'], $devices);
                $sorted[] = $device;
                addDeviceAndChildren($device['id'], $devices, $deviceMap, $sorted);
            }
        }
    }
    
    addDeviceAndChildren(null, $devices, $deviceMap, $sorted);
    
    return $sorted;
}

$allDevices = $deviceManager->getAllDevices();
$sortedDevices = sortDevicesHierarchical($allDevices);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerätestatus - Öffentliche Ansicht</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/public.css">
</head>
<body class="public-page">
    <header class="public-header">
        <div class="container">
            <h1>Systemstatus IT</h1>
            <p class="last-update">Letzte Aktualisierung: <?= date('d.m.Y H:i') ?></p>
        </div>
    </header>
    
    <main class="container">
        <div class="public-stats">
            <div class="stat-card status-normal">
                <div class="stat-value"><?= $stats[STATUS_NORMAL] ?></div>
                <div class="stat-label">Normal</div>
            </div>
            <div class="stat-card status-eingeschraenkt">
                <div class="stat-value"><?= $stats[STATUS_EINGESCHRAENKT] ?></div>
                <div class="stat-label">Eingeschränkt</div>
            </div>
            <div class="stat-card status-wartung">
                <div class="stat-value"><?= $stats[STATUS_WARTUNG] ?></div>
                <div class="stat-label">Wartung</div>
            </div>
            <div class="stat-card status-gestoert">
                <div class="stat-value"><?= $stats[STATUS_GESTOERT] ?></div>
                <div class="stat-label">Gestört</div>
            </div>
        </div>
        
        <div class="device-table-view">
            <h2>Geräteübersicht</h2>
            <?php if (empty($sortedDevices)): ?>
                <p class="no-devices">Keine Geräte vorhanden.</p>
            <?php else: ?>
                <table class="data-table public-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Notiz</th>
                            <th>Zuletzt aktualisiert</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sortedDevices as $device): ?>
                            <tr>
                                <td class="device-name-cell" style="padding-left: <?= ($device['level'] ?? 0) * 30 + 15 ?>px;">
                                    <?php if (($device['level'] ?? 0) > 0): ?>
                                        <span class="hierarchy-indicator">└─</span>
                                    <?php endif; ?>
                                    <?= Security::escape($device['name']) ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $device['status'] ?>">
                                        <?= Security::escape($status_labels[$device['status']] ?? $device['status']) ?>
                                    </span>
                                </td>
                                <td><?= Security::escape($device['note'] ?? '') ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($device['updated_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="status-legend">
            <h2>Statuslegende</h2>
            <div class="legend-items">
                <div class="legend-item">
                    <span class="legend-color" style="background-color: <?= $status_colors[STATUS_NORMAL] ?>"></span>
                    <span class="legend-label">Normal - Alle Systeme funktionieren einwandfrei</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: <?= $status_colors[STATUS_EINGESCHRAENKT] ?>"></span>
                    <span class="legend-label">Eingeschränkt - Teilweise Einschränkungen</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: <?= $status_colors[STATUS_WARTUNG] ?>"></span>
                    <span class="legend-label">Wartung - Geplante Wartungsarbeiten</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: <?= $status_colors[STATUS_GESTOERT] ?>"></span>
                    <span class="legend-label">Gestört - Systemstörung</span>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="public-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Geräteverwaltungssystem</p>
        </div>
    </footer>
    
    <script>
        // Auto-Refresh alle 60 Sekunden
        setTimeout(function() {
            location.reload();
        }, 60000);
    </script>
</body>
</html>