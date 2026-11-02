<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('discussions');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    if (isset($_POST['supprimer_discussion'])) {
        $discussionId = filter_var($_POST['discussion_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($discussionId === false) {
            http_response_code(400);
            exit('Discussion invalide.');
        }
        $stmt = $pdo->prepare('DELETE FROM discussions WHERE id = ?');
        $stmt->execute([$discussionId]);
        redirect('/admin/discussions.php?supprime=discussion');
    }
    if (isset($_POST['supprimer_reponse'])) {
        $reponseId = filter_var($_POST['reponse_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($reponseId === false) {
            http_response_code(400);
            exit('Réponse invalide.');
        }
        $stmt = $pdo->prepare('DELETE FROM discussions_reponses WHERE id = ?');
        $stmt->execute([$reponseId]);
        redirect('/admin/discussions.php?supprime=reponse');
    }
}

$discussions = $pdo->query('SELECT * FROM discussions ORDER BY date_creation DESC LIMIT 100')->fetchAll();
$reponsesParDiscussion = [];
if ($discussions) {
    $ids = array_column($discussions, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM discussions_reponses WHERE discussion_id IN ($placeholders) ORDER BY date_creation ASC");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $reponse) {
        $reponsesParDiscussion[$reponse['discussion_id']][] = $reponse;
    }
}

$page_admin = 'discussions';
$titre_admin = 'Discussions';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1>Discussions</h1></div>
<?php if (($_GET['supprime'] ?? '') === 'discussion'): ?><div class="alerte alerte-succes">La discussion et ses réponses ont été supprimées.</div><?php endif; ?>
<?php if (($_GET['supprime'] ?? '') === 'reponse'): ?><div class="alerte alerte-succes">La réponse a été supprimée.</div><?php endif; ?>

<?php foreach ($discussions as $discussion): ?>
<article class="admin-carte discussion-admin">
    <div class="discussion-admin-entete">
        <div>
            <h2><?= h($discussion['titre']) ?></h2>
            <p class="discussion-meta"><?= h($discussion['nom_auteur'] ?: 'Anonyme') ?> · <?= h(formatDateFr($discussion['date_creation'], true)) ?></p>
        </div>
        <form method="post" onsubmit="return confirm('Supprimer cette discussion et toutes ses réponses ?');">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="discussion_id" value="<?= (int) $discussion['id'] ?>">
            <button type="submit" name="supprimer_discussion" class="bouton-lien lien-suppr">Supprimer la discussion</button>
        </form>
    </div>
    <p class="discussion-message"><?= nl2br(h($discussion['contenu'])) ?></p>
    <?php foreach ($reponsesParDiscussion[$discussion['id']] ?? [] as $reponse): ?>
    <div class="discussion-reponse discussion-reponse-admin">
        <div class="discussion-admin-entete">
            <p class="discussion-meta"><?= h($reponse['nom_auteur'] ?: 'Anonyme') ?> · <?= h(formatDateFr($reponse['date_creation'], true)) ?></p>
            <form method="post" onsubmit="return confirm('Supprimer cette réponse ?');">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="reponse_id" value="<?= (int) $reponse['id'] ?>">
                <button type="submit" name="supprimer_reponse" class="bouton-lien lien-suppr">Supprimer la réponse</button>
            </form>
        </div>
        <p class="discussion-message"><?= nl2br(h($reponse['contenu'])) ?></p>
    </div>
    <?php endforeach; ?>
</article>
<?php endforeach; ?>
<?php if (!$discussions): ?><div class="admin-carte">Aucune discussion pour le moment.</div><?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
