<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'Services';
$services = $pdo->query("SELECT * FROM services ORDER BY ordre ASC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Services</div>
        <h1>Services de la mosquée</h1>
        <p style="color:var(--plâtre-100); max-width:560px;">La mosquée accompagne les fidèles dans les grandes étapes de la vie et au quotidien.</p>
    </div>
</section>

<section class="section">
    <div class="conteneur grille grille-2">
        <?php foreach ($services as $s): ?>
        <div class="carte">
            <div class="carte-corps">
                <h3><?= h($s['titre']) ?></h3>
                <p class="resume"><?= h($s['description']) ?></p>
                <a href="<?= BASE_URL ?>/contact.php" class="carte-lien">Contacter la mosquée pour ce service →</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (!$services): ?>
    <p>Les informations sur les services seront bientôt disponibles.</p>
    <?php endif; ?>
    <p style="margin-top:28px; color:var(--encre-600); font-size:0.9rem;">Les demandes de rendez-vous se font actuellement par téléphone ou par le formulaire de contact — un système de prise de rendez-vous en ligne est prévu pour une prochaine version du site.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
