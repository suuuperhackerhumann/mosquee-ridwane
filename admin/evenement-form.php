<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('evenements');

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$ev = ['titre' => '', 'description' => '', 'image' => '', 'categorie_id' => '', 'date_evenement' => '', 'heure_evenement' => '', 'lieu' => '', 'statut' => 'publie'];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE id = ?");
    $stmt->execute([$id]);
    $trouve = $stmt->fetch();
    if ($trouve) $ev = $trouve;
}

$categories = $pdo->query("SELECT * FROM categories WHERE type='evenement' ORDER BY nom")->fetchAll();
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $ev['titre'] = trim($_POST['titre'] ?? '');
    $ev['description'] = trim($_POST['description'] ?? '');
    $ev['categorie_id'] = $_POST['categorie_id'] ?: null;
    $ev['date_evenement'] = $_POST['date_evenement'] ?? '';
    $ev['heure_evenement'] = $_POST['heure_evenement'] ?: null;
    $ev['lieu'] = trim($_POST['lieu'] ?? '');
    $ev['statut'] = $_POST['statut'] ?? 'publie';

    if ($ev['titre'] === '' || $ev['date_evenement'] === '') {
        $erreur = "Le titre et la date sont obligatoires.";
    } else {
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $nomFichier = 'evt-' . time() . '-' . rand(1000,9999) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/uploads/' . $nomFichier);
                $ev['image'] = '/assets/uploads/' . $nomFichier;
            } else {
                $erreur = "Format d'image non supporté (jpg, png ou webp uniquement).";
            }
        }

        if (!$erreur) {
            $slug = slugify($ev['titre']) . ($id ? '' : '-' . substr(uniqid(), -5));
            if ($id) {
                $stmt = $pdo->prepare("UPDATE evenements SET titre=?, description=?, image=?, categorie_id=?, date_evenement=?, heure_evenement=?, lieu=?, statut=? WHERE id=?");
                $stmt->execute([$ev['titre'], $ev['description'], $ev['image'], $ev['categorie_id'], $ev['date_evenement'], $ev['heure_evenement'], $ev['lieu'], $ev['statut'], $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO evenements (titre, slug, description, image, categorie_id, date_evenement, heure_evenement, lieu, statut, admin_id) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$ev['titre'], $slug, $ev['description'], $ev['image'], $ev['categorie_id'], $ev['date_evenement'], $ev['heure_evenement'], $ev['lieu'], $ev['statut'], $_SESSION['admin_id']]);
            }
            redirect('/admin/evenements.php?ok=1');
        }
    }
}

$page_admin = 'evenements';
$titre_admin = $id ? "Modifier l'événement" : 'Nouvel événement';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1><?= $id ? "Modifier l'événement" : 'Nouvel événement' ?></h1></div>

<?php if ($erreur): ?><div class="alerte alerte-erreur"><?= h($erreur) ?></div><?php endif; ?>

<div class="admin-carte">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <div class="champ">
            <label for="titre">Titre *</label>
            <input type="text" id="titre" name="titre" required value="<?= h($ev['titre']) ?>">
        </div>
        <div class="grille grille-2">
            <div class="champ">
                <label for="date_evenement">Date *</label>
                <input type="date" id="date_evenement" name="date_evenement" required value="<?= h($ev['date_evenement']) ?>">
            </div>
            <div class="champ">
                <label for="heure_evenement">Heure</label>
                <input type="time" id="heure_evenement" name="heure_evenement" value="<?= h(substr($ev['heure_evenement'] ?? '', 0, 5)) ?>">
            </div>
        </div>
        <div class="grille grille-2">
            <div class="champ">
                <label for="lieu">Lieu</label>
                <input type="text" id="lieu" name="lieu" value="<?= h($ev['lieu']) ?>">
            </div>
            <div class="champ">
                <label for="categorie_id">Catégorie</label>
                <select id="categorie_id" name="categorie_id">
                    <option value="">— Aucune —</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $ev['categorie_id'] == $c['id'] ? 'selected' : '' ?>><?= h($c['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="champ">
            <label for="statut">Statut</label>
            <select id="statut" name="statut">
                <option value="publie" <?= $ev['statut'] === 'publie' ? 'selected' : '' ?>>Publié</option>
                <option value="brouillon" <?= $ev['statut'] === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
            </select>
        </div>
        <div class="champ">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="8"><?= h($ev['description']) ?></textarea>
        </div>
        <div class="champ">
            <label for="image">Image</label>
            <?php if ($ev['image']): ?><img src="<?= h(siteAssetUrl($ev['image'])) ?>" style="max-width:200px; border-radius:4px; margin-bottom:10px;"><?php endif; ?>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <button type="submit" class="bouton bouton-emeraude">Enregistrer</button>
        <a href="<?= BASE_URL ?>/admin/evenements.php" class="bouton bouton-contour" style="color:var(--emeraude-800); border-color:var(--plâtre-200);">Annuler</a>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
