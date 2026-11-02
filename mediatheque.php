<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'Médiathèque';
$page_description = 'Photos et vidéos des événements et de la vie de la communauté de la Mosquée Ridwane à Riviera Abatta, Abidjan.';

$albumFiltre = $_GET['album'] ?? '';
if (!is_string($albumFiltre)) $albumFiltre = '';
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
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:32px;" aria-label="Filtrer par album">
            <a href="<?= BASE_URL ?>/mediatheque.php" class="bouton <?= $albumFiltre === '' ? 'bouton-dore' : 'bouton-emeraude' ?>"<?= $albumFiltre === '' ? ' aria-current="page"' : '' ?>>Tout</a>
            <?php foreach ($albums as $album): ?>
            <a href="?album=<?= urlencode($album) ?>" class="bouton <?= $albumFiltre === $album ? 'bouton-dore' : 'bouton-emeraude' ?>"<?= $albumFiltre === $album ? ' aria-current="page"' : '' ?>><?= h($album) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($medias): ?>
        <div class="grille grille-4">
            <?php foreach ($medias as $m): ?>
            <?php
                $titreMedia = trim($m['titre'] ?? '') ?: ($m['type'] === 'photo' ? 'Photo sans titre' : 'Vidéo sans titre');
                $fichierMedia = siteAssetUrl($m['fichier']);
                $videoValide = $m['type'] === 'video' && preg_match('~^https?://~i', $m['fichier']);
                $extensionVideo = strtolower(pathinfo($m['fichier'], PATHINFO_EXTENSION));
                $typesVideo = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogv' => 'video/ogg', 'ogg' => 'video/ogg'];
                $videoLocale = $m['type'] === 'video'
                    && strpos($m['fichier'], '/assets/uploads/') === 0
                    && isset($typesVideo[$extensionVideo])
                    && is_file(__DIR__ . '/assets/uploads/' . basename($m['fichier']));
            ?>
            <article class="media-carte">
                <div class="carte-media">
                    <?php if ($m['type'] === 'photo'): ?>
                    <a href="<?= h($fichierMedia) ?>" target="_blank" rel="noopener noreferrer" aria-label="Voir la photo en grand : <?= h($titreMedia) ?> (nouvel onglet)">
                        <img src="<?= h($fichierMedia) ?>" alt="<?= h($titreMedia) ?>" loading="lazy">
                    </a>
                    <?php elseif ($videoValide): ?>
                    <a class="media-video-lien" href="<?= h($fichierMedia) ?>" target="_blank" rel="noopener noreferrer" aria-label="Voir la vidéo : <?= h($titreMedia) ?> (nouvel onglet)"><span aria-hidden="true">▶</span> Voir la vidéo</a>
                    <?php elseif ($videoLocale): ?>
                    <video controls preload="metadata" playsinline aria-label="<?= h($titreMedia) ?>">
                        <source src="<?= h($fichierMedia) ?>" type="<?= h($typesVideo[$extensionVideo]) ?>">
                        Votre navigateur ne prend pas en charge la lecture de cette vidéo.
                    </video>
                    <?php else: ?>
                    <span class="media-video-lien" style="height:100%;">Vidéo indisponible</span>
                    <?php endif; ?>
                </div>
                <div class="media-infos">
                    <h2><?= h($titreMedia) ?></h2>
                    <?php if (!empty($m['album'])): ?><p>Album : <?= h($m['album']) ?></p><?php endif; ?>
                    <?php if (!empty($m['date_ajout'])): ?><p>Ajouté le <?= h(formatDateFr($m['date_ajout'])) ?></p><?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p>Aucun contenu disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
