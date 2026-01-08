<?php
require_once 'config.php';
require_once 'classes/Auth.php';
require_once 'classes/Database.php';
require_once 'classes/Security.php';
require_once 'classes/ActivityLogger.php';

$auth = new Auth();
$auth->requireAdmin();

$db = new Database();
$activityLogger = new ActivityLogger();
$error = '';
$success = '';

$csrfToken = Security::generateCSRFToken();

// API-Key erstellen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token';
    } else {
        $description = trim($_POST['description'] ?? '');
        $apiKey = Security::generateApiKey();
        
        try {
            $db->execute(
                "INSERT INTO api_keys (api_key, description) VALUES (?, ?)",
                [$apiKey, $description]
            );
            $keyId = $db->getConnection()->lastInsertId();
            $activityLogger->log('api_key_created', 'api_keys', $keyId, null, ['description' => $description], $auth->getUserId());
            $success = 'API-Key erfolgreich erstellt: <strong>' . Security::escape($apiKey) . '</strong><br><small>Bitte notieren Sie sich diesen Key, er wird nicht erneut angezeigt!</small>';
        } catch (PDOException $e) {
            $error = 'Fehler beim Erstellen des API-Keys';
        }
    }
}

// API-Key aktivieren/deaktivieren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $key = $db->fetchOne("SELECT * FROM api_keys WHERE id = ?", [$id]);
        if ($key) {
            $newStatus = $key['is_active'] ? 0 : 1;
            $db->execute("UPDATE api_keys SET is_active = ? WHERE id = ?", [$newStatus, $id]);
            $activityLogger->log('api_key_toggled', 'api_keys', $id, $key, ['is_active' => $newStatus], $auth->getUserId());
            $success = 'API-Key Status geändert';
        }
    }
}

// API-Key löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $key = $db->fetchOne("SELECT * FROM api_keys WHERE id = ?", [$id]);
        if ($key) {
            $db->execute("DELETE FROM api_keys WHERE id = ?", [$id]);
            $activityLogger->log('api_key_deleted', 'api_keys', $id, $key, null, $auth->getUserId());
            $success = 'API-Key erfolgreich gelöscht';
        }
    }
}

$apiKeys = $db->fetchAll("SELECT * FROM api_keys ORDER BY created_at DESC");

$pageTitle = 'API-Keys';
include 'includes/header.php';
?>

<div class="api-keys-page">
    <h1>API-Key Verwaltung</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= Security::escape($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <div class="api-keys-header">
        <button class="btn btn-primary" onclick="showCreateForm()">Neuen API-Key erstellen</button>
    </div>
    
    <!-- Erstellungsformular -->
    <div id="createForm" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('createForm')">&times;</span>
            <h2>Neuen API-Key erstellen</h2>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="create_description">Beschreibung</label>
                    <input type="text" id="create_description" name="description" placeholder="z.B. Produktionsserver">
                </div>
                <button type="submit" class="btn btn-primary">Erstellen</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('createForm')">Abbrechen</button>
            </form>
        </div>
    </div>
    
    <!-- API-Key Liste -->
    <div class="api-keys-list">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>API-Key</th>
                    <th>Beschreibung</th>
                    <th>Status</th>
                    <th>Erstellt am</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apiKeys as $key): ?>
                    <tr>
                        <td><?= $key['id'] ?></td>
                        <td><code class="api-key"><?= Security::escape($key['api_key']) ?></code></td>
                        <td><?= Security::escape($key['description'] ?? '') ?></td>
                        <td>
                            <span class="status-badge <?= $key['is_active'] ? 'status-normal' : 'status-gestoert' ?>">
                                <?= $key['is_active'] ? 'Aktiv' : 'Inaktiv' ?>
                            </span>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($key['created_at'])) ?></td>
                        <td>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $key['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-secondary">
                                    <?= $key['is_active'] ? 'Deaktivieren' : 'Aktivieren' ?>
                                </button>
                            </form>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('API-Key wirklich löschen?')">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $key['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="api-info">
        <h2>API-Verwendung</h2>
        <p>Verwenden Sie den API-Key für externe Statusaktualisierungen:</p>
        <pre><code>POST /api/status/update
Content-Type: application/x-www-form-urlencoded

api_key=YOUR_API_KEY
device_key=DEVICE_KEY
status=normal|eingeschraenkt|wartung|gestoert
note=Optional note</code></pre>
    </div>
</div>

<script>
function showCreateForm() {
    document.getElementById('createForm').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
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