<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Security.php';

$auth = new Auth();
$auth->requireLogin();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? Security::escape($pageTitle) : 'Dashboard' ?> - Systemstatus IT</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="dashboard.php">Systemstatus IT</a>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
                <a href="devices.php" class="<?= basename($_SERVER['PHP_SELF']) == 'devices.php' ? 'active' : '' ?>">Geräte</a>
                <?php if ($auth->isAdmin()): ?>
                    <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">Benutzer</a>
                    <a href="api_keys.php" class="<?= basename($_SERVER['PHP_SELF']) == 'api_keys.php' ? 'active' : '' ?>">API-Keys</a>
                <?php endif; ?>
                <a href="activities.php" class="<?= basename($_SERVER['PHP_SELF']) == 'activities.php' ? 'active' : '' ?>">Aktivitäten</a>
                <a href="logout.php" class="logout">Abmelden (<?= Security::escape($auth->getUsername()) ?>)</a>
            </div>
            <button class="navbar-toggle" onclick="toggleNavbar()">☰</button>
        </div>
    </nav>
    <main class="container">