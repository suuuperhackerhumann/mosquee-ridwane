<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'À propos';

$instances = $pdo->query('SELECT * FROM instances_dirigeantes ORDER BY id')->fetchAll();
$instancesParGroupe = [];
foreach ($instances as $instance) {
    $instancesParGroupe[$instance['groupe']][] = $instance;
}

// Définition des instances : titre, icône, description (et sous-groupes éventuels)
$instancesConfig = [
    'conseil_imamal' => [
        'titre' => 'Conseil imamal',
        'icone' => 'fa-mosque',
        'description' => "Il guide la communauté sur le plan religieux, dirige les prières et veille à l'enseignement de l'islam.",
    ],
    'comite_gestion' => [
        'titre' => 'Comité de gestion',
        'icone' => 'fa-people-roof',
        'description' => "Il assure l'administration, les finances et l'entretien de la mosquée au quotidien.",
    ],
    'conseil_sages' => [
        'titre' => 'Conseil des sages',
        'icone' => 'fa-scale-balanced',
        'description' => "Il conseille, arbitre et accompagne les décisions importantes de la communauté.",
    ],
    'jeunes' => [
        'titre' => 'Comité des jeunes',
        'icone' => 'fa-users',
        'description' => "Il mobilise la jeunesse autour des activités, de l'entraide et de la vie de la mosquée.",
        'sous_groupes' => [
            'jeunes_filles'  => 'Les filles',
            'jeunes_garcons' => 'Les garçons',
        ],
    ],
];

if (!function_exists('carteInstance')) {
    function carteInstance(array $instance): void { ?>
        <article class="instance-carte">
            <?php if ($instance['photo']): ?>
                <img src="<?= BASE_URL . h($instance['photo']) ?>" alt="Portrait de <?= h($instance['nom']) ?>" loading="lazy">
            <?php else: ?>
                <div class="instance-photo-vide" aria-hidden="true"><i class="fa-regular fa-user"></i></div>
            <?php endif; ?>
            <div class="instance-contenu">
                <h4><?= h($instance['nom']) ?></h4>
                <?php if ($instance['fonction']): ?>
                    <p class="instance-fonction"><?= h($instance['fonction']) ?></p>
                <?php endif; ?>
                <?php if ($instance['description']): ?>
                    <p class="instance-desc"><?= nl2br(h($instance['description'])) ?></p>
                <?php endif; ?>
            </div>
        </article>
    <?php }
}

if (!function_exists('nombreMembres')) {
    function nombreMembres(array $instancesParGroupe, array $cfg, string $cle): int {
        if (!empty($cfg['sous_groupes'])) {
            $total = 0;
            foreach (array_keys($cfg['sous_groupes']) as $g) {
                $total += count($instancesParGroupe[$g] ?? []);
            }
            return $total;
        }
        return count($instancesParGroupe[$cle] ?? []);
    }
}

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

<section class="section section-alt instances-section">
    <div class="conteneur">
        <span class="eyebrow">Organisation de la mosquée</span>
        <h2 class="instances-titre">Les instances dirigeantes</h2>

        <!-- Navigation rapide -->
        <nav class="instances-nav" aria-label="Instances dirigeantes">
            <?php foreach ($instancesConfig as $cle => $cfg): ?>
                <a href="#instance-<?= h($cle) ?>"><i class="fa-solid <?= h($cfg['icone']) ?>"></i> <?= h($cfg['titre']) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php foreach ($instancesConfig as $cle => $cfg):
            $nb = nombreMembres($instancesParGroupe, $cfg, $cle); ?>
            <section class="instance-bloc" id="instance-<?= h($cle) ?>">
                <header class="instance-bloc-entete">
                    <div class="instance-bloc-icone"><i class="fa-solid <?= h($cfg['icone']) ?>"></i></div>
                    <div class="instance-bloc-titres">
                        <h3><?= h($cfg['titre']) ?></h3>
                        <p><?= h($cfg['description']) ?></p>
                    </div>
                    <span class="instance-bloc-badge"><?= $nb ?> membre<?= $nb > 1 ? 's' : '' ?></span>
                </header>

                <div class="instance-bloc-corps">
                    <?php if (!empty($cfg['sous_groupes'])): ?>
                        <?php foreach ($cfg['sous_groupes'] as $sg => $sgTitre): ?>
                            <div class="instances-sous-groupe">
                                <h4 class="sous-groupe-titre"><?= h($sgTitre) ?></h4>
                                <div class="instances-grille">
                                    <?php foreach ($instancesParGroupe[$sg] ?? [] as $instance): ?>
                                        <?php carteInstance($instance); ?>
                                    <?php endforeach; ?>
                                    <?php if (empty($instancesParGroupe[$sg])): ?>
                                        <p class="instance-vide">Aucun membre renseigné pour le moment.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="instances-grille">
                            <?php foreach ($instancesParGroupe[$cle] ?? [] as $instance): ?>
                                <?php carteInstance($instance); ?>
                            <?php endforeach; ?>
                            <?php if (empty($instancesParGroupe[$cle])): ?>
                                <p class="instance-vide">Aucun membre renseigné pour le moment.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>