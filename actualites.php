<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'Actualités';

$page = max(1, (int)($_GET['page'] ?? 1));
$parPage = 9;
$offset = ($page - 1) * $parPage;

$total = $pdo->query("SELECT COUNT(*) FROM actualites WHERE statut='publie'")->fetchColumn();
$stmt = $pdo->prepare("SELECT a.*, c.nom AS categorie FROM actualites a LEFT JOIN categories c ON a.categorie_id = c.id WHERE a.statut='publie' ORDER BY a.date_publication DESC LIMIT :limite OFFSET :offset");
$stmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$actus = $stmt->fetchAll();
$totalPages = max(1, ceil($total / $parPage));

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Actualités</div>
        <h1>Actualités &amp; annonces</h1>
    </div>
</section>

<section class="section">
    <div class="conteneur">
        <?php if ($actus): ?>
        <div class="grille grille-3">
            <?php foreach ($actus as $actu): ?>
            <a class="carte" href="<?= BASE_URL ?>/actualite.php?slug=<?= urlencode($actu['slug']) ?>">
                <div class="carte-image" style="background-image:url('<?= h(siteAssetUrl($actu['image'] ?: '/assets/img/placeholder-actu.jpg')) ?>');"></div>
                <div class="carte-corps">
                    <div class="carte-meta">
                        <?php if ($actu['categorie']): ?><span class="badge"><?= h($actu['categorie']) ?></span><?php endif; ?>
                        <span><?= h(formatDateFr($actu['date_publication'])) ?></span>
                    </div>
                    <h3><?= h($actu['titre']) ?></h3>
                    <p class="resume"><?= h($actu['resume'] ?: extraitTexte($actu['contenu'], 110)) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div style="display:flex; gap:10px; justify-content:center; margin-top:40px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="bouton <?= $i === $page ? 'bouton-dore' : 'bouton-emeraude' ?>" style="padding:8px 16px;"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <p>Aucune actualité publiée pour le moment.</p>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
