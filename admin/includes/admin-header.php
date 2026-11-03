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
<meta name="theme-color" content="#0e2d29">
<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon-32.png">
<link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon-32.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/img/apple-touch-icon.png">
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Karla:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-layout">
    <aside class="admin-sidebar" id="admin-navigation">
        <a class="admin-marque" href="<?= BASE_URL ?>/admin/dashboard">
            <img class="admin-logo" src="<?= BASE_URL ?>/assets/img/logo-mosquee.png" alt="">
            <span class="marque">Mosquée Ridwane</span>
        </a>
        <span class="sous-marque">Espace d'administration</span>
        <ul class="admin-nav">
            <li><a href="<?= BASE_URL ?>/admin/dashboard" class="<?= $page_admin === 'dashboard' ? 'actif' : '' ?>"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Tableau de bord</a></li>
            <?php if (hasAdminPermission('actualites')): ?><li><a href="<?= BASE_URL ?>/admin/actualites" class="<?= $page_admin === 'actualites' ? 'actif' : '' ?>"><i class="fa-regular fa-newspaper" aria-hidden="true"></i> Actualités</a></li><?php endif; ?>
            <?php if (hasAdminPermission('evenements')): ?><li><a href="<?= BASE_URL ?>/admin/evenements" class="<?= $page_admin === 'evenements' ? 'actif' : '' ?>"><i class="fa-regular fa-calendar-days" aria-hidden="true"></i> Événements</a></li><?php endif; ?>
            <?php if (hasAdminPermission('horaires')): ?><li><a href="<?= BASE_URL ?>/admin/horaires" class="<?= $page_admin === 'horaires' ? 'actif' : '' ?>"><i class="fa-regular fa-clock" aria-hidden="true"></i> Horaires de prière</a></li><?php endif; ?>
            <?php if (hasAdminPermission('pages')): ?><li><a href="<?= BASE_URL ?>/admin/pages" class="<?= $page_admin === 'pages' ? 'actif' : '' ?>"><i class="fa-regular fa-file-lines" aria-hidden="true"></i> Contenu des pages</a></li><?php endif; ?>
            <?php if (hasAdminPermission('media')): ?><li><a href="<?= BASE_URL ?>/admin/media" class="<?= $page_admin === 'media' ? 'actif' : '' ?>"><i class="fa-solid fa-photo-film" aria-hidden="true"></i> Médiathèque</a></li><?php endif; ?>
            <?php if (hasAdminPermission('messages')): ?><li><a href="<?= BASE_URL ?>/admin/messages" class="<?= $page_admin === 'messages' ? 'actif' : '' ?>"><i class="fa-regular fa-envelope" aria-hidden="true"></i> Messages de contact</a></li><?php endif; ?>
            <?php if (hasAdminPermission('discussions')): ?><li><a href="<?= BASE_URL ?>/admin/discussions" class="<?= $page_admin === 'discussions' || $page_admin === 'live' ? 'actif' : '' ?>"><i class="fa-regular fa-comments" aria-hidden="true"></i> Discussions et live</a></li><?php endif; ?>
            <?php if (isFullAdmin()): ?><li><a href="<?= BASE_URL ?>/admin/roles" class="<?= $page_admin === 'roles' ? 'actif' : '' ?>"><i class="fa-solid fa-users-gear" aria-hidden="true"></i> Utilisateurs et rôles</a></li><?php endif; ?>
            <li><a href="<?= BASE_URL ?>/admin/mot-de-passe" class="<?= $page_admin === 'mot-de-passe' ? 'actif' : '' ?>"><i class="fa-solid fa-key" aria-hidden="true"></i> Changer mon mot de passe</a></li>
            <li class="voir-site"><a href="<?= BASE_URL ?>/" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Voir le site</a></li>
            <li>
                <form method="post" action="<?= BASE_URL ?>/admin/logout">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <button type="submit" class="admin-deconnexion"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Se déconnecter</button>
                </form>
            </li>
        </ul>
    </aside>
    <button type="button" class="admin-sidebar-overlay" aria-label="Fermer le menu d'administration"></button>
    <main class="admin-main">
        <div class="admin-mobile-bar">
            <button type="button" class="admin-menu-toggle" aria-controls="admin-navigation" aria-expanded="false" aria-label="Ouvrir le menu d'administration">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <span>Administration</span>
        </div>