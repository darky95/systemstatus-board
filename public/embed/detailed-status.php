<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/DeviceManager.php';

$deviceManager = new DeviceManager();
$stats = $deviceManager->getStatusStatistics();
$allDevices = $deviceManager->getAllDevices();

// Filtere Geräte mit nicht-normalem Status
$nonNormalDevices = array_filter($allDevices, function($device) {
    return $device['status'] !== STATUS_NORMAL;
});

// Sortiere nach Status-Priorität (kritischste zuerst)
usort($nonNormalDevices, function($a, $b) {
    global $status_priorities;
    return $status_priorities[$b['status']] <=> $status_priorities[$a['status']];
});

// Bestimme den Gesamtstatus für die Hauptmeldung
$criticalCount = $stats[STATUS_GESTOERT];
$restrictedCount = $stats[STATUS_EINGESCHRAENKT];
$maintenanceCount = $stats[STATUS_WARTUNG];

if ($criticalCount > 0) {
    $displayStatus = 'gestoert';
    $mainMessage = $criticalCount . ' Störung' . ($criticalCount > 1 ? 'en' : '');
    $color = '#dc3545';
} elseif ($restrictedCount > 0) {
    $displayStatus = 'eingeschraenkt';
    $mainMessage = $restrictedCount . ' Einschränkung' . ($restrictedCount > 1 ? 'en' : '');
    $color = '#ffc107';
} elseif ($maintenanceCount > 0) {
    $displayStatus = 'wartung';
    $mainMessage = 'Wartungsarbeiten';
    $color = '#17a2b8';
} else {
    $displayStatus = 'normal';
    $mainMessage = 'Keine Störung';
    $color = '#28a745';
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Status</title>
    <style>
        body {
            margin: 0;
            padding: 12px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: white;
            font-size: 14px;
        }
        
        .detailed-status {
            background: white;
            border-radius: 8px;
            border: 2px solid <?= $color ?>;
            min-width: 250px;
            max-width: 350px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .status-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background-color: <?= $color ?>15;
        }
        
        .status-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: <?= $color ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 14px;
        }
        
        .status-main-text {
            font-weight: 600;
            color: #212529;
            font-size: 16px;
        }
        
        .devices-list {
            padding: 12px 16px;
        }
        
        .device-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .device-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .device-note {
            font-size: 12px;
            color: #6c757d;
            margin-left: 22px;
            font-style: italic;
            line-height: 1.3;
        }
        
        .device-item:last-child {
            border-bottom: none;
        }
        
        .device-status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        
        .device-name {
            font-size: 13px;
            color: #495057;
            flex-grow: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .device-status-label {
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Status-Farben für Geräte */
        .status-gestoert .device-status-dot { background-color: #dc3545; }
        .status-eingeschraenkt .device-status-dot { background-color: #ffc107; }
        .status-wartung .device-status-dot { background-color: #17a2b8; }
        
        /* Textfarbe für gelben Status */
        .status-eingeschraenkt .device-status-label { color: #212529; }
        
        .status-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .status-link:hover .detailed-status {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        
        .no-devices {
            padding: 16px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <a href="../index.php" class="status-link" target="_parent">
        <div class="detailed-status">
            <div class="status-header">
                <div class="status-icon">
                    <?php 
                    switch($displayStatus) {
                        case 'normal': echo '✓'; break;
                        case 'eingeschraenkt': echo '!'; break;
                        case 'wartung': echo '🔧'; break;
                        case 'gestoert': echo '⚠'; break;
                    }
                    ?>
                </div>
                <div class="status-main-text"><?= $mainMessage ?></div>
            </div>
            
            <?php if (!empty($nonNormalDevices)): ?>
                <div class="devices-list">
                    <?php foreach (array_slice($nonNormalDevices, 0, 5) as $device): ?>
                        <div class="device-item status-<?= $device['status'] ?>">
                            <div class="device-header">
                                <div class="device-status-dot"></div>
                                <div class="device-name"><?= htmlspecialchars($device['name']) ?></div>
                                <div class="device-status-label">
                                    <?= $status_labels[$device['status']] ?? $device['status'] ?>
                                </div>
                            </div>
                            <?php if (!empty($device['note'])): ?>
                                <div class="device-note">
                                    <?= htmlspecialchars($device['note']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($nonNormalDevices) > 5): ?>
                        <div class="device-item">
                            <div class="device-name" style="font-style: italic; color: #6c757d;">
                                ... und <?= count($nonNormalDevices) - 5 ?> weitere
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif ($displayStatus === 'normal'): ?>
                <div class="no-devices">Alle Systeme funktionieren ordnungsgemäß</div>
            <?php endif; ?>
        </div>
    </a>
</body>
</html>