<?php
// Weiterleitung zur Login-Seite oder Dashboard
require_once 'config.php';
require_once 'classes/Auth.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;