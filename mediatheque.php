<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'Médiathèque';

$albumFiltre = $_GET['album'] ?? '';
$sql = "SELECT * FROM mediatheque";
$params = [];
if ($albumFiltre !== '') {
    $sql .= " WHERE album = ?";
    $params[] = $albumFiltre;
}
$sql .= " ORDER BY date_ajout DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$medias = $stmt->fetchAll();

$albums = $pdo->query("SELECT DISTINCT album FROM mediatheque WHERE album IS NOT NULL AND album <> '' ORDER BY album")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Médiathèque</div>
        <h1>Médiathèque</h1>
        <p style="color:var(--plâtre-100);">Photos et vidéos des événements et de la vie de la mosquée.</p>
    </div>
</section>

<section class="section">
    <div class="conteneur">
        <?php if ($albums): ?>
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:32px;">
            <a href="<?= BASE_URL ?>/mediatheque.php" class="bouton <?= $albumFiltre === '' ? 'bouton-dore' : 'bouton-emeraude' ?>" style="padding:8px 16px;">Tout</a>
            <?php foreach ($albums as $album): ?>
            <a href="?album=<?= urlencode($album) ?>" class="bouton <?= $albumFiltre === $album ? 'bouton-dore' : 'bouton-emeraude' ?>" style="padding:8px 16px;"><?= h($album) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($medias): ?>
        <div class="grille grille-4">
            <?php foreach ($medias as $m): ?>
            <div class="carte-media">
                <?php if ($m['type'] === 'photo'): ?>
                <img src="<?= h(siteAssetUrl($m['fichier'])) ?>" alt="<?= h($m['titre']) ?>" loading="lazy">
                <?php else: ?>
                <a href="<?= h(siteAssetUrl($m['fichier'])) ?>" target="_blank" rel="noopener noreferrer" style="position:relative; display:block; height:100%;">
                    <img src="<?= h(siteAssetUrl($m['fichier'])) ?>" alt="<?= h($m['titre']) ?>" style="height:100%; object-fit:cover;">
                    <span style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(13,43,38,0.35); color:#fff; font-size:2rem;">▶</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p>Aucun contenu disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
