<?php
/* Admin - manage contact messages (SELECT, UPDATE read flag, DELETE). */
$adminPage = 'messages';
$pageTitle = 'Customer Messages';
require_once __DIR__ . '/../includes/functions.php';
if (!is_admin()) { redirect('../login.php'); }

/* ----- Mark a message as read (DML: UPDATE) ------------------------- */
if (($_GET['action'] ?? '') === 'read' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?');
    $stmt->execute([(int) $_GET['id']]);
    redirect('messages.php');
}

/* ----- Delete a message (DML: DELETE) ------------------------------- */
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM messages WHERE id = ?');
    $stmt->execute([(int) $_GET['id']]);
    set_flash('Message deleted.');
    redirect('messages.php');
}

/* ----- Load messages (DML: SELECT) ---------------------------------- */
$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>

<?php show_flash(); ?>

<div class="panel">
    <?php if (empty($messages)): ?>
        <p class="text-muted mb-0">No messages received yet.</p>
    <?php else: ?>
        <?php foreach ($messages as $m): ?>
            <div class="border rounded p-3 mb-3 <?= $m['is_read'] ? '' : 'bg-light' ?>">
                <div class="d-flex justify-content-between flex-wrap">
                    <div>
                        <strong><?= e($m['subject']) ?></strong>
                        <?php if (!$m['is_read']): ?>
                            <span class="badge bg-warning text-dark">New</span>
                        <?php endif; ?>
                        <br>
                        <small class="text-muted">
                            From <?= e($m['name']) ?> &lt;<?= e($m['email']) ?>&gt; ·
                            <?= e(date('d M Y, H:i', strtotime($m['created_at']))) ?>
                        </small>
                    </div>
                    <div class="action-buttons">
                        <?php if (!$m['is_read']): ?>
                            <a href="messages.php?action=read&id=<?= (int) $m['id'] ?>"
                               class="btn btn-sm btn-outline-gold">Mark read</a>
                        <?php endif; ?>
                        <a href="messages.php?action=delete&id=<?= (int) $m['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           data-confirm="Delete this message?">Delete</a>
                    </div>
                </div>
                <p class="mb-0 mt-2"><?= nl2br(e($m['message'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
