<?php
// $page_titre, $page_description et $fil_ariane (optionnel) sont définis par la page appelante
$page_titre = $page_titre ?? SITE_NOM;
$page_description = $page_description ?? SITE_DESCRIPTION;

$liens_nav = [
    'index.php'          => 'Accueil',
    'a-propos.php'       => 'À propos',
    'horaires.php'       => 'Horaires',
    'actualites.php'     => 'Actualités',
    'evenements.php'     => 'Événements',
    'enseignements.php'  => 'Enseignements',
    'services.php'       => 'Services',
    'mediatheque.php'    => 'Médiathèque',
    'discussions.php'    => 'Discussion',
    'contact.php'        => 'Contact',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_titre) ?> — <?= h(SITE_NOM) ?></title>
<meta name="description" content="<?= h($page_description) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Karla:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/accueil-visuel.css">
</head>
<body>

<a href="#contenu-principal" class="lien-evitement">Aller au contenu principal</a>

<header class="site-header" id="entete">
    <div class="conteneur header-bar">
        <a href="<?= BASE_URL ?>/index.php" class="logo">
            <span class="logo-marque logo-emplacement" aria-label="Emplacement du logo de la mosquée">
                <i class="fa-solid fa-mosque" aria-hidden="true"></i>
            </span>
            <span>
                Mosquée Ridwane
                <span class="logo-sub">Foi · Savoir · Communauté</span>
            </span>
        </a>

        <nav class="nav-principale" aria-label="Navigation principale" id="nav-principale">
            <ul>
                <?php foreach ($liens_nav as $fichier => $libelle): ?>
                <li><a href="<?= BASE_URL ?>/<?= $fichier ?>" class="<?= trim(estActif($fichier)) ?: null ?>"<?= estActif($fichier) ? ' aria-current="page"' : '' ?>><?= h($libelle) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <button class="menu-toggle" aria-label="Ouvrir le menu" aria-controls="nav-principale" aria-expanded="false">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>
</header>

<a href="<?= BASE_URL ?>/dons.php" class="bouton bouton-dore don-flottant">
    <i class="fa-solid fa-hand-holding-heart icone" aria-hidden="true"></i> Faire un don
</a>

<main id="contenu-principal">
