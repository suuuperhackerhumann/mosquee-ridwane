<?php
// Nécessite que config.php ait déjà été chargé et requireAdmin() appelé
$page_admin = $page_admin ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($titre_admin ?? 'Administration') ?> — Espace admin</title>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Karla:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <span class="marque">Mosquée Ridwane</span>
        <span class="sous-marque">Espace d'administration</span>
        <ul class="admin-nav">
            <li><a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= $page_admin === 'dashboard' ? 'actif' : '' ?>">Tableau de bord</a></li>
            <?php if (hasAdminPermission('actualites')): ?><li><a href="<?= BASE_URL ?>/admin/actualites.php" class="<?= $page_admin === 'actualites' ? 'actif' : '' ?>">Actualités</a></li><?php endif; ?>
            <?php if (hasAdminPermission('evenements')): ?><li><a href="<?= BASE_URL ?>/admin/evenements.php" class="<?= $page_admin === 'evenements' ? 'actif' : '' ?>">Événements</a></li><?php endif; ?>
            <?php if (hasAdminPermission('horaires')): ?><li><a href="<?= BASE_URL ?>/admin/horaires.php" class="<?= $page_admin === 'horaires' ? 'actif' : '' ?>">Horaires de prière</a></li><?php endif; ?>
            <?php if (hasAdminPermission('pages')): ?><li><a href="<?= BASE_URL ?>/admin/pages.php" class="<?= $page_admin === 'pages' ? 'actif' : '' ?>">Contenu des pages</a></li><?php endif; ?>
            <?php if (hasAdminPermission('media')): ?><li><a href="<?= BASE_URL ?>/admin/media.php" class="<?= $page_admin === 'media' ? 'actif' : '' ?>">Médiathèque</a></li><?php endif; ?>
            <?php if (hasAdminPermission('messages')): ?><li><a href="<?= BASE_URL ?>/admin/messages.php" class="<?= $page_admin === 'messages' ? 'actif' : '' ?>">Messages de contact</a></li><?php endif; ?>
            <?php if (hasAdminPermission('discussions')): ?><li><a href="<?= BASE_URL ?>/admin/discussions.php" class="<?= $page_admin === 'discussions' ? 'actif' : '' ?>">Discussions</a></li><?php endif; ?>
            <?php if (isFullAdmin()): ?><li><a href="<?= BASE_URL ?>/admin/roles.php" class="<?= $page_admin === 'roles' ? 'actif' : '' ?>">Utilisateurs et rôles</a></li><?php endif; ?>
            <li><a href="<?= BASE_URL ?>/admin/mot-de-passe.php" class="<?= $page_admin === 'mot-de-passe' ? 'actif' : '' ?>">Changer mon mot de passe</a></li>
            <li class="voir-site"><a href="<?= BASE_URL ?>/index.php" target="_blank">↗ Voir le site</a></li>
            <li>
                <form method="post" action="<?= BASE_URL ?>/admin/logout.php">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <button type="submit" class="admin-deconnexion">Se déconnecter</button>
                </form>
            </li>
        </ul>
    </aside>
    <main class="admin-main">