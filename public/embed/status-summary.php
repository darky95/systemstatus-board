<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/DeviceManager.php';

$deviceManager = new DeviceManager();
$stats = $deviceManager->getStatusStatistics();

// Bestimme den Gesamtstatus
$criticalCount = $stats[STATUS_GESTOERT];
$restrictedCount = $stats[STATUS_EINGESCHRAENKT];
$maintenanceCount = $stats[STATUS_WARTUNG];
$normalCount = $stats[STATUS_NORMAL];

// Status-Priorisierung: Gestört > Eingeschränkt > Wartung > Normal
if ($criticalCount > 0) {
    $displayStatus = 'gestoert';
    $message = $criticalCount . ' Störung' . ($criticalCount > 1 ? 'en' : '');
    $color = '#dc3545';
} elseif ($restrictedCount > 0) {
    $displayStatus = 'eingeschraenkt';
    $message = $restrictedCount . ' Einschränkung' . ($restrictedCount > 1 ? 'en' : '');
    $color = '#ffc107';
} elseif ($maintenanceCount > 0) {
    $displayStatus = 'wartung';
    $message = 'Wartungsarbeiten';
    $color = '#17a2b8';
} else {
    $displayStatus = 'normal';
    $message = 'Keine Störung';
    $color = '#28a745';
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Summary</title>
    <style>
        body {
            margin: 0;
            padding: 12px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: white;
            font-size: 14px;
        }
        
        .status-summary {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 6px;
            background: white;
            border: 2px solid <?= $color ?>;
            min-width: 200px;
            max-width: 300px;
            transition: all 0.3s ease;
        }
        
        .status-summary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .status-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: <?= $color ?>;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 12px;
        }
        
        .status-text {
            font-weight: 600;
            color: #212529;
            flex-grow: 1;
        }
        
        .status-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .status-link:hover .status-text {
            text-decoration: underline;
        }
        
        /* Spezifische Anpassungen für jeden Status */
        <?php if ($displayStatus === 'eingeschraenkt'): ?>
        .status-text {
            color: #212529;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <a href="../index.php" class="status-link" target="_parent">
        <div class="status-summary">
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
            <div class="status-text"><?= $message ?></div>
        </div>
    </a>
</body>
</html>