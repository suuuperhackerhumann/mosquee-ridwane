<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('messages');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $messageId = (int) ($_POST['message_id'] ?? 0);
    if (isset($_POST['marquer_lu'])) {
        $stmt = $pdo->prepare("UPDATE messages_contact SET lu = 1 WHERE id = ?");
        $stmt->execute([$messageId]);
        redirect('/admin/messages');
    }
    if (isset($_POST['supprimer'])) {
        $stmt = $pdo->prepare("DELETE FROM messages_contact WHERE id = ?");
        $stmt->execute([$messageId]);
        redirect('/admin/messages?suppr=1');
    }
}

$messages = $pdo->query("SELECT * FROM messages_contact ORDER BY date_envoi DESC")->fetchAll();

$page_admin = 'messages';
$titre_admin = 'Messages de contact';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1>Messages de contact</h1></div>
<?php if (isset($_GET['suppr'])): ?><div class="alerte alerte-succes">Message supprimé.</div><?php endif; ?>

<?php foreach ($messages as $m): ?>
<div class="admin-carte" style="<?= $m['lu'] ? 'opacity:0.7;' : 'border-left:3px solid var(--laiton-500);' ?>">
    <div style="display:flex; justify-content:space-between; align-items:start;">
        <div>
            <strong><?= h($m['nom']) ?></strong> — <a href="mailto:<?= h($m['email']) ?>"><?= h($m['email']) ?></a>
            <?php if ($m['telephone']): ?> — <?= h($m['telephone']) ?><?php endif; ?>
            <?php if (!$m['lu']): ?><span class="statut-pastille statut-brouillon" style="margin-left:8px;">Non lu</span><?php endif; ?>
            <p style="color:var(--encre-600); font-size:0.85rem; margin:6px 0;"><?= h(formatDateFr($m['date_envoi'], true)) ?><?php if ($m['sujet']): ?> · Sujet : <?= h($m['sujet']) ?><?php endif; ?></p>
        </div>
        <div class="actions">
            <?php if (!$m['lu']): ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="message_id" value="<?= (int) $m['id'] ?>">
                <button type="submit" name="marquer_lu" class="bouton-lien">Marquer comme lu</button>
            </form>
            <?php endif; ?>
            <form method="post" onsubmit="return confirm('Supprimer ce message ?');">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="message_id" value="<?= (int) $m['id'] ?>">
                <button type="submit" name="supprimer" class="lien-suppr bouton-lien">Supprimer</button>
            </form>
        </div>
    </div>
    <p style="margin-top:10px; white-space:pre-line;"><?= h($m['message']) ?></p>
</div>
<?php endforeach; ?>
<?php if (!$messages): ?><div class="admin-carte">Aucun message reçu pour le moment.</div><?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
