<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('pages');

$groupes = [
    'conseil_imamal' => 'Conseil imamal',
    'comite_gestion' => 'Comité de gestion',
    'conseil_sages' => 'Conseil des sages',
    'jeunes_filles' => 'Comité des jeunes — Filles',
    'jeunes_garcons' => 'Comité des jeunes — Garçons',
];
$erreur = '';
$instanceCourante = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = $_POST['action'] ?? '';
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

    if ($id === false) {
        $erreur = 'La sélection est invalide.';
    } elseif ($action === 'supprimer') {
        $stmt = $pdo->prepare('SELECT photo FROM instances_dirigeantes WHERE id = ?');
        $stmt->execute([$id]);
        $photo = $stmt->fetchColumn();
        if ($photo === false) {
            $erreur = 'Cette personne n’existe plus.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM instances_dirigeantes WHERE id = ?');
            $stmt->execute([$id]);
            if (is_string($photo) && strpos($photo, '/assets/uploads/') === 0) {
                $cheminPhoto = __DIR__ . '/../assets/uploads/' . basename($photo);
                if (is_file($cheminPhoto) && !unlink($cheminPhoto)) {
                    throw new RuntimeException('Impossible de supprimer le portrait associé.');
                }
            }
            signalerModificationPublique();
            redirect('/admin/instances?suppr=1');
        }
    } elseif ($action === 'enregistrer') {
        $nom = trim($_POST['nom'] ?? '');
        $fonction = trim($_POST['fonction'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $groupe = $_POST['groupe'] ?? '';
        $stmt = $id > 0 ? $pdo->prepare('SELECT * FROM instances_dirigeantes WHERE id = ?') : null;
        if ($stmt) {
            $stmt->execute([$id]);
            $instanceCourante = $stmt->fetch();
            if (!$instanceCourante) {
                $erreur = 'Cette personne n’existe plus.';
            }
        }

        if (!$erreur && ($nom === '' || mb_strlen($nom) > 150)) {
            $erreur = 'Le nom est obligatoire et ne doit pas dépasser 150 caractères.';
        } elseif (!$erreur && ($fonction === '' || mb_strlen($fonction) > 150)) {
            $erreur = 'La fonction est obligatoire et ne doit pas dépasser 150 caractères.';
        } elseif (!$erreur && !isset($groupes[$groupe])) {
            $erreur = 'Choisissez une instance valide.';
        } elseif (!$erreur && mb_strlen($description) > 3000) {
            $erreur = 'La description ne doit pas dépasser 3 000 caractères.';
        }

        $photo = $instanceCourante['photo'] ?? null;
        $nouvellePhoto = null;
        $fichierPhoto = $_FILES['photo'] ?? null;
        if (!$erreur && $fichierPhoto && ($fichierPhoto['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (($fichierPhoto['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($fichierPhoto['tmp_name'] ?? '')) {
                $erreur = 'Le portrait n’a pas pu être téléversé. Vérifiez le fichier et réessayez.';
            } elseif (($fichierPhoto['size'] ?? 0) > 5 * 1024 * 1024) {
                $erreur = 'Le portrait ne doit pas dépasser 5 Mo.';
            } else {
                $extension = strtolower(pathinfo($fichierPhoto['name'], PATHINFO_EXTENSION));
                $image = @getimagesize($fichierPhoto['tmp_name']);
                $formats = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
                if (!isset($formats[$extension]) || !$image || $image['mime'] !== $formats[$extension]) {
                    $erreur = 'Format invalide. Les portraits doivent être au format JPG, PNG ou WebP.';
                } elseif (!is_dir(__DIR__ . '/../assets/uploads') || !is_writable(__DIR__ . '/../assets/uploads')) {
                    $erreur = 'Le dossier de téléversement est indisponible.';
                } else {
                    $nomPhoto = 'instance-' . bin2hex(random_bytes(8)) . '.' . $extension;
                    if (!move_uploaded_file($fichierPhoto['tmp_name'], __DIR__ . '/../assets/uploads/' . $nomPhoto)) {
                        $erreur = 'Le portrait n’a pas pu être enregistré. Réessayez.';
                    } else {
                        $nouvellePhoto = '/assets/uploads/' . $nomPhoto;
                        $photo = $nouvellePhoto;
                    }
                }
            }
        }

        if (!$erreur) {
            try {
                if ($id > 0) {
                    $stmt = $pdo->prepare('UPDATE instances_dirigeantes SET groupe = ?, nom = ?, fonction = ?, description = ?, photo = ? WHERE id = ?');
                    $stmt->execute([$groupe, $nom, $fonction, $description, $photo, $id]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO instances_dirigeantes (groupe, nom, fonction, description, photo) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$groupe, $nom, $fonction, $description, $photo]);
                }
            } catch (Throwable $e) {
                if ($nouvellePhoto) {
                    $cheminPhoto = __DIR__ . '/../assets/uploads/' . basename($nouvellePhoto);
                    if (is_file($cheminPhoto) && !unlink($cheminPhoto)) {
                        throw new RuntimeException('Impossible de nettoyer le nouveau portrait après échec de l’enregistrement.', 0, $e);
                    }
                }
                throw $e;
            }

            if ($nouvellePhoto && !empty($instanceCourante['photo']) && strpos($instanceCourante['photo'], '/assets/uploads/') === 0) {
                $anciennePhoto = __DIR__ . '/../assets/uploads/' . basename($instanceCourante['photo']);
                if (is_file($anciennePhoto) && !unlink($anciennePhoto)) {
                    throw new RuntimeException('La personne a été enregistrée, mais l’ancien portrait n’a pas pu être supprimé.');
                }
            }
            signalerModificationPublique();
            redirect('/admin/instances?ok=1');
        }

        $instanceCourante = [
            'id' => $id,
            'groupe' => $groupe,
            'nom' => $nom,
            'fonction' => $fonction,
            'description' => $description,
            'photo' => $photo,
        ];
    }
}

if ($instanceCourante === null && isset($_GET['id'])) {
    $idEdition = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($idEdition !== false) {
        $stmt = $pdo->prepare('SELECT * FROM instances_dirigeantes WHERE id = ?');
        $stmt->execute([$idEdition]);
        $instanceCourante = $stmt->fetch() ?: null;
    }
}
$instances = $pdo->query('SELECT * FROM instances_dirigeantes ORDER BY FIELD(groupe, "conseil_imamal", "comite_gestion", "conseil_sages", "jeunes_filles", "jeunes_garcons"), nom')->fetchAll();

$page_admin = 'instances';
$titre_admin = 'Instances dirigeantes';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1>Instances dirigeantes</h1></div>
<?php if ($erreur): ?><div class="alerte alerte-erreur"><?= h($erreur) ?></div><?php endif; ?>
<?php if (isset($_GET['ok'])): ?><div class="alerte alerte-succes">La personne a été enregistrée.</div><?php endif; ?>
<?php if (isset($_GET['suppr'])): ?><div class="alerte alerte-succes">La personne a été supprimée.</div><?php endif; ?>

<div class="admin-carte">
    <h2 style="margin-top:0;"><?= $instanceCourante ? 'Modifier une personne' : 'Ajouter une personne' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="action" value="enregistrer">
        <input type="hidden" name="id" value="<?= (int) ($instanceCourante['id'] ?? 0) ?>">
        <div class="champ">
            <label for="instance-groupe">Instance</label>
            <select id="instance-groupe" name="groupe" required>
                <?php foreach ($groupes as $cle => $libelle): ?>
                    <option value="<?= h($cle) ?>" <?= ($instanceCourante['groupe'] ?? '') === $cle ? 'selected' : '' ?>><?= h($libelle) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="champ">
            <label for="instance-nom">Nom</label>
            <input id="instance-nom" type="text" name="nom" maxlength="150" required value="<?= h($instanceCourante['nom'] ?? '') ?>">
        </div>
        <div class="champ">
            <label for="instance-fonction">Fonction</label>
            <input id="instance-fonction" type="text" name="fonction" maxlength="150" required value="<?= h($instanceCourante['fonction'] ?? '') ?>">
        </div>
        <div class="champ">
            <label for="instance-description">Description</label>
            <textarea id="instance-description" name="description" rows="4" maxlength="3000"><?= h($instanceCourante['description'] ?? '') ?></textarea>
        </div>
        <div class="champ">
            <label for="instance-photo">Portrait (JPG, PNG ou WebP, 5 Mo maximum)</label>
            <input id="instance-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp">
            <?php if (!empty($instanceCourante['photo'])): ?><p>Un portrait est déjà enregistré. Choisissez une nouvelle image pour le remplacer.</p><?php endif; ?>
        </div>
        <button type="submit" class="bouton bouton-emeraude">Enregistrer</button>
        <?php if ($instanceCourante): ?> <a href="<?= BASE_URL ?>/admin/instances">Annuler</a><?php endif; ?>
    </form>
</div>

<div class="admin-carte">
    <h2 style="margin-top:0;">Personnes enregistrées</h2>
    <table class="table-admin">
        <thead><tr><th>Nom</th><th>Instance</th><th>Fonction</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($instances as $instance): ?>
            <tr>
                <td><?= h($instance['nom']) ?></td>
                <td><?= h($groupes[$instance['groupe']] ?? $instance['groupe']) ?></td>
                <td><?= h($instance['fonction']) ?></td>
                <td class="actions">
                    <a href="<?= BASE_URL ?>/admin/instances?id=<?= (int) $instance['id'] ?>">Modifier</a>
                    <form method="post" onsubmit="return confirm('Supprimer cette personne et son portrait ?');">
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id" value="<?= (int) $instance['id'] ?>">
                        <button type="submit" class="lien-suppr bouton-lien">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$instances): ?><tr><td colspan="4">Aucune personne enregistrée.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>