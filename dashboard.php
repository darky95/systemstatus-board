<?php
require_once 'config.php';
require_once 'classes/Auth.php';
require_once 'classes/DeviceManager.php';
require_once 'classes/Security.php';

$auth = new Auth();
$auth->requireLogin();

$deviceManager = new DeviceManager();
$stats = $deviceManager->getStatusStatistics();

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="dashboard">
    <h1>Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total'] ?></div>
            <div class="stat-label">Gesamt Geräte</div>
        </div>
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
    
    <div class="quick-actions">
        <h2>Schnellaktionen</h2>
        <div class="action-buttons">
            <a href="devices.php?action=create" class="btn btn-primary">Neues Gerät erstellen</a>
            <a href="devices.php" class="btn btn-secondary">Geräte verwalten</a>
            <a href="public/index.php" class="btn btn-secondary" target="_blank">Öffentliche Ansicht</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>