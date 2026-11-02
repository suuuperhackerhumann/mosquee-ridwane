<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('media');

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer'])) {
    $selection = $_POST['supprimer'];
    if (!is_array($selection) || !$selection) {
        $erreur = 'Sélectionnez au moins un contenu à supprimer.';
    } else {
        $identifiants = [];
        foreach ($selection as $identifiant) {
            $identifiantValide = filter_var($identifiant, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($identifiantValide === false) {
                $erreur = 'La sélection à supprimer est invalide.';
                break;
            }
            $identifiants[] = $identifiantValide;
        }
        $identifiants = array_values(array_unique($identifiants));

        if (!$erreur) {
            $placeholders = implode(',', array_fill(0, count($identifiants), '?'));
            $stmt = $pdo->prepare("SELECT id, fichier FROM mediatheque WHERE id IN ($placeholders)");
            $stmt->execute($identifiants);
            $elements = $stmt->fetchAll();

            if (count($elements) !== count($identifiants)) {
                $erreur = 'Certains contenus sélectionnés ne sont plus disponibles. Actualisez la page et réessayez.';
            } else {
                foreach ($elements as $element) {
                    if (strpos($element['fichier'], '/assets/uploads/') === 0) {
                        $cheminFichier = __DIR__ . '/../assets/uploads/' . basename($element['fichier']);
                        if (is_file($cheminFichier) && !unlink($cheminFichier)) {
                            throw new RuntimeException('Impossible de supprimer un fichier de la médiathèque.');
                        }
                    }
                }

                $stmt = $pdo->prepare("DELETE FROM mediatheque WHERE id IN ($placeholders)");
                $stmt->execute($identifiants);
                redirect('/admin/media.php?suppr=' . count($identifiants));
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $album = trim($_POST['album'] ?? '');
    $type = $_POST['type'] ?? 'photo';

    if ($type === 'video') {
        $modeVideo = $_POST['mode_video'] ?? 'lien';
        if ($modeVideo === 'fichier') {
            $video = $_FILES['video_file'] ?? null;
            if (
                !$video
                || !isset($video['name'], $video['tmp_name'], $video['error'], $video['size'])
                || !is_string($video['name'])
                || !is_string($video['tmp_name'])
                || !is_int($video['error'])
                || $video['error'] === UPLOAD_ERR_NO_FILE
            ) {
                $erreur = 'Merci de choisir un fichier vidéo.';
            } elseif ($video['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($video['tmp_name'])) {
                if ($video['error'] === UPLOAD_ERR_INI_SIZE || $video['error'] === UPLOAD_ERR_FORM_SIZE) {
                    $erreur = 'La vidéo dépasse la limite autorisée par le serveur. Réduisez sa taille ou augmentez upload_max_filesize et post_max_size dans PHP.';
                } elseif ($video['error'] === UPLOAD_ERR_PARTIAL) {
                    $erreur = 'Le téléversement de la vidéo a été interrompu. Réessayez.';
                } elseif ($video['error'] === UPLOAD_ERR_NO_TMP_DIR || $video['error'] === UPLOAD_ERR_CANT_WRITE) {
                    $erreur = 'Le serveur ne peut pas enregistrer temporairement la vidéo. Contactez l’administrateur.';
                } else {
                    $erreur = 'La vidéo n’a pas pu être téléversée. Vérifiez le fichier et réessayez.';
                }
            } else {
                $extension = strtolower(pathinfo($video['name'], PATHINFO_EXTENSION));
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($video['tmp_name']);
                $formatsVideo = [
                    'mp4' => 'video/mp4',
                    'webm' => 'video/webm',
                    'ogv' => 'video/ogg',
                    'ogg' => 'video/ogg',
                ];
                if (!isset($formatsVideo[$extension]) || $mime !== $formatsVideo[$extension]) {
                    $erreur = 'Format vidéo invalide. Formats acceptés : MP4, WebM ou OGG.';
                } elseif (!is_dir(__DIR__ . '/../assets/uploads') || !is_writable(__DIR__ . '/../assets/uploads')) {
                    $erreur = 'Le dossier de téléversement est indisponible. Contactez l’administrateur du site.';
                } else {
                    $nomFichier = 'video-' . bin2hex(random_bytes(8)) . '.' . $extension;
                    $cheminFichier = __DIR__ . '/../assets/uploads/' . $nomFichier;
                    if (!move_uploaded_file($video['tmp_name'], $cheminFichier)) {
                        $erreur = 'La vidéo n’a pas pu être enregistrée. Veuillez réessayer.';
                    } else {
                        try {
                            $stmt = $pdo->prepare("INSERT INTO mediatheque (titre, type, fichier, album) VALUES (?, 'video', ?, ?)");
                            $stmt->execute([$titre, '/assets/uploads/' . $nomFichier, $album]);
                        } catch (Throwable $e) {
                            if (is_file($cheminFichier) && !unlink($cheminFichier)) {
                                throw new RuntimeException('Impossible de nettoyer le fichier vidéo après l’échec de son enregistrement.', 0, $e);
                            }
                            throw $e;
                        }
                    }
                }
            }
        } elseif ($modeVideo === 'lien') {
            $url = trim($_POST['url_video'] ?? '');
            if ($url === '') {
                $erreur = "Merci de renseigner l'URL de la vidéo.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO mediatheque (titre, type, fichier, album) VALUES (?, 'video', ?, ?)");
                $stmt->execute([$titre, $url, $album]);
            }
        } else {
            $erreur = 'Choisissez une source vidéo valide.';
        }
    } else {
        $fichiers = $_FILES['fichiers'] ?? null;
        if (
            !$fichiers
            || !isset($fichiers['name'], $fichiers['tmp_name'], $fichiers['error'])
            || !is_array($fichiers['name'])
            || !is_array($fichiers['tmp_name'])
            || !is_array($fichiers['error'])
        ) {
            $erreur = "Merci de choisir au moins une photo à téléverser.";
        } else {
            $photos = [];
            foreach ($fichiers['name'] as $index => $nomOriginal) {
                if (!is_string($nomOriginal)) {
                    $erreur = "La sélection de photos est invalide.";
                    break;
                }
                if ($nomOriginal !== '') {
                    $photos[] = $index;
                }
            }

            if (!$erreur && !$photos) {
                $erreur = "Merci de choisir au moins une photo à téléverser.";
            } elseif (!$erreur && count($photos) > 10) {
                $erreur = "Vous pouvez ajouter au maximum 10 photos par envoi.";
            } elseif (!$erreur) {
                $extensions = [];
                foreach ($photos as $index) {
                    if (
                        !isset($fichiers['tmp_name'][$index], $fichiers['error'][$index])
                        || !is_string($fichiers['tmp_name'][$index])
                        || $fichiers['error'][$index] !== UPLOAD_ERR_OK
                        || !is_uploaded_file($fichiers['tmp_name'][$index])
                    ) {
                        $erreur = "Une photo n'a pas pu être téléversée. Vérifiez la taille des fichiers et réessayez.";
                        break;
                    }

                    $extension = strtolower(pathinfo($fichiers['name'][$index], PATHINFO_EXTENSION));
                    $image = @getimagesize($fichiers['tmp_name'][$index]);
                    $formats = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
                    if (!isset($formats[$extension]) || !$image || $image['mime'] !== $formats[$extension]) {
                        $erreur = "Format d'image non supporté ou fichier invalide (jpg, png ou webp uniquement).";
                        break;
                    }
                    $extensions[$index] = $extension;
                }

                $dossierUploads = __DIR__ . '/../assets/uploads';
                if (!$erreur && (!is_dir($dossierUploads) || !is_writable($dossierUploads))) {
                    $erreur = "Le dossier de téléversement est indisponible. Contactez l'administrateur du site.";
                }

                if (!$erreur) {
                    $fichiersDeplaces = [];
                    try {
                        foreach ($photos as $index) {
                            $nomFichier = 'media-' . bin2hex(random_bytes(8)) . '.' . $extensions[$index];
                            if (!move_uploaded_file($fichiers['tmp_name'][$index], $dossierUploads . '/' . $nomFichier)) {
                                $erreur = "Une photo n'a pas pu être enregistrée. Veuillez réessayer.";
                                break;
                            }
                            $fichiersDeplaces[] = $nomFichier;
                        }

                        if (!$erreur) {
                            $pdo->beginTransaction();
                            $stmt = $pdo->prepare("INSERT INTO mediatheque (titre, type, fichier, album) VALUES (?, 'photo', ?, ?)");
                            foreach ($fichiersDeplaces as $nomFichier) {
                                $stmt->execute([$titre, '/assets/uploads/' . $nomFichier, $album]);
                            }
                            $pdo->commit();
                            redirect('/admin/media.php?ok=1');
                        }
                    } catch (Throwable $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        foreach ($fichiersDeplaces as $nomFichier) {
                            $cheminFichier = $dossierUploads . '/' . $nomFichier;
                            if (is_file($cheminFichier) && !unlink($cheminFichier)) {
                                throw new RuntimeException('Impossible de nettoyer un fichier après l’échec du téléversement.', 0, $e);
                            }
                        }
                        throw $e;
                    }

                    if ($erreur) {
                        foreach ($fichiersDeplaces as $nomFichier) {
                            $cheminFichier = $dossierUploads . '/' . $nomFichier;
                            if (is_file($cheminFichier) && !unlink($cheminFichier)) {
                                throw new RuntimeException('Impossible de nettoyer un fichier après l’échec du téléversement.');
                            }
                        }
                    }
                }
            }
        }
    }
    if (!$erreur) redirect('/admin/media.php?ok=1');
}

$medias = $pdo->query("SELECT * FROM mediatheque ORDER BY date_ajout DESC")->fetchAll();
$limitesUpload = array_filter([
    ini_get('upload_max_filesize'),
    ini_get('post_max_size'),
]);
$tailleMaxUpload = null;
foreach ($limitesUpload as $limite) {
    if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*([KMG])?B?\s*$/i', $limite, $parties)) {
        $unite = strtoupper($parties[2] ?? '');
        $multiplicateur = 1;
        if ($unite === 'G') {
            $multiplicateur = 1024 ** 3;
        } elseif ($unite === 'M') {
            $multiplicateur = 1024 ** 2;
        } elseif ($unite === 'K') {
            $multiplicateur = 1024;
        }
        $octets = (int) ($parties[1] * $multiplicateur);
        $tailleMaxUpload = $tailleMaxUpload === null ? $octets : min($tailleMaxUpload, $octets);
    }
}
$tailleMaxUploadTexte = $tailleMaxUpload === null
    ? 'la limite configurée par PHP'
    : ($tailleMaxUpload >= 1024 ** 2
        ? number_format($tailleMaxUpload / (1024 ** 2), 0, ',', ' ') . ' Mo'
        : number_format($tailleMaxUpload / 1024, 0, ',', ' ') . ' Ko');
$albumsMedia = [];
foreach ($medias as $media) {
    $nomAlbum = trim($media['album'] ?? '') ?: 'Sans album';
    $albumsMedia[$nomAlbum][] = $media;
}

$page_admin = 'media';
$titre_admin = 'Médiathèque';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1>Médiathèque</h1></div>
<?php if (isset($_GET['ok'])): ?><div class="alerte alerte-succes">Ajouté à la médiathèque.</div><?php endif; ?>
<?php if (isset($_GET['suppr'])): ?><div class="alerte alerte-succes"><?= (int) $_GET['suppr'] ?> contenu<?= (int) $_GET['suppr'] > 1 ? 's' : '' ?> supprimé<?= (int) $_GET['suppr'] > 1 ? 's' : '' ?>.</div><?php endif; ?>
<?php if ($erreur): ?><div class="alerte alerte-erreur"><?= h($erreur) ?></div><?php endif; ?>

<div class="admin-carte">
    <h3 style="margin-top:0;">Ajouter un contenu</h3>
    <form method="post" enctype="multipart/form-data" id="form-media">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <div class="grille grille-2">
            <div class="champ">
                <label for="titre">Titre</label>
                <input type="text" id="titre" name="titre">
            </div>
            <div class="champ">
                <label for="album">Album (optionnel)</label>
                <input type="text" id="album" name="album" placeholder="ex : Ramadan 2026">
            </div>
        </div>
        <div class="champ">
            <label for="type">Type</label>
            <select id="type" name="type">
                <option value="photo">Photo</option>
                <option value="video">Vidéo</option>
            </select>
        </div>
        <div class="champ" id="champ-photo">
            <label for="fichiers">Photos (10 maximum par envoi)</label>
            <input type="file" id="fichiers" name="fichiers[]" accept=".jpg,.jpeg,.png,.webp" multiple>
            <small>Formats acceptés : JPG, PNG et WEBP. Vous pouvez sélectionner jusqu’à 10 photos à la fois.</small>
        </div>
        <div id="champ-video" hidden>
            <div class="champ">
                <label for="mode-video">Source de la vidéo</label>
                <select id="mode-video" name="mode_video">
                    <option value="lien">Lien YouTube / Vimeo</option>
                    <option value="fichier">Téléverser un fichier vidéo</option>
                </select>
            </div>
            <div class="champ" id="champ-video-lien">
                <label for="url_video">URL de la vidéo</label>
                <input type="url" id="url_video" name="url_video" placeholder="https://...">
            </div>
            <div class="champ" id="champ-video-fichier" hidden>
                <label for="video-file">Fichier vidéo</label>
                <input type="file" id="video-file" name="video_file" accept=".mp4,.webm,.ogv,.ogg,video/mp4,video/webm,video/ogg" disabled>
                <small>Formats acceptés : MP4, WebM et OGG. Taille maximale actuelle du serveur : <?= h($tailleMaxUploadTexte) ?> par fichier.</small>
            </div>
        </div>
        <button type="submit" class="bouton bouton-emeraude">Ajouter</button>
    </form>
</div>

<div class="admin-carte">
    <h3 style="margin-top:0;">Contenus existants</h3>
    <?php if ($albumsMedia): ?>
    <div class="media-albums">
        <?php $indexAlbum = 0; ?>
        <?php foreach ($albumsMedia as $nomAlbum => $contenusAlbum): ?>
        <?php $dialogId = 'album-media-' . $indexAlbum++; ?>
        <article class="media-album">
            <div class="media-album-apercus" aria-hidden="true">
                <?php foreach (array_slice($contenusAlbum, 0, 3) as $apercu): ?>
                    <?php if ($apercu['type'] === 'photo'): ?>
                    <img src="<?= h(siteAssetUrl($apercu['fichier'])) ?>" alt="">
                    <?php else: ?>
                    <span class="media-video-apercu"><i class="fa-solid fa-play" aria-hidden="true"></i></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <h4><?= h($nomAlbum) ?></h4>
            <p><?= count($contenusAlbum) ?> contenu<?= count($contenusAlbum) > 1 ? 's' : '' ?></p>
            <button type="button" class="bouton bouton-emeraude ouvrir-album" data-dialog="<?= h($dialogId) ?>">Voir l’album</button>
        </article>

        <dialog class="media-dialogue" id="<?= h($dialogId) ?>" aria-labelledby="<?= h($dialogId) ?>-titre">
            <div class="media-dialogue-entete">
                <div>
                    <h3 id="<?= h($dialogId) ?>-titre"><?= h($nomAlbum) ?></h3>
                    <p>Sélectionnez les contenus à supprimer.</p>
                </div>
                <button type="button" class="bouton-lien fermer-album" aria-label="Fermer"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <form method="post" class="media-suppression-form">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <div class="media-selection-actions">
                    <label><input type="checkbox" class="selectionner-tout"> Tout sélectionner</label>
                    <span><?= count($contenusAlbum) ?> contenu<?= count($contenusAlbum) > 1 ? 's' : '' ?></span>
                </div>
                <div class="media-selection-grille">
                    <?php foreach ($contenusAlbum as $contenu): ?>
                    <label class="media-selection-element">
                        <input type="checkbox" name="supprimer[]" value="<?= (int) $contenu['id'] ?>">
                        <?php if ($contenu['type'] === 'photo'): ?>
                        <img src="<?= h(siteAssetUrl($contenu['fichier'])) ?>" alt="">
                        <?php else: ?>
                        <span class="media-video-apercu"><i class="fa-solid fa-play" aria-hidden="true"></i> Vidéo</span>
                        <?php endif; ?>
                        <span><?= h($contenu['titre'] ?: ucfirst($contenu['type'])) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="media-dialogue-pied">
                    <button type="button" class="bouton bouton-emeraude fermer-album">Annuler</button>
                    <button type="submit" class="bouton bouton-danger supprimer-selection" disabled>Supprimer la sélection</button>
                </div>
            </form>
        </dialog>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p>Aucun contenu pour le moment.</p>
    <?php endif; ?>
</div>

<script>
const typeMedia = document.getElementById('type');
const champVideo = document.getElementById('champ-video');
const modeVideo = document.getElementById('mode-video');
const champVideoLien = document.getElementById('champ-video-lien');
const champVideoFichier = document.getElementById('champ-video-fichier');
const champUrlVideo = document.getElementById('url_video');
const champFichierVideo = document.getElementById('video-file');
const actualiserTypeMedia = () => {
    const estVideo = typeMedia.value === 'video';
    document.getElementById('champ-photo').hidden = estVideo;
    champVideo.hidden = !estVideo;
    champUrlVideo.disabled = !estVideo || modeVideo.value !== 'lien';
    champUrlVideo.required = estVideo && modeVideo.value === 'lien';
    champFichierVideo.disabled = !estVideo || modeVideo.value !== 'fichier';
    champFichierVideo.required = estVideo && modeVideo.value === 'fichier';
    champVideoLien.hidden = modeVideo.value !== 'lien';
    champVideoFichier.hidden = modeVideo.value !== 'fichier';
};
typeMedia.addEventListener('change', actualiserTypeMedia);
modeVideo.addEventListener('change', actualiserTypeMedia);
actualiserTypeMedia();

document.querySelectorAll('.ouvrir-album').forEach((bouton) => {
    bouton.addEventListener('click', () => document.getElementById(bouton.dataset.dialog).showModal());
});
document.querySelectorAll('.media-dialogue').forEach((dialogue) => {
    dialogue.querySelectorAll('.fermer-album').forEach((bouton) => {
        bouton.addEventListener('click', () => dialogue.close());
    });
    const cases = [...dialogue.querySelectorAll('input[name="supprimer[]"]')];
    const toutSelectionner = dialogue.querySelector('.selectionner-tout');
    const boutonSuppression = dialogue.querySelector('.supprimer-selection');
    const actualiserSelection = () => {
        boutonSuppression.disabled = !cases.some((caseSelection) => caseSelection.checked);
        toutSelectionner.checked = cases.every((caseSelection) => caseSelection.checked);
        toutSelectionner.indeterminate = cases.some((caseSelection) => caseSelection.checked) && !toutSelectionner.checked;
    };
    cases.forEach((caseSelection) => caseSelection.addEventListener('change', actualiserSelection));
    toutSelectionner.addEventListener('change', () => {
        cases.forEach((caseSelection) => {
            caseSelection.checked = toutSelectionner.checked;
        });
        actualiserSelection();
    });
    dialogue.addEventListener('click', (evenement) => {
        if (evenement.target === dialogue) dialogue.close();
    });
    dialogue.querySelector('form').addEventListener('submit', (evenement) => {
        const nombre = cases.filter((caseSelection) => caseSelection.checked).length;
        if (!nombre || !confirm(`Supprimer ${nombre} contenu${nombre > 1 ? 's' : ''} sélectionné${nombre > 1 ? 's' : ''} ?`)) {
            evenement.preventDefault();
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
