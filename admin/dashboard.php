<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$nbActus = hasAdminPermission('actualites') ? $pdo->query("SELECT COUNT(*) FROM actualites")->fetchColumn() : null;
$nbEvenements = hasAdminPermission('evenements') ? $pdo->query("SELECT COUNT(*) FROM evenements WHERE date_evenement >= CURDATE()")->fetchColumn() : null;
$nbMessages = hasAdminPermission('messages') ? $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu = 0")->fetchColumn() : null;
$nbMedia = hasAdminPermission('media') ? $pdo->query("SELECT COUNT(*) FROM mediatheque")->fetchColumn() : null;

$page_admin = 'dashboard';
$titre_admin = 'Tableau de bord';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete">
    <h1>Bonjour, <?= h($_SESSION['admin_nom']) ?> 👋</h1>
</div>

<div class="admin-stats">
    <?php if ($nbActus !== null): ?><div class="admin-stat"><div class="valeur"><?= $nbActus ?></div><div class="libelle">Actualités</div></div><?php endif; ?>
    <?php if ($nbEvenements !== null): ?><div class="admin-stat"><div class="valeur"><?= $nbEvenements ?></div><div class="libelle">Événements à venir</div></div><?php endif; ?>
    <?php if ($nbMessages !== null): ?><div class="admin-stat"><div class="valeur"><?= $nbMessages ?></div><div class="libelle">Messages non lus</div></div><?php endif; ?>
    <?php if ($nbMedia !== null): ?><div class="admin-stat"><div class="valeur"><?= $nbMedia ?></div><div class="libelle">Fichiers médiathèque</div></div><?php endif; ?>
</div>

<div class="admin-carte">
    <h3 style="margin-top:0;">Accès rapides</h3>
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <?php if (hasAdminPermission('actualites')): ?><a href="<?= BASE_URL ?>/admin/actualite-form.php" class="bouton bouton-emeraude">+ Nouvelle actualité</a><?php endif; ?>
        <?php if (hasAdminPermission('evenements')): ?><a href="<?= BASE_URL ?>/admin/evenement-form.php" class="bouton bouton-emeraude">+ Nouvel événement</a><?php endif; ?>
        <?php if (hasAdminPermission('horaires')): ?><a href="<?= BASE_URL ?>/admin/horaires.php" class="bouton bouton-emeraude">Mettre à jour les horaires</a><?php endif; ?>
        <?php if (hasAdminPermission('pages')): ?><a href="<?= BASE_URL ?>/admin/pages.php" class="bouton bouton-emeraude">Modifier les textes du site</a><?php endif; ?>
    </div>
</div>

<?php if (hasAdminPermission('horaires')): ?>
<div class="admin-carte">
    <h3 style="margin-top:0;">Pense-bête</h3>
    <p style="color:var(--encre-600); font-size:0.92rem;">Pensez à mettre à jour les horaires de prière régulièrement (idéalement chaque mois, et quotidiennement pendant le Ramadan) depuis la rubrique « Horaires de prière ».</p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
