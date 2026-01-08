<?php
// Konfigurationsdatei
define('DB_PATH', __DIR__ . '/database.db');
define('SESSION_NAME', 'device_management_session');
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// Status-Definitionen
define('STATUS_NORMAL', 'normal');
define('STATUS_EINGESCHRAENKT', 'eingeschraenkt');
define('STATUS_GESTOERT', 'gestoert');
define('STATUS_WARTUNG', 'wartung');

// Status-Prioritäten (höher = kritischer)
$status_priorities = [
    STATUS_NORMAL => 1,
    STATUS_EINGESCHRAENKT => 2,
    STATUS_WARTUNG => 3,
    STATUS_GESTOERT => 4
];

// Status-Farben
$status_colors = [
    STATUS_NORMAL => '#28a745',
    STATUS_EINGESCHRAENKT => '#ffc107',
    STATUS_WARTUNG => '#17a2b8',
    STATUS_GESTOERT => '#dc3545'
];

// Status-Labels
$status_labels = [
    STATUS_NORMAL => 'Normal',
    STATUS_EINGESCHRAENKT => 'Eingeschränkt',
    STATUS_WARTUNG => 'Wartung',
    STATUS_GESTOERT => 'Gestört'
];

session_name(SESSION_NAME);
session_start();