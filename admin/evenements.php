<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('evenements');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer'])) {
    checkCsrf();
    $stmt = $pdo->prepare("DELETE FROM evenements WHERE id = ?");
    $stmt->execute([(int) $_POST['supprimer']]);
    redirect('/admin/evenements?suppr=1');
}

$evenements = $pdo->query("SELECT e.*, c.nom AS categorie FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id ORDER BY e.date_evenement DESC")->fetchAll();

$page_admin = 'evenements';
$titre_admin = 'Événements';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete">
    <h1>Événements</h1>
    <a href="<?= BASE_URL ?>/admin/evenement-form.php" class="bouton bouton-emeraude">+ Nouvel événement</a>
</div>

<?php if (isset($_GET['suppr'])): ?><div class="alerte alerte-succes">Événement supprimé.</div><?php endif; ?>
<?php if (isset($_GET['ok'])): ?><div class="alerte alerte-succes">Événement enregistré.</div><?php endif; ?>

<div class="admin-carte">
    <table class="table-admin">
        <thead><tr><th>Titre</th><th>Catégorie</th><th>Date</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($evenements as $e): ?>
            <tr>
                <td><?= h($e['titre']) ?></td>
                <td><?= h($e['categorie'] ?? '—') ?></td>
                <td><?= h(formatDateFr($e['date_evenement'])) ?></td>
                <td><span class="statut-pastille statut-<?= $e['statut'] ?>"><?= $e['statut'] === 'publie' ? 'Publié' : 'Brouillon' ?></span></td>
                <td class="actions">
                    <a href="<?= BASE_URL ?>/admin/evenement-form.php?id=<?= $e['id'] ?>">Modifier</a>
                    <form method="post" action="<?= BASE_URL ?>/admin/evenements.php" onsubmit="return confirm('Supprimer cet événement ?');">
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                        <input type="hidden" name="supprimer" value="<?= (int) $e['id'] ?>">
                        <button type="submit" class="lien-suppr bouton-lien">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$evenements): ?><tr><td colspan="5">Aucun événement pour le moment.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
