<?php
require_once __DIR__ . '/config/config.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT e.*, c.nom AS categorie FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id WHERE e.slug = ? AND e.statut = 'publie'");
$stmt->execute([$slug]);
$ev = $stmt->fetch();

if (!$ev) {
    renderSiteError(404, 'Événement introuvable', 'Cet événement n’existe pas ou n’est plus disponible.', BASE_URL . '/evenements.php', 'Voir les événements');
}

$page_titre = $ev['titre'];
$page_description = extraitTexte($ev['description'] ?: $ev['titre'], 160);
$page_og_image = $ev['image'] ?: '/assets/img/social-share.png';
$page_og_image_alt = $ev['titre'];
$evenementImageUrl = rtrim(SITE_URL, '/') . '/' . ltrim($page_og_image, '/');
$dateDebutEvenement = $ev['date_evenement'] . ' ' . ($ev['heure_evenement'] ?: '00:00:00');
$page_structured_data = [[
    '@type' => 'Event',
    'name' => $ev['titre'],
    'description' => $page_description,
    'image' => $evenementImageUrl,
    'startDate' => date(DATE_W3C, strtotime($dateDebutEvenement)),
    'eventStatus' => 'https://schema.org/EventScheduled',
    'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
    'location' => [
        '@type' => 'Place',
        'name' => $ev['lieu'] ?: SITE_NOM,
        'address' => SITE_ADRESSE,
    ],
    'organizer' => ['@id' => rtrim(SITE_URL, '/') . '/#mosquee'],
]];
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / <a href="<?= BASE_URL ?>/evenements.php" style="color:inherit;">Événements</a> / <?= h($ev['titre']) ?></div>
        <?php if ($ev['categorie']): ?><span class="badge"><?= h($ev['categorie']) ?></span><?php endif; ?>
        <h1><?= h($ev['titre']) ?></h1>
        <p style="color:var(--laiton-300); font-family:var(--police-donnee);">
            <span class="meta-icone"><i class="fa-solid fa-calendar-days icone" aria-hidden="true"></i> <?= h(formatDateFr($ev['date_evenement'])) ?></span>
            <?php if ($ev['heure_evenement']): ?> — <?= formatHeure($ev['heure_evenement']) ?><?php endif; ?>
            <?php if ($ev['lieu']): ?> · <span class="meta-icone"><i class="fa-solid fa-location-dot icone" aria-hidden="true"></i> <?= h($ev['lieu']) ?></span><?php endif; ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="conteneur" style="max-width:780px;">
        <?php if ($ev['image']): ?>
        <img src="<?= h(siteAssetUrl($ev['image'])) ?>" alt="<?= h($ev['titre']) ?>" style="border-radius:6px; margin-bottom:32px;">
        <?php endif; ?>
        <div style="font-size:1.05rem;"><?= nl2br(h($ev['description'])) ?></div>
        <p style="margin-top:40px;"><a href="<?= BASE_URL ?>/evenements.php" class="carte-lien">← Retour aux événements</a></p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
