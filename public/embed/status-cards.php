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
    <title>Status Cards</title>
    <style>
        body {
            margin: 0;
            padding: 10px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: transparent;
        }
        
        .status-cards-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 300px;
        }
        
        .status-card {
            padding: 12px 16px;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            color: white;
            text-decoration: none;
            display: block;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .status-card.normal {
            background-color: #28a745;
        }
        
        .status-card.eingeschraenkt {
            background-color: #ffc107;
            color: #212529;
        }
        
        .status-card.wartung {
            background-color: #17a2b8;
        }
        
        .status-card.gestoert {
            background-color: #dc3545;
        }
        
        .card-count {
            font-size: 24px;
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
        }
        
        .card-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="status-cards-container">
        <a href="../index.php" class="status-card normal" target="_parent">
            <span class="card-count"><?= $stats[STATUS_NORMAL] ?></span>
            <span class="card-label">Normal</span>
        </a>
        
        <a href="../index.php" class="status-card eingeschraenkt" target="_parent">
            <span class="card-count"><?= $stats[STATUS_EINGESCHRAENKT] ?></span>
            <span class="card-label">Eingeschränkt</span>
        </a>
        
        <a href="../index.php" class="status-card wartung" target="_parent">
            <span class="card-count"><?= $stats[STATUS_WARTUNG] ?></span>
            <span class="card-label">Wartung</span>
        </a>
        
        <a href="../index.php" class="status-card gestoert" target="_parent">
            <span class="card-count"><?= $stats[STATUS_GESTOERT] ?></span>
            <span class="card-label">Gestört</span>
        </a>
    </div>
</body>
</html>