<?php
require_once __DIR__ . '/config/config.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT e.*, c.nom AS categorie FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id WHERE e.slug = ? AND e.statut = 'publie'");
$stmt->execute([$slug]);
$ev = $stmt->fetch();

if (!$ev) {
    http_response_code(404);
    $page_titre = 'Événement introuvable';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="section conteneur"><h1>Événement introuvable</h1><p><a href="' . h(BASE_URL) . '/evenements.php" class="carte-lien">Retour aux événements →</a></p></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_titre = $ev['titre'];
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
