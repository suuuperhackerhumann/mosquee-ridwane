<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('media');

$erreur = '';

if (isset($_GET['supprimer'])) {
    $stmt = $pdo->prepare("SELECT fichier FROM mediatheque WHERE id = ?");
    $stmt->execute([(int)$_GET['supprimer']]);
    $m = $stmt->fetch();
    if ($m && strpos($m['fichier'], '/assets/uploads/') === 0) {
        $cheminFichier = __DIR__ . '/../assets/uploads/' . basename($m['fichier']);
        if (is_file($cheminFichier) && !unlink($cheminFichier)) {
            throw new RuntimeException('Impossible de supprimer le fichier de la médiathèque.');
        }
    }
    $stmt = $pdo->prepare("DELETE FROM mediatheque WHERE id = ?");
    $stmt->execute([(int)$_GET['supprimer']]);
    redirect('/admin/media.php?suppr=1');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
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
        if (empty($_FILES['fichier']['name'])) {
            $erreur = "Merci de choisir une photo à téléverser.";
        } else {
            $ext = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
                $erreur = "Format d'image non supporté (jpg, png ou webp uniquement).";
            } else {
                $nomFichier = 'media-' . time() . '-' . rand(1000,9999) . '.' . $ext;
                move_uploaded_file($_FILES['fichier']['tmp_name'], __DIR__ . '/../assets/uploads/' . $nomFichier);
                $stmt = $pdo->prepare("INSERT INTO mediatheque (titre, type, fichier, album) VALUES (?, 'photo', ?, ?)");
                $stmt->execute([$titre, '/assets/uploads/' . $nomFichier, $album]);
            }
        }
    }
    if (!$erreur) redirect('/admin/media.php?ok=1');
}

$medias = $pdo->query("SELECT * FROM mediatheque ORDER BY date_ajout DESC")->fetchAll();

$page_admin = 'media';
$titre_admin = 'Médiathèque';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1>Médiathèque</h1></div>
<?php if (isset($_GET['ok'])): ?><div class="alerte alerte-succes">Ajouté à la médiathèque.</div><?php endif; ?>
<?php if (isset($_GET['suppr'])): ?><div class="alerte alerte-succes">Élément supprimé.</div><?php endif; ?>
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
            <label for="fichier">Fichier image</label>
            <input type="file" id="fichier" name="fichier" accept=".jpg,.jpeg,.png,.webp">
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
    <table class="table-admin">
        <thead><tr><th>Aperçu</th><th>Titre</th><th>Type</th><th>Album</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($medias as $m): ?>
        <tr>
            <td><?php if ($m['type'] === 'photo'): ?><img src="<?= h(siteAssetUrl($m['fichier'])) ?>" style="width:60px; height:44px; object-fit:cover; border-radius:4px;"><?php else: ?>🎬<?php endif; ?></td>
            <td><?= h($m['titre'] ?: '—') ?></td>
            <td><?= h(ucfirst($m['type'])) ?></td>
            <td><?= h($m['album'] ?: '—') ?></td>
            <td class="actions"><a href="<?= BASE_URL ?>/admin/media.php?supprimer=<?= $m['id'] ?>" class="lien-suppr" onclick="return confirm('Supprimer ce contenu ?');">Supprimer</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$medias): ?><tr><td colspan="5">Aucun contenu pour le moment.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
