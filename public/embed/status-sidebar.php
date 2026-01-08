<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/DeviceManager.php';

$deviceManager = new DeviceManager();
$stats = $deviceManager->getStatusStatistics();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compact Status</title>
    <style>
        body {
            margin: 0;
            padding: 8px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: white;
            font-size: 12px;
        }
        
        .compact-status {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 100px;
            max-width: 140px;
        }
        
        .sidebar-header {
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            color: #495057;
            padding: 4px 0;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 6px;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: #212529;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid #e9ecef;
            background: white;
        }
        
        .status-item:hover {
            border-color: #adb5bd;
            background: #f8f9fa;
            transform: translateX(2px);
        }
        
        .status-item:hover {
            opacity: 0.8;
            transform: scale(1.02);
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .status-count {
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-grow: 1;
        }
        
        /* Status-Farben */
        .normal .status-dot { background-color: #28a745; }
        .eingeschraenkt .status-dot { background-color: #ffc107; }
        .wartung .status-dot { background-color: #17a2b8; }
        .gestoert .status-dot { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="compact-status">
        <div class="sidebar-header">IT-Status</div>
        <a href="../index.php" class="status-item normal" target="_parent">
            <span class="status-dot"></span>
            <span class="status-count"><?= $stats[STATUS_NORMAL] ?></span>
            <span class="status-label">Normal</span>
        </a>
        
        <a href="../index.php" class="status-item eingeschraenkt" target="_parent">
            <span class="status-dot"></span>
            <span class="status-count"><?= $stats[STATUS_EINGESCHRAENKT] ?></span>
            <span class="status-label">Eingeschr.</span>
        </a>
        
        <a href="../index.php" class="status-item wartung" target="_parent">
            <span class="status-dot"></span>
            <span class="status-count"><?= $stats[STATUS_WARTUNG] ?></span>
            <span class="status-label">Wartung</span>
        </a>
        
        <a href="../index.php" class="status-item gestoert" target="_parent">
            <span class="status-dot"></span>
            <span class="status-count"><?= $stats[STATUS_GESTOERT] ?></span>
            <span class="status-label">Gestört</span>
        </a>
    </div>
</body>
</html>