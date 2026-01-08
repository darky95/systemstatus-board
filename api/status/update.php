<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/DeviceManager.php';
require_once __DIR__ . '/../../classes/Security.php';
require_once __DIR__ . '/../../classes/ActivityLogger.php';
require_once __DIR__ . '/../../classes/Database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Nur POST-Requests erlaubt';
    echo json_encode($response);
    exit;
}

// JSON-Request unterstützen
$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    $_POST = array_merge($_POST, $input);
}

$apiKey = $_POST['api_key'] ?? $_GET['api_key'] ?? '';
$deviceKey = $_POST['device_key'] ?? $_GET['device_key'] ?? '';
$status = $_POST['status'] ?? $_GET['status'] ?? '';
$note = $_POST['note'] ?? $_GET['note'] ?? '';

// API-Key validieren
if (empty($apiKey) || !Security::validateApiKey($apiKey)) {
    $response['message'] = 'Ungültiger oder fehlender API-Key';
    echo json_encode($response);
    exit;
}

// Parameter validieren
if (empty($deviceKey)) {
    $response['message'] = 'device_key ist erforderlich';
    echo json_encode($response);
    exit;
}

if (empty($status) || !Security::validateStatus($status)) {
    $response['message'] = 'Ungültiger Status. Erlaubt: normal, eingeschraenkt, wartung, gestoert';
    echo json_encode($response);
    exit;
}

// Status aktualisieren
$deviceManager = new DeviceManager();
$device = $deviceManager->getDeviceByKey($deviceKey);

if (!$device) {
    $response['message'] = 'Gerät nicht gefunden';
    echo json_encode($response);
    exit;
}

$oldStatus = $device['status'];
$deviceManager->updateDeviceStatusByKey($deviceKey, $status, $note);

// Aktivität protokollieren
$db = new Database();
$apiKeyData = $db->fetchOne("SELECT id FROM api_keys WHERE api_key = ?", [$apiKey]);
$activityLogger = new ActivityLogger();
$activityLogger->log(
    'status_updated_api',
    'devices',
    $device['id'],
    ['status' => $oldStatus],
    ['status' => $status, 'note' => $note],
    null
);

$response['success'] = true;
$response['message'] = 'Status erfolgreich aktualisiert';
$response['device'] = [
    'id' => $device['id'],
    'name' => $device['name'],
    'status' => $status
];

echo json_encode($response);