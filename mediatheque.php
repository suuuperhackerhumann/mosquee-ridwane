<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'Médiathèque';
$page_description = 'Photos et vidéos des événements et de la vie de la communauté de la Mosquée Ridwane à Riviera Abatta, Abidjan.';

/* ---------- Filtres ---------- */
$albumFiltre = $_GET['album'] ?? '';
if (!is_string($albumFiltre)) $albumFiltre = '';
$typeFiltre = $_GET['type'] ?? '';
if (!is_string($typeFiltre) || !in_array($typeFiltre, ['photo', 'video'], true)) $typeFiltre = '';

$sql = "SELECT * FROM mediatheque";
$params = [];
if ($albumFiltre !== '') {
    $sql .= " WHERE album = ?";
    $params[] = $albumFiltre;
}
$sql .= " ORDER BY date_ajout DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tousMedias = $stmt->fetchAll();

$albums = $pdo->query("SELECT DISTINCT album FROM mediatheque WHERE album IS NOT NULL AND album <> '' ORDER BY album")->fetchAll(PDO::FETCH_COLUMN);

/* ---------- Séparer photos et vidéos ---------- */
$photos = [];
$videos = [];
foreach ($tousMedias as $m) {
    if ($m['type'] === 'photo') $photos[] = $m;
    elseif ($m['type'] === 'video') $videos[] = $m;
}
$nbPhotos = count($photos);
$nbVideos = count($videos);
if ($typeFiltre === 'photo') $videos = [];
elseif ($typeFiltre === 'video') $photos = [];

/* Combien d'éléments visibles au départ (le reste s'affiche avec « Afficher plus ») */
$limitePhotos = 9;
$limiteVideos = 6;

/* ---------- Aides ---------- */
$lienMedia = function (string $album, string $type): string {
    $q = array_filter(['album' => $album, 'type' => $type], fn($v) => $v !== '');
    return BASE_URL . '/mediatheque.php' . ($q ? '?' . http_build_query($q) : '');
};

$infosVideo = function (array $m): array {
    $fichier = (string)($m['fichier'] ?? '');
    $r = ['embed' => null, 'miniature' => null, 'locale' => false, 'externe' => false];
    $r['externe'] = (bool)preg_match('~^https?://~i', $fichier);

    if ($r['externe']) {
        $urlVideo = parse_url($fichier) ?: [];
        $hoteVideo = strtolower($urlVideo['host'] ?? '');
        $cheminVideo = trim($urlVideo['path'] ?? '', '/');
        $hotesYouTube = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com'];
        if (in_array($hoteVideo, $hotesYouTube, true)) {
            $segmentsVideo = explode('/', $cheminVideo);
            if ($hoteVideo === 'youtu.be' || $hoteVideo === 'www.youtu.be') {
                $idVideo = $segmentsVideo[0] ?? '';
            } elseif (in_array($segmentsVideo[0] ?? '', ['embed', 'live', 'shorts', 'v'], true)) {
                $idVideo = $segmentsVideo[1] ?? '';
            } else {
                parse_str($urlVideo['query'] ?? '', $parametresVideo);
                $idVideo = $parametresVideo['v'] ?? '';
            }
            if (is_string($idVideo) && preg_match('/^[A-Za-z0-9_-]{11}$/', $idVideo)) {
                $r['embed'] = 'https://www.youtube-nocookie.com/embed/' . $idVideo . '?autoplay=1';
                $r['miniature'] = 'https://i.ytimg.com/vi/' . $idVideo . '/hqdefault.jpg';
            }
        } elseif (in_array($hoteVideo, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            if (preg_match('~(?:^|/)(\d+)(?:/|$)~', $cheminVideo, $correspondanceVideo)) {
                $r['embed'] = 'https://player.vimeo.com/video/' . $correspondanceVideo[1] . '?autoplay=1';
            }
        }
    }

    $extensionVideo = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));
    $typesVideo = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogv' => 'video/ogg', 'ogg' => 'video/ogg'];
    $r['locale'] = strpos($fichier, '/assets/uploads/') === 0
        && isset($typesVideo[$extensionVideo])
        && is_file(__DIR__ . '/assets/uploads/' . basename($fichier));

    return $r;
};

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ===== Médiathèque : mise en page aérée =====
   Ajuste ces 5 variables pour les faire coller exactement à ta palette. */
.mediatheque{
    --mt-encre:#17251f;
    --mt-emeraude:#0f4c3a;
    --mt-dore:#b08d3c;
    --mt-bord:#e3ddcf;
    --mt-doux:#5b6a63;
    --mt-rayon:14px;
    --mt-ease:cubic-bezier(.16,1,.3,1);
}

/* --- Barre de filtres --- */
.mt-barre{display:flex;flex-direction:column;gap:14px;margin-bottom:clamp(28px,5vw,52px)}
.mt-onglets{display:inline-flex;align-self:flex-start;gap:2px;padding:4px;background:#fff;border:1px solid var(--mt-bord);border-radius:999px}
.mt-onglet{display:inline-flex;align-items:center;gap:8px;min-height:44px;padding:0 18px;border-radius:999px;color:var(--mt-encre);font-weight:600;text-decoration:none;transition:background-color .2s ease-out,color .2s ease-out}
.mt-onglet:hover{background:#f3efe4}
.mt-onglet[aria-current="page"]{background:var(--mt-emeraude);color:#fff}
.mt-onglet .mt-n{font-size:.8125rem;font-weight:500;opacity:.7}
.mt-albums{display:flex;gap:8px;overflow-x:auto;padding:2px 2px 8px;margin:0 -2px;scrollbar-width:thin;-webkit-overflow-scrolling:touch}
.mt-chip{flex:0 0 auto;display:inline-flex;align-items:center;min-height:44px;padding:0 16px;border:1px solid var(--mt-bord);border-radius:999px;background:#fff;color:var(--mt-encre);font-size:.9375rem;text-decoration:none;transition:border-color .2s ease-out,background-color .2s ease-out}
.mt-chip:hover{border-color:var(--mt-dore)}
.mt-chip[aria-current="page"]{background:#f5ecd4;border-color:var(--mt-dore);font-weight:600}
.mt-onglet:focus-visible,.mt-chip:focus-visible,.mt-plus:focus-visible{outline:3px solid var(--mt-dore);outline-offset:2px}

/* --- Blocs Photos / Vidéos --- */
.mt-bloc + .mt-bloc{margin-top:clamp(56px,9vw,96px)}
.mt-bloc-tete{display:flex;align-items:center;gap:12px;margin-bottom:clamp(16px,3vw,24px)}
.mt-bloc-tete h2{margin:0;font-size:clamp(1.375rem,1.1rem + 1vw,1.875rem);line-height:1.2}
.mt-bloc-tete i{color:var(--mt-dore);font-size:1.25rem}
.mt-compte{margin-left:auto;color:var(--mt-doux);font-size:.9375rem}

/* --- Cartes (neutralise les anciens styles de .media-carte) --- */
.mediatheque .mt-carte{position:relative;margin:0;padding:0;border:0;box-shadow:none;transform:none;overflow:hidden;border-radius:var(--mt-rayon);background:#e8e3d6}
.mediatheque .mt-carte:hover{transform:none;box-shadow:none}

/* --- Galerie photos --- */
.mt-galerie{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.mt-photo{aspect-ratio:4/3}
.mt-vedette{grid-column:span 2;aspect-ratio:16/10}
.mediatheque .mt-photo .carte-media{position:absolute;inset:0;width:auto;height:auto;aspect-ratio:auto;margin:0;padding:0}
.mt-photo .media-photo-ouvrir{display:block;width:100%;height:100%}
.mt-photo img{display:block;width:100%;height:100%;object-fit:cover;transition:transform .6s var(--mt-ease)}
.mt-photo:hover img{transform:scale(1.04)}
.mt-photo .media-photo-ouvrir:focus-visible{outline:3px solid var(--mt-dore);outline-offset:-3px}

/* Légende discrète, affichée au survol / au focus (toujours visible au tactile) */
.mediatheque .mt-photo .media-infos{position:absolute;left:0;right:0;bottom:0;margin:0;padding:34px 14px 12px;background:linear-gradient(to top,rgba(10,22,17,.72),rgba(10,22,17,0));color:#fff;pointer-events:none;opacity:0;transform:translateY(6px);transition:opacity .25s ease-out,transform .3s var(--mt-ease)}
.mt-photo:hover .media-infos,.mt-photo:focus-within .media-infos{opacity:1;transform:none}
.mt-photo .media-infos h3{margin:0;color:#fff;font-size:.9375rem;font-weight:600;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.mt-photo .media-infos p{display:none}
.mt-sans-titre .media-infos{display:none !important}
@media (hover:none){.mediatheque .mt-photo .media-infos{opacity:1;transform:none}}

@media (min-width:640px){
    .mt-galerie{grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
    .mt-vedette{grid-column:auto;aspect-ratio:4/3}
}
@media (min-width:900px){
    .mt-galerie{gap:16px;grid-auto-rows:clamp(11rem,17vw,15rem)}
    .mt-photo{aspect-ratio:auto}
    .mt-vedette{grid-column:span 2;grid-row:span 2;aspect-ratio:auto}
    .mt-vedette .media-infos h3{font-size:1.125rem}
}

/* --- Vidéos --- */
.mt-videos{display:grid;grid-template-columns:1fr;gap:28px 20px}
@media (min-width:640px){.mt-videos{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (min-width:1000px){.mt-videos{grid-template-columns:repeat(3,minmax(0,1fr));gap:36px 24px}}
.mediatheque .mt-video{overflow:visible;background:transparent;border-radius:0}
.mediatheque .mt-video .carte-media{position:relative;width:auto;height:auto;margin:0;padding:0;aspect-ratio:16/9;overflow:hidden;border-radius:var(--mt-rayon);background:var(--mt-emeraude)}
.mt-video-bouton{position:absolute;inset:0;display:block;width:100%;height:100%;padding:0;border:0;background:none;color:#fff;cursor:pointer;text-decoration:none}
.mt-video-bouton img,.mt-video-bouton video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;pointer-events:none;transition:transform .6s var(--mt-ease)}
.mt-video-bouton::after{content:"";position:absolute;inset:0;background:rgba(10,22,17,.18);transition:background-color .3s ease-out}
.mt-video-bouton:hover::after{background:rgba(10,22,17,.06)}
.mt-video-bouton:hover img,.mt-video-bouton:hover video{transform:scale(1.03)}
.mt-video-bouton:focus-visible{outline:3px solid var(--mt-dore);outline-offset:-3px}
.mt-play{position:absolute;z-index:1;left:50%;top:50%;translate:-50% -50%;display:grid;place-items:center;width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,.95);color:var(--mt-emeraude);font-size:1.25rem;box-shadow:0 6px 20px rgba(0,0,0,.25);transition:transform .3s var(--mt-ease)}
.mt-play i{margin-left:3px}
.mt-video-bouton:hover .mt-play{transform:scale(1.08)}
.mt-video-bouton:active .mt-play{transform:scale(.96)}
.mt-video-indispo{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:#fff;opacity:.85;font-size:.9375rem}
.mt-video-indispo i{font-size:1.5rem}
.mediatheque .mt-video .media-infos{margin:0;padding:12px 2px 0;background:none;border:0}
.mt-video .media-infos h3{margin:0 0 4px;font-size:1.0625rem;line-height:1.35;color:var(--mt-encre)}
.mt-video .media-infos p{margin:0;color:var(--mt-doux);font-size:.875rem;line-height:1.5}

/* --- Afficher plus --- */
.mt-plus-zone{display:flex;flex-direction:column;align-items:center;gap:10px;margin-top:clamp(24px,4vw,36px)}
.mt-plus-zone[hidden]{display:none}
.mt-plus{display:inline-flex;align-items:center;gap:10px;min-height:48px;padding:0 26px;border:1.5px solid var(--mt-emeraude);border-radius:999px;background:transparent;color:var(--mt-emeraude);font:inherit;font-weight:600;cursor:pointer;transition:background-color .2s ease-out,color .2s ease-out,transform .15s ease-out}
.mt-plus:hover{background:var(--mt-emeraude);color:#fff}
.mt-plus:active{transform:scale(.97)}
.mt-progres{margin:0;color:var(--mt-doux);font-size:.875rem}
.mt-pret .mt-cache{display:none}
.mt-apparait{animation:mt-entree .45s var(--mt-ease) both}
@keyframes mt-entree{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}

/* --- État vide --- */
.mt-vide{max-width:30rem;margin:clamp(24px,6vw,64px) auto;text-align:center}
.mt-vide i{font-size:2.5rem;color:var(--mt-dore);margin-bottom:16px}
.mt-vide h2{margin:0 0 8px;font-size:1.375rem}
.mt-vide p{margin:0 0 24px;color:var(--mt-doux)}

@media (prefers-reduced-motion:reduce){
    .mt-photo img,.mt-video-bouton img,.mt-video-bouton video,.mt-play,.mt-plus,.mediatheque .mt-photo .media-infos{transition:none}
    .mt-apparait{animation:none}
}
</style>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Médiathèque</div>
        <h1>Médiathèque</h1>
        <p style="color:var(--plâtre-100);">Photos et vidéos des événements et de la vie de la mosquée.</p>
    </div>
</section>

<section class="section mediatheque">
    <div class="conteneur">

        <?php if ($nbPhotos > 0 && $nbVideos > 0 || $albums): ?>
        <div class="mt-barre">
            <?php if ($nbPhotos > 0 && $nbVideos > 0): ?>
            <nav class="mt-onglets" aria-label="Type de contenu">
                <a class="mt-onglet" href="<?= h($lienMedia($albumFiltre, '')) ?>"<?= $typeFiltre === '' ? ' aria-current="page"' : '' ?>>Tout <span class="mt-n"><?= $nbPhotos + $nbVideos ?></span></a>
                <a class="mt-onglet" href="<?= h($lienMedia($albumFiltre, 'photo')) ?>"<?= $typeFiltre === 'photo' ? ' aria-current="page"' : '' ?>>Photos <span class="mt-n"><?= $nbPhotos ?></span></a>
                <a class="mt-onglet" href="<?= h($lienMedia($albumFiltre, 'video')) ?>"<?= $typeFiltre === 'video' ? ' aria-current="page"' : '' ?>>Vidéos <span class="mt-n"><?= $nbVideos ?></span></a>
            </nav>
            <?php endif; ?>

            <?php if ($albums): ?>
            <nav class="mt-albums" aria-label="Filtrer par album">
                <a class="mt-chip" href="<?= h($lienMedia('', $typeFiltre)) ?>"<?= $albumFiltre === '' ? ' aria-current="page"' : '' ?>>Tous les albums</a>
                <?php foreach ($albums as $album): ?>
                <a class="mt-chip" href="<?= h($lienMedia($album, $typeFiltre)) ?>"<?= $albumFiltre === $album ? ' aria-current="page"' : '' ?>><?= h($album) ?></a>
                <?php endforeach; ?>
            </nav>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($photos): ?>
        <div class="mt-bloc" data-mt-bloc>
            <div class="mt-bloc-tete">
                <i class="fa-solid fa-images" aria-hidden="true"></i>
                <h2>Photos</h2>
                <span class="mt-compte"><?= count($photos) ?> photo<?= count($photos) > 1 ? 's' : '' ?></span>
            </div>

            <div class="mt-galerie">
                <?php foreach ($photos as $i => $m): ?>
                <?php
                    $titreBrut = trim($m['titre'] ?? '');
                    $sansTitre = $titreBrut === '';
                    $titreMedia = $sansTitre ? 'Photo sans titre' : $titreBrut;
                    $fichierMedia = siteAssetUrl($m['fichier']);
                    $classes = 'media-carte mt-carte mt-photo';
                    if ($i === 0 && count($photos) >= 5) $classes .= ' mt-vedette';
                    if ($sansTitre) $classes .= ' mt-sans-titre';
                    if ($i >= $limitePhotos) $classes .= ' mt-cache';
                ?>
                <article class="<?= $classes ?>">
                    <div class="carte-media">
                        <a class="media-photo-ouvrir" href="<?= h($fichierMedia) ?>" aria-haspopup="dialog" aria-controls="media-preview-dialog" aria-label="Agrandir la photo : <?= h($titreMedia) ?>">
                            <img src="<?= h($fichierMedia) ?>" alt="<?= h($titreMedia) ?>" loading="lazy" decoding="async">
                        </a>
                    </div>
                    <div class="media-infos">
                        <h3><?= h($titreMedia) ?></h3>
                        <?php if (!empty($m['album'])): ?><p>Album : <?= h($m['album']) ?></p><?php endif; ?>
                        <?php if (!empty($m['date_ajout'])): ?><p>Ajouté le <?= h(formatDateFr($m['date_ajout'])) ?></p><?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <?php if (count($photos) > $limitePhotos): ?>
            <div class="mt-plus-zone">
                <button class="mt-plus" type="button" data-mt-plus data-pas="9">
                    Afficher plus de photos <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
                <p class="mt-progres" role="status" aria-live="polite"><span data-mt-vues><?= $limitePhotos ?></span> sur <?= count($photos) ?> affichées</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($videos): ?>
        <div class="mt-bloc" data-mt-bloc>
            <div class="mt-bloc-tete">
                <i class="fa-solid fa-film" aria-hidden="true"></i>
                <h2>Vidéos</h2>
                <span class="mt-compte"><?= count($videos) ?> vidéo<?= count($videos) > 1 ? 's' : '' ?></span>
            </div>

            <div class="mt-videos">
                <?php foreach ($videos as $i => $m): ?>
                <?php
                    $titreBrut = trim($m['titre'] ?? '');
                    $titreMedia = $titreBrut !== '' ? $titreBrut : 'Vidéo sans titre';
                    $fichierMedia = siteAssetUrl($m['fichier']);
                    $v = $infosVideo($m);
                    $classes = 'media-carte mt-carte mt-video';
                    if ($i >= $limiteVideos) $classes .= ' mt-cache';
                ?>
                <article class="<?= $classes ?>">
                    <div class="carte-media">
                        <?php if ($v['embed']): ?>
                        <button class="mt-video-bouton media-video-ouvrir" type="button" data-video-embed="<?= h($v['embed']) ?>" data-video-titre="<?= h($titreMedia) ?>" aria-haspopup="dialog" aria-controls="media-preview-dialog" aria-label="Lire la vidéo : <?= h($titreMedia) ?>">
                            <?php if ($v['miniature']): ?><img src="<?= h($v['miniature']) ?>" alt="" loading="lazy" decoding="async"><?php endif; ?>
                            <span class="mt-play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
                        </button>
                        <?php elseif ($v['locale']): ?>
                        <button class="mt-video-bouton media-video-ouvrir" type="button" data-video-src="<?= h($fichierMedia) ?>" data-video-titre="<?= h($titreMedia) ?>" aria-haspopup="dialog" aria-controls="media-preview-dialog" aria-label="Lire la vidéo : <?= h($titreMedia) ?>">
                            <video src="<?= h($fichierMedia) ?>#t=0.5" preload="metadata" muted playsinline tabindex="-1" aria-hidden="true"></video>
                            <span class="mt-play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
                        </button>
                        <?php elseif ($v['externe']): ?>
                        <a class="mt-video-bouton" href="<?= h($fichierMedia) ?>" target="_blank" rel="noopener noreferrer" aria-label="Voir la vidéo : <?= h($titreMedia) ?> (nouvel onglet)">
                            <span class="mt-play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
                        </a>
                        <?php else: ?>
                        <div class="mt-video-indispo">
                            <i class="fa-solid fa-video-slash" aria-hidden="true"></i>
                            <span>Vidéo indisponible</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="media-infos">
                        <h3><?= h($titreMedia) ?></h3>
                        <?php if (!empty($m['album'])): ?><p>Album : <?= h($m['album']) ?></p><?php endif; ?>
                        <?php if (!empty($m['date_ajout'])): ?><p>Ajoutée le <?= h(formatDateFr($m['date_ajout'])) ?></p><?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <?php if (count($videos) > $limiteVideos): ?>
            <div class="mt-plus-zone">
                <button class="mt-plus" type="button" data-mt-plus data-pas="6">
                    Afficher plus de vidéos <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
                <p class="mt-progres" role="status" aria-live="polite"><span data-mt-vues><?= $limiteVideos ?></span> sur <?= count($videos) ?> affichées</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!$photos && !$videos): ?>
        <div class="mt-vide">
            <i class="fa-regular fa-images" aria-hidden="true"></i>
            <?php if ($albumFiltre !== '' || $typeFiltre !== ''): ?>
            <h2>Rien à voir dans cette sélection</h2>
            <p>Aucun contenu ne correspond à ce filtre pour le moment.</p>
            <a class="bouton bouton-emeraude" href="<?= BASE_URL ?>/mediatheque.php">Voir toute la médiathèque</a>
            <?php else: ?>
            <h2>La médiathèque se remplit bientôt</h2>
            <p>Les photos et vidéos des événements de la mosquée seront publiées ici.</p>
            <a class="bouton bouton-emeraude" href="<?= BASE_URL ?>/index.php">Retour à l’accueil</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<dialog class="media-preview-dialog" id="media-preview-dialog" aria-labelledby="media-preview-caption">
    <button class="media-preview-dialog-fermer" type="button" aria-label="Fermer l’aperçu">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
    <figure>
        <img id="media-preview-image" alt="">
        <video id="video-preview-player" controls playsinline hidden></video>
        <iframe id="video-preview-embed" title="" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen hidden></iframe>
        <figcaption id="media-preview-caption"></figcaption>
    </figure>
</dialog>

<script>
/* Affichage progressif : sans JavaScript, tout reste visible. */
(function () {
    document.querySelectorAll('[data-mt-bloc]').forEach(function (bloc) {
        bloc.classList.add('mt-pret');
        var bouton = bloc.querySelector('[data-mt-plus]');
        if (!bouton) return;
        var zone = bouton.closest('.mt-plus-zone');
        var compteur = zone.querySelector('[data-mt-vues]');
        var pas = parseInt(bouton.getAttribute('data-pas'), 10) || 9;
        var total = bloc.querySelectorAll('article').length;

        bouton.addEventListener('click', function () {
            var cachees = bloc.querySelectorAll('.mt-cache');
            var n = Math.min(pas, cachees.length);
            for (var i = 0; i < n; i++) {
                cachees[i].classList.remove('mt-cache');
                cachees[i].classList.add('mt-apparait');
                cachees[i].style.animationDelay = (i * 40) + 'ms';
            }
            var restantes = bloc.querySelectorAll('.mt-cache').length;
            compteur.textContent = total - restantes;
            if (n > 0) {
                var cible = cachees[0].querySelector('a, button');
                if (cible) cible.focus({ preventScroll: true });
            }
            if (restantes === 0) zone.hidden = true;
        });
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>