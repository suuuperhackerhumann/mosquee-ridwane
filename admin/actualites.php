<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('actualites');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer'])) {
    checkCsrf();
    $stmt = $pdo->prepare("DELETE FROM actualites WHERE id = ?");
    $stmt->execute([(int) $_POST['supprimer']]);
    redirect('/admin/actualites?suppr=1');
}

$actus = $pdo->query("SELECT a.*, c.nom AS categorie FROM actualites a LEFT JOIN categories c ON a.categorie_id = c.id ORDER BY a.date_publication DESC")->fetchAll();

$page_admin = 'actualites';
$titre_admin = 'Actualités';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete">
    <h1>Actualités &amp; annonces</h1>
    <a href="<?= BASE_URL ?>/admin/actualite-form.php" class="bouton bouton-emeraude">+ Nouvelle actualité</a>
</div>

<?php if (isset($_GET['suppr'])): ?><div class="alerte alerte-succes">Actualité supprimée.</div><?php endif; ?>
<?php if (isset($_GET['ok'])): ?><div class="alerte alerte-succes">Actualité enregistrée.</div><?php endif; ?>

<div class="admin-carte">
    <table class="table-admin">
        <thead><tr><th>Titre</th><th>Catégorie</th><th>Statut</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($actus as $a): ?>
            <tr>
                <td><?= h($a['titre']) ?></td>
                <td><?= h($a['categorie'] ?? '—') ?></td>
                <td><span class="statut-pastille statut-<?= $a['statut'] ?>"><?= $a['statut'] === 'publie' ? 'Publié' : 'Brouillon' ?></span></td>
                <td><?= h(formatDateFr($a['date_publication'])) ?></td>
                <td class="actions">
                    <a href="<?= BASE_URL ?>/admin/actualite-form.php?id=<?= $a['id'] ?>">Modifier</a>
                    <form method="post" action="<?= BASE_URL ?>/admin/actualites.php" onsubmit="return confirm('Supprimer cette actualité ?');">
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                        <input type="hidden" name="supprimer" value="<?= (int) $a['id'] ?>">
                        <button type="submit" class="lien-suppr bouton-lien">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$actus): ?><tr><td colspan="5">Aucune actualité pour le moment.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
