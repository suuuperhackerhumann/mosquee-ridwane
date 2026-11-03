<?php
require_once __DIR__ . '/config/config.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT a.*, c.nom AS categorie FROM actualites a LEFT JOIN categories c ON a.categorie_id = c.id WHERE a.slug = ? AND a.statut = 'publie'");
$stmt->execute([$slug]);
$actu = $stmt->fetch();

if (!$actu) {
    renderSiteError(404, 'Actualité introuvable', 'Cette actualité n’existe pas ou n’est plus disponible.', BASE_URL . '/actualites.php', 'Voir les actualités');
}

$page_titre = $actu['titre'];
$page_description = extraitTexte(
    $actu['resume'] ?: ($actu['contenu'] ?: 'Actualité de la Mosquée Ridwane à Riviera Abatta, Abidjan : ' . $actu['titre']),
    160
);
if (mb_strlen($page_description) < 40) {
    $page_description = 'Actualité de la Mosquée Ridwane à Riviera Abatta, Abidjan : '
        . $actu['titre']
        . ($page_description !== '' ? ' — ' . $page_description : '.');
}
$page_og_type = 'article';
$page_og_image = $actu['image'] ?: '/assets/img/social-share.png';
$page_og_image_alt = $actu['titre'];
$page_publication_date = $actu['date_publication'];
$articleImageUrl = rtrim(SITE_URL, '/') . '/' . ltrim($page_og_image, '/');
$page_structured_data = [[
    '@type' => 'NewsArticle',
    'headline' => $actu['titre'],
    'description' => $page_description,
    'image' => $articleImageUrl,
    'datePublished' => date(DATE_W3C, strtotime($actu['date_publication'])),
    'mainEntityOfPage' => $canonicalUrl ?? rtrim(SITE_URL, '/') . '/actualite?slug=' . rawurlencode($actu['slug']),
    'publisher' => ['@id' => rtrim(SITE_URL, '/') . '/#mosquee'],
]];
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / <a href="<?= BASE_URL ?>/actualites.php" style="color:inherit;">Actualités</a> / <?= h($actu['titre']) ?></div>
        <?php if ($actu['categorie']): ?><span class="badge"><?= h($actu['categorie']) ?></span><?php endif; ?>
        <h1><?= h($actu['titre']) ?></h1>
        <p style="color:var(--laiton-300); font-family:var(--police-donnee);"><?= h(formatDateFr($actu['date_publication'])) ?></p>
    </div>
</section>

<section class="section">
    <div class="conteneur" style="max-width:780px;">
        <?php if ($actu['image']): ?>
        <img src="<?= h(siteAssetUrl($actu['image'])) ?>" alt="<?= h($actu['titre']) ?>" style="border-radius:6px; margin-bottom:32px;">
        <?php endif; ?>
        <div style="font-size:1.05rem;"><?= nl2br(h($actu['contenu'])) ?></div>
        <p style="margin-top:40px;"><a href="<?= BASE_URL ?>/actualites.php" class="carte-lien">← Retour aux actualités</a></p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
