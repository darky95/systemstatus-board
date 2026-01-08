<?php
require_once 'config.php';
require_once 'classes/Auth.php';
require_once 'classes/ActivityLogger.php';
require_once 'classes/Security.php';

$auth = new Auth();
$auth->requireLogin();

$activityLogger = new ActivityLogger();
$activities = $activityLogger->getActivities(500);

$pageTitle = 'Aktivitätsprotokoll';
include 'includes/header.php';
?>

<div class="activities-page">
    <h1>Aktivitätsprotokoll</h1>
    
    <div class="activities-list">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Zeitstempel</th>
                    <th>Benutzer</th>
                    <th>Aktion</th>
                    <th>Tabelle</th>
                    <th>Datensatz-ID</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td><?= date('d.m.Y H:i:s', strtotime($activity['created_at'])) ?></td>
                        <td><?= Security::escape($activity['username'] ?? 'System') ?></td>
                        <td><span class="action-badge"><?= Security::escape($activity['action']) ?></span></td>
                        <td><?= Security::escape($activity['table_name']) ?></td>
                        <td><?= $activity['record_id'] ?? '-' ?></td>
                        <td>
                            <?php
                            $oldData = $activity['old_data'] ? json_decode($activity['old_data'], true) : null;
                            $newData = $activity['new_data'] ? json_decode($activity['new_data'], true) : null;
                            
                            if ($oldData || $newData) {
                                echo '<details><summary>Details</summary>';
                                if ($oldData) {
                                    echo '<strong>Alt:</strong> <pre>' . Security::escape(json_encode($oldData, JSON_PRETTY_PRINT)) . '</pre>';
                                }
                                if ($newData) {
                                    echo '<strong>Neu:</strong> <pre>' . Security::escape(json_encode($newData, JSON_PRETTY_PRINT)) . '</pre>';
                                }
                                echo '</details>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>