<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'À propos';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / À propos</div>
        <h1>À propos de la Mosquée Ridwane</h1>
    </div>
</section>

<section class="section">
    <div class="conteneur grille grille-2" style="gap:56px;">
        <div>
            <span class="eyebrow">Notre histoire</span>
            <h2><?= h(contenuTitre('apropos_histoire')) ?></h2>
            <p><?= nl2br(h(contenuTexte('apropos_histoire'))) ?></p>
        </div>
        <div>
            <span class="eyebrow">Notre mission</span>
            <h2><?= h(contenuTitre('apropos_mission')) ?></h2>
            <p><?= nl2br(h(contenuTexte('apropos_mission'))) ?></p>
        </div>
    </div>
</section>

<svg class="motif-etoile" viewBox="0 0 40 40"><path d="M20 3 L23.5 14.5 L35 14.5 L25.7 21.5 L29.2 33 L20 26 L10.8 33 L14.3 21.5 L5 14.5 L16.5 14.5 Z"/></svg>

<section class="section section-alt">
    <div class="conteneur grille grille-2" style="gap:56px;">
        <div>
            <span class="eyebrow">L'imam</span>
            <h2><?= h(contenuTitre('apropos_imam')) ?></h2>
            <p><?= nl2br(h(contenuTexte('apropos_imam'))) ?></p>
        </div>
        <div>
            <span class="eyebrow">L'équipe</span>
            <h2><?= h(contenuTitre('apropos_equipe')) ?></h2>
            <p><?= nl2br(h(contenuTexte('apropos_equipe'))) ?></p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
