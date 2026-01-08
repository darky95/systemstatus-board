<?php
require_once 'config.php';
require_once 'classes/Auth.php';
require_once 'classes/DeviceManager.php';
require_once 'classes/ActivityLogger.php';
require_once 'classes/Security.php';

$auth = new Auth();
$auth->requireLogin();

$deviceManager = new DeviceManager();
$activityLogger = new ActivityLogger();
$error = '';
$success = '';

// CSRF-Token generieren
$csrfToken = Security::generateCSRFToken();

// Gerät erstellen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token';
    } else {
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? STATUS_NORMAL;
        $note = trim($_POST['note'] ?? '');
        $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        
        if (empty($name)) {
            $error = 'Name ist erforderlich';
        } elseif (!Security::validateStatus($status)) {
            $error = 'Ungültiger Status';
        } else {
            $deviceId = $deviceManager->createDevice($name, $status, $note, $parentId);
            $activityLogger->log('device_created', 'devices', $deviceId, null, ['name' => $name, 'status' => $status], $auth->getUserId());
            $success = 'Gerät erfolgreich erstellt';
        }
    }
}

// Gerät bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? STATUS_NORMAL;
        $note = trim($_POST['note'] ?? '');
        $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        
        $oldDevice = $deviceManager->getDeviceById($id);
        
        if (empty($name) || !Security::validateStatus($status)) {
            $error = 'Ungültige Eingabe';
        } else {
            $deviceManager->updateDevice($id, $name, $status, $note, $parentId);
            $activityLogger->log('device_updated', 'devices', $id, $oldDevice, ['name' => $name, 'status' => $status], $auth->getUserId());
            $success = 'Gerät erfolgreich aktualisiert';
        }
    }
}

// Gerät löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $device = $deviceManager->getDeviceById($id);
        if ($device) {
            $deviceManager->deleteDevice($id);
            $activityLogger->log('device_deleted', 'devices', $id, $device, null, $auth->getUserId());
            $success = 'Gerät erfolgreich gelöscht';
        }
    }
}

// Massenstatus-Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_update') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token';
    } else {
        $deviceIds = $_POST['device_ids'] ?? [];
        $status = $_POST['status'] ?? STATUS_NORMAL;
        $note = trim($_POST['note'] ?? '');
        
        if (!empty($deviceIds) && Security::validateStatus($status)) {
            $deviceManager->bulkUpdateStatus($deviceIds, $status, $note);
            $activityLogger->log('bulk_status_update', 'devices', null, null, ['device_ids' => $deviceIds, 'status' => $status], $auth->getUserId());
            $success = count($deviceIds) . ' Gerät(e) erfolgreich aktualisiert';
        }
    }
}

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

$devices = $deviceManager->getAllDevices();
$deviceTree = $deviceManager->getDeviceTree();
$stats = $deviceManager->getStatusStatistics();
$sortedDevices = sortDevicesHierarchical($devices);

$pageTitle = 'Systemstatus IT';
include 'includes/header.php';
?>

<div class="devices-page">
    <h1>Systemstatus IT</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= Security::escape($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= Security::escape($success) ?></div>
    <?php endif; ?>
    
    <div class="devices-header">
        <button class="btn btn-primary" onclick="showCreateForm()">Neues Gerät erstellen</button>
        <button class="btn btn-secondary" onclick="showBulkUpdateForm()">Massenstatus-Update</button>
    </div>
    
    <!-- Erstellungsformular -->
    <div id="createForm" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('createForm')">&times;</span>
            <h2>Neues Gerät erstellen</h2>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="create_name">Name *</label>
                    <input type="text" id="create_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="create_status">Status</label>
                    <select id="create_status" name="status">
                        <option value="<?= STATUS_NORMAL ?>">Normal</option>
                        <option value="<?= STATUS_EINGESCHRAENKT ?>">Eingeschränkt</option>
                        <option value="<?= STATUS_WARTUNG ?>">Wartung</option>
                        <option value="<?= STATUS_GESTOERT ?>">Gestört</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="create_parent">Übergeordnetes Gerät</label>
                    <select id="create_parent" name="parent_id">
                        <option value="">Keines</option>
                        <?php foreach ($sortedDevices as $device): ?>
                            <option value="<?= $device['id'] ?>"><?= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $device['level'] ?? 0) ?><?= Security::escape($device['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="create_note">Notiz</label>
                    <textarea id="create_note" name="note" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Erstellen</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('createForm')">Abbrechen</button>
            </form>
        </div>
    </div>
    
    <!-- Massenupdate-Formular -->
    <div id="bulkUpdateForm" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('bulkUpdateForm')">&times;</span>
            <h2>Massenstatus-Update</h2>
            <form method="POST" action="" id="bulkUpdateFormElement">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="bulk_update">
                <div class="form-group">
                    <label for="bulk_status">Neuer Status</label>
                    <select id="bulk_status" name="status" required>
                        <option value="<?= STATUS_NORMAL ?>">Normal</option>
                        <option value="<?= STATUS_EINGESCHRAENKT ?>">Eingeschränkt</option>
                        <option value="<?= STATUS_WARTUNG ?>">Wartung</option>
                        <option value="<?= STATUS_GESTOERT ?>">Gestört</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bulk_note">Notiz (optional)</label>
                    <textarea id="bulk_note" name="note" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Geräte auswählen:</label>
                    <div class="device-checkboxes">
                        <?php foreach ($devices as $device): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="device_ids[]" value="<?= $device['id'] ?>">
                                <?= Security::escape($device['name']) ?> 
                                <span class="status-badge status-<?= $device['status'] ?>"><?= Security::escape($status_labels[$device['status']] ?? $device['status']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Aktualisieren</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('bulkUpdateForm')">Abbrechen</button>
            </form>
        </div>
    </div>
    
    <!-- Suchfunktion -->
    <div class="search-box">
        <input type="text" id="deviceSearch" placeholder="Geräte durchsuchen..." onkeyup="filterDevices()">
    </div>
    
    <!-- Geräteliste -->
    <div class="devices-list">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Notiz</th>
                    <th>Geräte-Key</th>
                    <th>Zuletzt aktualisiert</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody id="deviceTableBody">
                <?php foreach ($sortedDevices as $device): ?>
                    <tr data-device-name="<?= strtolower(Security::escape($device['name'])) ?>">
                        <td style="padding-left: <?= ($device['level'] ?? 0) * 30 + 15 ?>px;">
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
                        <td><code class="device-key"><?= Security::escape($device['device_key']) ?></code></td>
                        <td><?= date('d.m.Y H:i', strtotime($device['updated_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="editDevice(<?= htmlspecialchars(json_encode($device), ENT_QUOTES) ?>)">Bearbeiten</button>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Gerät wirklich löschen?')">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $device['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Bearbeitungsformular -->
    <div id="editForm" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editForm')">&times;</span>
            <h2>Gerät bearbeiten</h2>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_name">Name *</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status">
                        <option value="<?= STATUS_NORMAL ?>">Normal</option>
                        <option value="<?= STATUS_EINGESCHRAENKT ?>">Eingeschränkt</option>
                        <option value="<?= STATUS_WARTUNG ?>">Wartung</option>
                        <option value="<?= STATUS_GESTOERT ?>">Gestört</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_parent">Übergeordnetes Gerät</label>
                    <select id="edit_parent" name="parent_id">
                        <option value="">Keines</option>
                        <?php foreach ($sortedDevices as $device): ?>
                            <option value="<?= $device['id'] ?>"><?= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $device['level'] ?? 0) ?><?= Security::escape($device['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_note">Notiz</label>
                    <textarea id="edit_note" name="note" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Speichern</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editForm')">Abbrechen</button>
            </form>
        </div>
    </div>
</div>

<script>
function showCreateForm() {
    document.getElementById('createForm').style.display = 'block';
}

function showBulkUpdateForm() {
    document.getElementById('bulkUpdateForm').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function editDevice(device) {
    document.getElementById('edit_id').value = device.id;
    document.getElementById('edit_name').value = device.name;
    document.getElementById('edit_status').value = device.status;
    document.getElementById('edit_parent').value = device.parent_id || '';
    document.getElementById('edit_note').value = device.note || '';
    document.getElementById('editForm').style.display = 'block';
}

function filterDevices() {
    const input = document.getElementById('deviceSearch');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('#deviceTableBody tr');
    
    rows.forEach(row => {
        const name = row.getAttribute('data-device-name');
        if (name.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>