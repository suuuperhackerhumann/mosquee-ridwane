<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'Enseignements';
$enseignements = $pdo->query("SELECT * FROM enseignements ORDER BY ordre ASC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Enseignements</div>
        <h1>Enseignements</h1>
        <p style="color:var(--plâtre-100); max-width:560px;">École coranique, cours de Coran, d'arabe et enseignements religieux, pour les enfants comme pour les adultes.</p>
    </div>
</section>

<section class="section">
    <div class="conteneur grille grille-2">
        <?php foreach ($enseignements as $ens): ?>
        <div class="carte">
            <div class="carte-corps">
                <div class="carte-meta"><span class="badge"><?= h($ens['public_cible']) ?></span></div>
                <h3><?= h($ens['titre']) ?></h3>
                <p class="resume"><?= h($ens['description']) ?></p>
                <?php if ($ens['horaires']): ?>
                <p class="meta-icone" style="font-family:var(--police-donnee); font-weight:600; color:var(--mint-700); margin-top:auto;"><i class="fa-solid fa-clock icone" aria-hidden="true"></i> <?= h($ens['horaires']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (!$enseignements): ?>
    <p>Les informations sur les enseignements seront bientôt disponibles.</p>
    <?php endif; ?>

    <div class="carte" style="margin-top:36px;">
        <div class="carte-corps" style="text-align:center;">
            <h3>Une question sur nos programmes ?</h3>
            <p class="resume">Contactez-nous pour connaître les modalités d'inscription et les places disponibles.</p>
            <a href="<?= BASE_URL ?>/contact.php" class="bouton bouton-emeraude" style="align-self:center;">Nous contacter</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
