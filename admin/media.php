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
        $url = trim($_POST['url_video'] ?? '');
        if ($url === '') {
            $erreur = "Merci de renseigner l'URL de la vidéo.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO mediatheque (titre, type, fichier, album) VALUES (?, 'video', ?, ?)");
            $stmt->execute([$titre, $url, $album]);
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
            <select id="type" name="type" onchange="document.getElementById('champ-photo').style.display = this.value==='photo' ? 'block':'none'; document.getElementById('champ-video').style.display = this.value==='video' ? 'block':'none';">
                <option value="photo">Photo</option>
                <option value="video">Vidéo (lien YouTube / Vimeo)</option>
            </select>
        </div>
        <div class="champ" id="champ-photo">
            <label for="fichiers">Photos (10 maximum par envoi)</label>
            <input type="file" id="fichiers" name="fichiers[]" accept=".jpg,.jpeg,.png,.webp" multiple>
            <small>Formats acceptés : JPG, PNG et WEBP. Vous pouvez sélectionner jusqu’à 10 photos à la fois.</small>
        </div>
        <div class="champ" id="champ-video" style="display:none;">
            <label for="url_video">URL de la vidéo</label>
            <input type="url" id="url_video" name="url_video" placeholder="https://...">
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
                    <span class="media-video-apercu">▶</span>
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
                <button type="button" class="bouton-lien fermer-album" aria-label="Fermer">✕</button>
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
                        <span class="media-video-apercu">▶ Vidéo</span>
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
