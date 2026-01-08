<?php
require_once 'config.php';
require_once 'classes/Auth.php';
require_once 'classes/Database.php';
require_once 'classes/ActivityLogger.php';
require_once 'classes/Security.php';

$auth = new Auth();
$auth->requireAdmin();

$db = new Database();
$activityLogger = new ActivityLogger();
$error = '';
$success = '';

$csrfToken = Security::generateCSRFToken();

// Benutzer erstellen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
        
        if (empty($username) || empty($password)) {
            $error = 'Benutzername und Passwort sind erforderlich';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $error = 'Passwort muss mindestens ' . PASSWORD_MIN_LENGTH . ' Zeichen lang sein';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            try {
                $db->execute(
                    "INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)",
                    [$username, $hashedPassword, $isAdmin]
                );
                $userId = $db->getConnection()->lastInsertId();
                $activityLogger->log('user_created', 'users', $userId, null, ['username' => $username, 'is_admin' => $isAdmin], $auth->getUserId());
                $success = 'Benutzer erfolgreich erstellt';
            } catch (PDOException $e) {
                $error = 'Benutzername bereits vergeben';
            }
        }
    }
}

// Benutzer bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
        
        $oldUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        
        if (empty($username)) {
            $error = 'Benutzername ist erforderlich';
        } else {
            if (!empty($password)) {
                if (strlen($password) < PASSWORD_MIN_LENGTH) {
                    $error = 'Passwort muss mindestens ' . PASSWORD_MIN_LENGTH . ' Zeichen lang sein';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    try {
                        $db->execute(
                            "UPDATE users SET username = ?, password = ?, is_admin = ? WHERE id = ?",
                            [$username, $hashedPassword, $isAdmin, $id]
                        );
                        $activityLogger->log('user_updated', 'users', $id, $oldUser, ['username' => $username, 'is_admin' => $isAdmin], $auth->getUserId());
                        $success = 'Benutzer erfolgreich aktualisiert';
                    } catch (PDOException $e) {
                        $error = 'Benutzername bereits vergeben';
                    }
                }
            } else {
                try {
                    $db->execute(
                        "UPDATE users SET username = ?, is_admin = ? WHERE id = ?",
                        [$username, $isAdmin, $id]
                    );
                    $activityLogger->log('user_updated', 'users', $id, $oldUser, ['username' => $username, 'is_admin' => $isAdmin], $auth->getUserId());
                    $success = 'Benutzer erfolgreich aktualisiert';
                } catch (PDOException $e) {
                    $error = 'Benutzername bereits vergeben';
                }
            }
        }
    }
}

// Benutzer löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültiger CSRF-Token';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        if ($id == $auth->getUserId()) {
            $error = 'Sie können sich nicht selbst löschen';
        } else {
            $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
            if ($user) {
                $db->execute("DELETE FROM users WHERE id = ?", [$id]);
                $activityLogger->log('user_deleted', 'users', $id, $user, null, $auth->getUserId());
                $success = 'Benutzer erfolgreich gelöscht';
            }
        }
    }
}

$users = $db->fetchAll("SELECT * FROM users ORDER BY username");

$pageTitle = 'Benutzerverwaltung';
include 'includes/header.php';
?>

<div class="users-page">
    <h1>Benutzerverwaltung</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= Security::escape($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= Security::escape($success) ?></div>
    <?php endif; ?>
    
    <div class="users-header">
        <button class="btn btn-primary" onclick="showCreateForm()">Neuen Benutzer erstellen</button>
    </div>
    
    <!-- Erstellungsformular -->
    <div id="createForm" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('createForm')">&times;</span>
            <h2>Neuen Benutzer erstellen</h2>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="create_username">Benutzername *</label>
                    <input type="text" id="create_username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="create_password">Passwort *</label>
                    <input type="password" id="create_password" name="password" required minlength="<?= PASSWORD_MIN_LENGTH ?>">
                    <small>Mindestens <?= PASSWORD_MIN_LENGTH ?> Zeichen</small>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_admin" value="1">
                        Administrator
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Erstellen</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('createForm')">Abbrechen</button>
            </form>
        </div>
    </div>
    
    <!-- Bearbeitungsformular -->
    <div id="editForm" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editForm')">&times;</span>
            <h2>Benutzer bearbeiten</h2>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_username">Benutzername *</label>
                    <input type="text" id="edit_username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="edit_password">Neues Passwort (leer lassen, um nicht zu ändern)</label>
                    <input type="password" id="edit_password" name="password" minlength="<?= PASSWORD_MIN_LENGTH ?>">
                    <small>Mindestens <?= PASSWORD_MIN_LENGTH ?> Zeichen</small>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_admin" value="1" id="edit_is_admin">
                        Administrator
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Speichern</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editForm')">Abbrechen</button>
            </form>
        </div>
    </div>
    
    <!-- Benutzerliste -->
    <div class="users-list">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Benutzername</th>
                    <th>Administrator</th>
                    <th>Erstellt am</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= Security::escape($user['username']) ?></td>
                        <td><?= $user['is_admin'] ? 'Ja' : 'Nein' ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="editUser(<?= htmlspecialchars(json_encode($user), ENT_QUOTES) ?>)">Bearbeiten</button>
                            <?php if ($user['id'] != $auth->getUserId()): ?>
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Benutzer wirklich löschen?')">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showCreateForm() {
    document.getElementById('createForm').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function editUser(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_is_admin').checked = user.is_admin == 1;
    document.getElementById('edit_password').value = '';
    document.getElementById('editForm').style.display = 'block';
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