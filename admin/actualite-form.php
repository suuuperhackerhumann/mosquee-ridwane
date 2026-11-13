<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('actualites');

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$actu = ['titre' => '', 'resume' => '', 'contenu' => '', 'image' => '', 'categorie_id' => '', 'statut' => 'publie'];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM actualites WHERE id = ?");
    $stmt->execute([$id]);
    $trouve = $stmt->fetch();
    if ($trouve) $actu = $trouve;
}

$categories = $pdo->query("SELECT * FROM categories WHERE type='actualite' ORDER BY nom")->fetchAll();
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $actu['titre'] = trim($_POST['titre'] ?? '');
    $actu['resume'] = trim($_POST['resume'] ?? '');
    $actu['contenu'] = trim($_POST['contenu'] ?? '');
    $actu['categorie_id'] = (int)($_POST['categorie_id'] ?? 0) ?: null;
    $actu['statut'] = in_array($_POST['statut'] ?? '', ['publie', 'brouillon'], true) ? $_POST['statut'] : 'publie';

    if ($actu['titre'] === '' || $actu['contenu'] === '') {
        $erreur = "Le titre et le contenu sont obligatoires.";
    } else {
        // Upload d'image (optionnel)
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (($_FILES['image']['size'] ?? 0) > MAX_UPLOAD_FILE_SIZE) {
                $erreur = "L'image ne doit pas dépasser 100 Mo.";
            } elseif (in_array($ext, ['jpg','jpeg','png','webp']) && @getimagesize($_FILES['image']['tmp_name']) !== false) {
                $nomFichier = 'actu-' . time() . '-' . rand(1000,9999) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/uploads/' . $nomFichier);
                $actu['image'] = '/assets/uploads/' . $nomFichier;
            } else {
                $erreur = "Format d'image non supporté (jpg, png ou webp uniquement).";
            }
        }

        if (!$erreur) {
            $slug = slugify($actu['titre']) . ($id ? '' : '-' . substr(uniqid(), -5));
            if ($id) {
                $stmt = $pdo->prepare("UPDATE actualites SET titre=?, resume=?, contenu=?, image=?, categorie_id=?, statut=? WHERE id=?");
                $stmt->execute([$actu['titre'], $actu['resume'], $actu['contenu'], $actu['image'], $actu['categorie_id'], $actu['statut'], $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO actualites (titre, slug, resume, contenu, image, categorie_id, statut, admin_id) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->execute([$actu['titre'], $slug, $actu['resume'], $actu['contenu'], $actu['image'], $actu['categorie_id'], $actu['statut'], $_SESSION['admin_id']]);
            }
            signalerModificationPublique();
            redirect('/admin/actualites?ok=1');
        }
    }
}

$page_admin = 'actualites';
$titre_admin = $id ? 'Modifier l\'actualité' : 'Nouvelle actualité';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1><?= $id ? "Modifier l'actualité" : 'Nouvelle actualité' ?></h1></div>

<?php if ($erreur): ?><div class="alerte alerte-erreur"><?= h($erreur) ?></div><?php endif; ?>

<div class="admin-carte">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <div class="champ">
            <label for="titre">Titre *</label>
            <input type="text" id="titre" name="titre" required value="<?= h($actu['titre']) ?>">
        </div>
        <div class="grille grille-2">
            <div class="champ">
                <label for="categorie_id">Catégorie</label>
                <select id="categorie_id" name="categorie_id">
                    <option value="">— Aucune —</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $actu['categorie_id'] == $c['id'] ? 'selected' : '' ?>><?= h($c['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="champ">
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <option value="publie" <?= $actu['statut'] === 'publie' ? 'selected' : '' ?>>Publié</option>
                    <option value="brouillon" <?= $actu['statut'] === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                </select>
            </div>
        </div>
        <div class="champ">
            <label for="resume">Résumé court (affiché dans les listes)</label>
            <input type="text" id="resume" name="resume" value="<?= h($actu['resume']) ?>">
        </div>
        <div class="champ">
            <label for="contenu">Contenu *</label>
            <textarea id="contenu" name="contenu" rows="10" required><?= h($actu['contenu']) ?></textarea>
        </div>
        <div class="champ">
            <label for="image">Image</label>
            <?php if ($actu['image']): ?><img src="<?= h(siteAssetUrl($actu['image'])) ?>" style="max-width:200px; border-radius:4px; margin-bottom:10px;"><?php endif; ?>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
            <small>100 Mo maximum.</small>
        </div>
        <button type="submit" class="bouton bouton-emeraude">Enregistrer</button>
        <a href="<?= BASE_URL ?>/admin/actualites.php" class="bouton bouton-contour" style="color:var(--emeraude-800); border-color:var(--plâtre-200);">Annuler</a>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
