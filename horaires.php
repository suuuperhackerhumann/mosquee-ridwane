<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'Horaires de prière';
$horaires = getHorairesDuJour();
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Horaires de prière</div>
        <h1>Horaires de prière</h1>
    </div>
</section>

<section class="section">
    <div class="conteneur">
        <?php if ($horaires): ?>
        <p class="eyebrow"><?= h(formatDateFr($horaires['date_jour'])) ?></p>
        <table class="tableau-horaires">
            <thead>
                <tr><th>Prière</th><th>Heure</th></tr>
            </thead>
            <tbody>
                <tr><td>Fajr</td><td class="heure" data-heure-priere="<?= formatHeure($horaires['fajr']) ?>"><?= formatHeure($horaires['fajr']) ?></td></tr>
                <tr><td>Lever du soleil (Shuruq)</td><td class="heure"><?= formatHeure($horaires['lever_soleil']) ?></td></tr>
                <tr><td>Dhuhr</td><td class="heure" data-heure-priere="<?= formatHeure($horaires['dhuhr']) ?>"><?= formatHeure($horaires['dhuhr']) ?></td></tr>
                <tr><td>Asr</td><td class="heure" data-heure-priere="<?= formatHeure($horaires['asr']) ?>"><?= formatHeure($horaires['asr']) ?></td></tr>
                <tr><td>Maghrib</td><td class="heure" data-heure-priere="<?= formatHeure($horaires['maghrib']) ?>"><?= formatHeure($horaires['maghrib']) ?></td></tr>
                <tr><td>Isha</td><td class="heure" data-heure-priere="<?= formatHeure($horaires['isha']) ?>"><?= formatHeure($horaires['isha']) ?></td></tr>
            </tbody>
        </table>
        <?php else: ?>
        <p>Les horaires n'ont pas encore été renseignés. Merci de repasser bientôt.</p>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt">
    <div class="conteneur grille grille-2" style="gap:56px;">
        <div class="carte">
            <div class="carte-corps">
                <span class="badge">Vendredi</span>
                <h3><?= h(contenuTitre('horaires_jumuah')) ?></h3>
                <p class="resume"><?= nl2br(h(contenuTexte('horaires_jumuah'))) ?></p>
                <?php if ($horaires && $horaires['jumuah']): ?>
                <p style="font-family:var(--police-donnee); font-weight:700; font-size:1.2rem; color:var(--emeraude-700);">Khutbah : <?= formatHeure($horaires['jumuah']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="carte">
            <div class="carte-corps">
                <span class="badge">Ramadan</span>
                <h3><?= h(contenuTitre('horaires_ramadan')) ?></h3>
                <p class="resume"><?= nl2br(h(contenuTexte('horaires_ramadan'))) ?></p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
