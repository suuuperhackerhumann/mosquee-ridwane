<?php
require_once __DIR__ . '/config/config.php';

$page_titre = 'Accueil';
$horaires = getHorairesDuJour();

$stmt = $pdo->query("SELECT a.*, c.nom AS categorie FROM actualites a LEFT JOIN categories c ON a.categorie_id = c.id WHERE a.statut='publie' ORDER BY a.date_publication DESC LIMIT 3");
$dernieres_actus = $stmt->fetchAll();

$stmt = $pdo->query("SELECT e.*, c.nom AS categorie FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id WHERE e.statut='publie' AND e.date_evenement >= CURDATE() ORDER BY e.date_evenement ASC LIMIT 1");
$prochain_evenement = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="conteneur">
        <div class="hero-contenu">
            <span class="eyebrow">As-salamu alaykum</span>
            <h1 id="test"><?= h(contenuTitre('accueil_bienvenue', 'Bienvenue à la Mosquée Ridwane')) ?></h1>
            <p class="intro"><?= h(contenuTexte('accueil_presentation')) ?></p>
            <div class="hero-actions">
                <a href="<?= BASE_URL ?>/horaires.php" class="bouton bouton-dore">Voir les horaires de prière</a>
                <a href="<?= BASE_URL ?>/a-propos.php" class="bouton bouton-contour">Découvrir la mosquée</a>
            </div>
        </div>

        <?php if ($horaires): ?>
        <div class="hero-horaires">
            <div class="hero-horaires-titre">
                <span>Horaires du jour</span>
                <span><?= h(formatDateFr(time())) ?></span>
            </div>
            <div class="grille-horaires-hero">
                <div data-heure-priere="<?= formatHeure($horaires['fajr']) ?>">
                    <div class="nom">Fajr</div>
                    <div class="heure"><?= formatHeure($horaires['fajr']) ?></div>
                </div>
                <div data-heure-priere="<?= formatHeure($horaires['dhuhr']) ?>">
                    <div class="nom">Dhuhr</div>
                    <div class="heure"><?= formatHeure($horaires['dhuhr']) ?></div>
                </div>
                <div data-heure-priere="<?= formatHeure($horaires['asr']) ?>">
                    <div class="nom">Asr</div>
                    <div class="heure"><?= formatHeure($horaires['asr']) ?></div>
                </div>
                <div data-heure-priere="<?= formatHeure($horaires['maghrib']) ?>">
                    <div class="nom">Maghrib</div>
                    <div class="heure"><?= formatHeure($horaires['maghrib']) ?></div>
                </div>
                <div data-heure-priere="<?= formatHeure($horaires['isha']) ?>">
                    <div class="nom">Isha</div>
                    <div class="heure"><?= formatHeure($horaires['isha']) ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Présentation courte -->
<section class="section">
    <div class="conteneur grille grille-2" style="align-items:center; gap:56px;">
        <div>
            <span class="eyebrow">Notre mosquée</span>
            <h2><?= h(contenuTitre('apropos_mission', 'Un lieu de foi, de savoir et de fraternité')) ?></h2>
            <p><?= h(extraitTexte(contenuTexte('apropos_mission', "La Mosquée Ridwane accueille chaque jour les fidèles pour les prières, l'enseignement et la vie communautaire, dans un esprit d'ouverture et de service."), 320)) ?></p>
            <a href="<?= BASE_URL ?>/a-propos.php" class="carte-lien">En savoir plus sur la mosquée →</a>
        </div>
        <?php if ($prochain_evenement): ?>
        <div class="carte">
            <div class="carte-corps">
                <div class="carte-meta">
                    <span class="badge">Prochain événement</span>
                </div>
                <h3><?= h($prochain_evenement['titre']) ?></h3>
                <p class="resume"><?= h(extraitTexte($prochain_evenement['description'], 140)) ?></p>
                <p class="resume" style="font-family:var(--police-donnee); font-weight:600; color:var(--emeraude-700);">
                    <span class="meta-icone"><i class="fa-solid fa-calendar-days icone" aria-hidden="true"></i> <?= h(formatDateFr($prochain_evenement['date_evenement'])) ?></span>
                    <?php if ($prochain_evenement['heure_evenement']): ?> — <?= formatHeure($prochain_evenement['heure_evenement']) ?><?php endif; ?>
                    <?php if ($prochain_evenement['lieu']): ?><br><span class="meta-icone"><i class="fa-solid fa-location-dot icone" aria-hidden="true"></i> <?= h($prochain_evenement['lieu']) ?></span><?php endif; ?>
                </p>
                <a href="<?= BASE_URL ?>/evenement.php?slug=<?= urlencode($prochain_evenement['slug']) ?>" class="carte-lien">Voir les détails →</a>
            </div>
        </div>
        <?php else: ?>
        <div class="carte"><div class="carte-corps">
            <h3>Aucun événement à venir pour le moment</h3>
            <p class="resume">Consultez régulièrement cette page pour connaître nos prochaines activités.</p>
        </div></div>
        <?php endif; ?>
    </div>
</section>

<!-- Actualités -->
<section class="section section-alt">
    <div class="conteneur">
        <div class="section-tete">
            <span class="eyebrow">Vie de la mosquée</span>
            <h2>Dernières actualités</h2>
        </div>
        <?php if ($dernieres_actus): ?>
        <div class="grille grille-3">
            <?php foreach ($dernieres_actus as $actu): ?>
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
        <?php else: ?>
        <p>Aucune actualité publiée pour le moment.</p>
        <?php endif; ?>
        <p style="margin-top:28px;"><a href="<?= BASE_URL ?>/actualites.php" class="carte-lien">Voir toutes les actualités →</a></p>
    </div>
</section>

<!-- Enseignements -->
<section class="section">
    <div class="conteneur grille grille-2" style="align-items:center; gap:56px;">
        <div>
            <span class="eyebrow">Apprendre</span>
            <h2>Nos enseignements</h2>
            <p>École coranique, cours de Coran, d'arabe et enseignements religieux pour enfants comme pour adultes.</p>
            <a href="<?= BASE_URL ?>/enseignements.php" class="bouton bouton-emeraude">Découvrir les cours</a>
        </div>
        <div>
            <span class="eyebrow">Accompagner</span>
            <h2>Nos services</h2>
            <p>Nikah, Janazah, Zakat, consultations religieuses : la mosquée est à votre service dans les grandes étapes de la vie.</p>
            <a href="<?= BASE_URL ?>/services.php" class="bouton bouton-emeraude">Voir les services</a>
        </div>
    </div>
</section>

<!-- Dons -->
<section class="section section-emeraude">
    <div class="conteneur" style="text-align:center; max-width:640px; margin:0 auto;">
        <span class="eyebrow" style="justify-content:center;">Soutenir la mosquée</span>
        <h2><?= h(contenuTitre('dons_intro', 'Votre don fait vivre la mosquée')) ?></h2>
        <p style="color:var(--plâtre-100);"><?= h(contenuTexte('dons_intro')) ?></p>
        <a href="<?= BASE_URL ?>/dons.php" class="bouton bouton-dore">Faire un don</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>