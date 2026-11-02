<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('messages');

if (isset($_GET['lu'])) {
    $stmt = $pdo->prepare("UPDATE messages_contact SET lu = 1 WHERE id = ?");
    $stmt->execute([(int)$_GET['lu']]);
    redirect('/admin/messages.php');
}
if (isset($_GET['supprimer'])) {
    $stmt = $pdo->prepare("DELETE FROM messages_contact WHERE id = ?");
    $stmt->execute([(int)$_GET['supprimer']]);
    redirect('/admin/messages.php?suppr=1');
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
            <?php if (!$m['lu']): ?><a href="?lu=<?= $m['id'] ?>">Marquer comme lu</a><?php endif; ?>
            <a href="?supprimer=<?= $m['id'] ?>" class="lien-suppr" onclick="return confirm('Supprimer ce message ?');">Supprimer</a>
        </div>
    </div>
    <p style="margin-top:10px; white-space:pre-line;"><?= h($m['message']) ?></p>
</div>
<?php endforeach; ?>
<?php if (!$messages): ?><div class="admin-carte">Aucun message reçu pour le moment.</div><?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
