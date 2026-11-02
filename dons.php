<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'Faire un don';
$projets = $pdo->query("SELECT * FROM dons_projets WHERE statut='actif' ORDER BY ordre ASC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Dons</div>
        <h1><?= h(contenuTitre('dons_intro', 'Faire un don')) ?></h1>
        <p style="color:var(--plâtre-100); max-width:600px;"><?= h(contenuTexte('dons_intro')) ?></p>
    </div>
</section>

<?php if ($projets): ?>
<section class="section">
    <div class="conteneur">
        <div class="section-tete"><span class="eyebrow">Projets en cours</span><h2>Ce que votre don soutient</h2></div>
        <div class="grille grille-3">
            <?php foreach ($projets as $p):
                $pourcentage = $p['objectif'] > 0 ? min(100, round(($p['montant_collecte'] / $p['objectif']) * 100)) : 0;
            ?>
            <div class="carte">
                <div class="carte-image" style="background-image:url('<?= h(siteAssetUrl($p['image'] ?: '/assets/img/placeholder-don.jpg')) ?>');"></div>
                <div class="carte-corps">
                    <h3><?= h($p['titre']) ?></h3>
                    <p class="resume"><?= h(extraitTexte($p['description'], 120)) ?></p>
                    <?php if ($p['objectif']): ?>
                    <div class="barre-progression"><span style="width:<?= $pourcentage ?>%;"></span></div>
                    <p style="font-family:var(--police-donnee); font-size:0.85rem; margin-top:8px;"><?= number_format($p['montant_collecte'], 0, ',', ' ') ?> € collectés sur <?= number_format($p['objectif'], 0, ',', ' ') ?> €</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section section-alt">
    <div class="conteneur grille grille-2" style="gap:56px;">
        <div class="carte">
            <div class="carte-corps">
                <h3>Comment faire un don</h3>
                <p class="resume">Les dons peuvent être effectués directement à la mosquée, par virement bancaire, ou par tout autre moyen indiqué à l'accueil. Contactez-nous pour obtenir nos coordonnées bancaires.</p>
                <a href="<?= BASE_URL ?>/contact.php" class="bouton bouton-emeraude" style="align-self:flex-start; margin-top:12px;">Nous contacter pour faire un don</a>
            </div>
        </div>
        <div class="carte">
            <div class="carte-corps">
                <span class="badge">Aïd al-Fitr</span>
                <h3><?= h(contenuTitre('dons_zakat_fitr')) ?></h3>
                <p class="resume"><?= h(contenuTexte('dons_zakat_fitr')) ?></p>
            </div>
        </div>
    </div>
    <div class="conteneur">
        <p style="margin-top:32px; color:var(--encre-600); font-size:0.9rem;">Le paiement en ligne sécurisé sera proposé dans une prochaine version du site.</p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
