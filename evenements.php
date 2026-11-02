<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'Événements';

$stmt = $pdo->query("SELECT e.*, c.nom AS categorie FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id WHERE e.statut='publie' AND e.date_evenement >= CURDATE() ORDER BY e.date_evenement ASC");
$avenir = $stmt->fetchAll();

$stmt = $pdo->query("SELECT e.*, c.nom AS categorie FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id WHERE e.statut='publie' AND e.date_evenement < CURDATE() ORDER BY e.date_evenement DESC LIMIT 6");
$passes = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Événements</div>
        <h1>Événements</h1>
    </div>
</section>

<section class="section">
    <div class="conteneur">
        <div class="section-tete"><span class="eyebrow">À venir</span><h2>Prochains événements</h2></div>
        <?php if ($avenir): ?>
        <ul class="liste-simple">
            <?php foreach ($avenir as $ev): ?>
            <li>
                <div class="bloc-date">
                    <div class="jour"><?= date('d', strtotime($ev['date_evenement'])) ?></div>
                    <div class="mois"><?= h(moisAbrev($ev['date_evenement'])) ?></div>
                </div>
                <div style="flex:1;">
                    <div class="carte-meta">
                        <?php if ($ev['categorie']): ?><span class="badge"><?= h($ev['categorie']) ?></span><?php endif; ?>
                        <?php if ($ev['heure_evenement']): ?><span><?= formatHeure($ev['heure_evenement']) ?></span><?php endif; ?>
                        <?php if ($ev['lieu']): ?><span>· <?= h($ev['lieu']) ?></span><?php endif; ?>
                    </div>
                    <h3 style="margin-bottom:6px;"><a href="<?= BASE_URL ?>/evenement.php?slug=<?= urlencode($ev['slug']) ?>"><?= h($ev['titre']) ?></a></h3>
                    <p class="resume"><?= h(extraitTexte($ev['description'], 160)) ?></p>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p>Aucun événement à venir pour le moment. Revenez bientôt !</p>
        <?php endif; ?>
    </div>
</section>

<?php if ($passes): ?>
<section class="section section-alt">
    <div class="conteneur">
        <div class="section-tete"><span class="eyebrow">Souvenirs</span><h2>Événements passés</h2></div>
        <div class="grille grille-3">
            <?php foreach ($passes as $ev): ?>
            <a class="carte" href="<?= BASE_URL ?>/evenement.php?slug=<?= urlencode($ev['slug']) ?>">
                <div class="carte-image" style="background-image:url('<?= h(siteAssetUrl($ev['image'] ?: '/assets/img/placeholder-evenement.jpg')) ?>');"></div>
                <div class="carte-corps">
                    <div class="carte-meta"><span><?= h(formatDateFr($ev['date_evenement'])) ?></span></div>
                    <h3><?= h($ev['titre']) ?></h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
