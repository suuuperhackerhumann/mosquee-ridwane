<?php
// $page_titre, $page_description et $fil_ariane (optionnel) sont définis par la page appelante
$page_titre = $page_titre ?? SITE_NOM;
$page_description = $page_description ?? SITE_DESCRIPTION;
$pageDescriptionSeo = mb_substr(trim(strip_tags($page_description)), 0, 300);
$pageMetaTitle = $page_meta_title ?? ($page_titre . ' — ' . SITE_NOM);
$pageOgDescription = $page_og_description ?? $pageDescriptionSeo;
$liveSessionActive = (bool) $pdo->query("SELECT id FROM live_sessions WHERE statut = 'actif' ORDER BY date_debut DESC LIMIT 1")->fetchColumn();
$siteUrlSeo = rtrim(SITE_URL, '/');
$pageScript = basename(parse_url($_SERVER['SCRIPT_NAME'] ?? '/index.php', PHP_URL_PATH) ?: 'index.php');
$pageRoute = pathinfo($pageScript, PATHINFO_FILENAME);
$canonicalUrl = $pageRoute === 'index' ? $siteUrlSeo . '/' : $siteUrlSeo . '/' . rawurlencode($pageRoute);
if (in_array($pageRoute, ['actualite', 'evenement'], true) && isset($_GET['slug']) && is_string($_GET['slug'])) {
    $canonicalUrl .= '?slug=' . rawurlencode($_GET['slug']);
} elseif ($pageRoute === 'actualites' && isset($_GET['page']) && filter_var($_GET['page'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 2]])) {
    $canonicalUrl .= '?page=' . (int) $_GET['page'];
}
$fallbackSocialImage = '/assets/img/social-share.png';
$pageSocialImage = isset($page_og_image) && is_string($page_og_image) ? $page_og_image : $fallbackSocialImage;
$socialImagePath = ltrim($pageSocialImage, '/');
if (
    strpos($pageSocialImage, '//') === 0
    || !preg_match('~^assets/[A-Za-z0-9._/-]+$~', $socialImagePath)
    || strpos($socialImagePath, '..') !== false
    || !is_file(dirname(__DIR__) . '/' . $socialImagePath)
) {
    $socialImagePath = ltrim($fallbackSocialImage, '/');
}
$socialImageUrl = $siteUrlSeo . '/' . implode('/', array_map('rawurlencode', explode('/', $socialImagePath))) . '?v=20261108';
$socialImageAlt = isset($page_og_image_alt) && is_string($page_og_image_alt)
    ? $page_og_image_alt
    : 'Emblème de la Communauté Musulmane de la Riviera Abatta';
$socialImageSize = getimagesize(dirname(__DIR__) . '/' . $socialImagePath);
$socialImageWidth = $socialImageSize[0] ?? 1200;
$socialImageHeight = $socialImageSize[1] ?? 630;
$ogType = isset($page_og_type) && $page_og_type === 'article' ? 'article' : 'website';
$mosqueLogoUrl = $siteUrlSeo . '/assets/img/logo-mosquee.png?v=20261108';
$structuredData = [[
    '@type' => 'Mosque',
    '@id' => $siteUrlSeo . '/#mosquee',
    'name' => SITE_NOM,
    'url' => $siteUrlSeo . '/',
    'image' => $mosqueLogoUrl,
    'logo' => [
        '@type' => 'ImageObject',
        'url' => $mosqueLogoUrl,
    ],
    'description' => SITE_DESCRIPTION,
    'telephone' => SITE_TELEPHONE,
    'hasMap' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(SITE_LAT . ',' . SITE_LNG),
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => SITE_ADRESSE,
        'addressLocality' => 'Abidjan',
        'addressRegion' => 'Abidjan',
        'addressCountry' => 'CI',
    ],
    'geo' => [
        '@type' => 'GeoCoordinates',
        'latitude' => (float) SITE_LAT,
        'longitude' => (float) SITE_LNG,
    ],
    'sameAs' => array_values(array_filter([SITE_FACEBOOK, SITE_INSTAGRAM !== '#' ? SITE_INSTAGRAM : null, SITE_YOUTUBE !== '#' ? SITE_YOUTUBE : null])),
]];
if (!empty($page_structured_data) && is_array($page_structured_data)) {
    $structuredData = array_merge($structuredData, $page_structured_data);
}
$graphData = [
    $structuredData[0],
    [
        '@type' => 'WebSite',
        '@id' => $siteUrlSeo . '/#website',
        'url' => $siteUrlSeo . '/',
        'name' => SITE_NOM,
        'inLanguage' => 'fr-CI',
        'publisher' => ['@id' => $siteUrlSeo . '/#mosquee'],
    ],
];
$graphData = array_merge($graphData, array_slice($structuredData, 1));
$donneesStructurees = [
    '@context' => 'https://schema.org',
    '@graph' => $graphData,
];

$liens_nav = [
    'index'          => 'Accueil',
    'a-propos'       => 'À propos',
    'horaires'       => 'Horaires',
    'actualites'     => 'Actualités',
    'evenements'     => 'Événements',
    'enseignements'  => 'Enseignements',
    'services'       => 'Services',
    'mediatheque'    => 'Médiathèque',
    'discussions'   => 'Discussion',
    'contact'        => 'Contact',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageMetaTitle) ?></title>
<meta name="description" content="<?= h($pageDescriptionSeo) ?>">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<link rel="canonical" href="<?= h($canonicalUrl) ?>">
<meta name="theme-color" content="#0e2d29">
<meta name="site-revision" content="<?= h(publicRevision()) ?>">
<meta name="site-revision-url" content="<?= h(BASE_URL . '/public-revision') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon-32.png?v=20261108">
<link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon-32.png?v=20261108">
<link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/img/apple-touch-icon.png?v=20261108">
<link rel="manifest" href="<?= BASE_URL ?>/site.webmanifest">
<link rel="sitemap" type="application/xml" href="<?= h($siteUrlSeo . '/sitemap') ?>">
<meta property="og:type" content="<?= h($ogType) ?>">
<meta property="og:locale" content="fr_CI">
<meta property="og:site_name" content="<?= h(SITE_NOM) ?>">
<meta property="og:title" content="<?= h($pageMetaTitle) ?>">
<meta property="og:description" content="<?= h($pageOgDescription) ?>">
<meta property="og:url" content="<?= h($canonicalUrl) ?>">
<meta property="og:image" content="<?= h($socialImageUrl) ?>">
<meta property="og:image:secure_url" content="<?= h($socialImageUrl) ?>">
<meta property="og:image:alt" content="<?= h($socialImageAlt) ?>">
<meta property="og:image:type" content="image/<?= strtolower(pathinfo($socialImagePath, PATHINFO_EXTENSION)) === 'jpg' ? 'jpeg' : strtolower(pathinfo($socialImagePath, PATHINFO_EXTENSION)) ?>">
<meta property="og:image:width" content="<?= (int) $socialImageWidth ?>">
<meta property="og:image:height" content="<?= (int) $socialImageHeight ?>">
<?php if ($ogType === 'article' && !empty($page_publication_date)): ?><meta property="article:published_time" content="<?= h(date(DATE_W3C, strtotime($page_publication_date))) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= h($pageMetaTitle) ?>">
<meta name="twitter:description" content="<?= h($pageDescriptionSeo) ?>">
<meta name="twitter:image" content="<?= h($socialImageUrl) ?>">
<meta name="twitter:image:alt" content="<?= h($socialImageAlt) ?>">
<script type="application/ld+json"><?= json_encode($donneesStructurees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Karla:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/accueil-visuel.css">
<style>
/* Sur mobile, le don reste disponible sans recouvrir le contenu. */
@media (max-width: 760px) {
    .don-flottant { position: static; display: flex; width: 100%; border-radius: 0; box-shadow: none; }
}

/* Header toujours visible au scroll (bouton hamburger inclus). */
.site-header {
    position: fixed !important;
    top: 0;
    left: 0;
    right: 0;
    width: 100%;
    z-index: 1000;
}
body { padding-top: 72px; }
.site-header .header-bar { min-height: 72px; }

/* Menu mobile : panneau compact au lieu de plein écran. */
@media (max-width: 1360px) {
    .nav-principale {
        position: fixed;
        top: 72px;
        right: 12px;
        left: auto;
        bottom: auto;
        width: min(320px, calc(100vw - 24px));
        height: auto;
        max-height: calc(100dvh - 84px);
        overflow-y: auto;
        border-radius: 14px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, .25);
    }
    .nav-principale ul {
        flex-direction: column;
        align-items: stretch;
    }
}

/* Informations visibles sous les médias, quelle que soit leur taille. */
.media-carte { min-width: 0; overflow: hidden; border: 1px solid var(--plâtre-200); border-radius: 10px; background: #fffef8; }
.media-carte .carte-media { border-radius: 0; }
.media-carte .carte-media a { display: block; width: 100%; height: 100%; }
.media-carte .carte-media img { width: 100%; height: 100%; object-fit: cover; }
.media-infos { padding: 16px 18px 20px; overflow-wrap: anywhere; }
.media-infos h2 { margin: 0 0 8px; font-size: 1.2rem; }
main > .section .media-infos p { margin: 4px 0 0; font-size: .92rem; line-height: 1.5; }
.media-video-lien { display: flex !important; align-items: center; justify-content: center; gap: 10px; background: var(--mint-950); color: var(--plâtre-50); font-weight: 700; }
</style>
</head>
<body>

<a href="#contenu-principal" class="lien-evitement">Aller au contenu principal</a>

<header class="site-header" id="entete">
    <div class="conteneur header-bar">
        <a href="<?= BASE_URL ?>/" class="logo">
            <img class="logo-marque logo-emplacement" src="<?= BASE_URL ?>/assets/img/logo-mosquee.png?v=20261108" alt="Emblème de la Communauté Musulmane de la Riviera Abatta">
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

        <button type="button" class="menu-toggle" aria-label="Ouvrir le menu" aria-controls="nav-principale" aria-expanded="false">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>
</header>

<a href="<?= BASE_URL ?>/dons" class="bouton bouton-dore don-flottant">
    <i class="fa-solid fa-hand-holding-heart icone" aria-hidden="true"></i> Faire un don
</a>
<?php if ($liveSessionActive): ?>
<a href="<?= BASE_URL ?>/live" class="live-flottant" aria-label="Questions en direct : participer au live">
    <i class="fa-solid fa-tower-broadcast" aria-hidden="true"></i>
    <span>LIVE</span>
</a>
<?php endif; ?>

<main id="contenu-principal">