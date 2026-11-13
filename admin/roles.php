<?php
require_once __DIR__ . '/../config/config.php';
requireFullAdmin();

$erreur = '';
$succes = '';
$permissionsDisponibles = adminPermissions();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    if (isset($_POST['creer_role'])) {
        $nom = trim($_POST['nom_role'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        if (!is_array($permissions)) {
            $permissions = [];
        }
        $selectedPermissions = [];
        foreach ($permissions as $permission) {
            if (is_string($permission) && isset($permissionsDisponibles[$permission])) {
                $selectedPermissions[] = $permission;
            }
        }
        $permissions = array_values(array_unique($selectedPermissions));

        if ($nom === '' || mb_strlen($nom) > 100) {
            $erreur = 'Le nom du rôle est obligatoire et ne doit pas dépasser 100 caractères.';
        } elseif (!$permissions) {
            $erreur = 'Choisissez au moins une tâche pour ce rôle.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM roles WHERE nom = ?');
            $stmt->execute([$nom]);
            if ($stmt->fetch()) {
                $erreur = 'Un rôle porte déjà ce nom.';
            } else {
                $cle = slugify($nom) . '-' . bin2hex(random_bytes(4));
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('INSERT INTO roles (nom, role_key) VALUES (?, ?)');
                $stmt->execute([$nom, $cle]);
                $roleId = (int) $pdo->lastInsertId();
                $stmt = $pdo->prepare('INSERT INTO role_permissions (role_id, permission) VALUES (?, ?)');
                foreach ($permissions as $permission) {
                    $stmt->execute([$roleId, $permission]);
                }
                $pdo->commit();
                $succes = 'Le rôle a été créé.';
            }
        }
    } elseif (isset($_POST['modifier_role'])) {
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $nom = trim($_POST['nom_role'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        if (!is_array($permissions)) {
            $permissions = [];
        }
        $selectedPermissions = [];
        foreach ($permissions as $permission) {
            if (is_string($permission) && isset($permissionsDisponibles[$permission])) {
                $selectedPermissions[] = $permission;
            }
        }
        $permissions = array_values(array_unique($selectedPermissions));

        $stmt = $pdo->prepare("SELECT id FROM roles WHERE id = ? AND role_key <> 'administrateur'");
        $stmt->execute([$roleId]);
        if (!$stmt->fetch()) {
            $erreur = 'Ce rôle ne peut pas être modifié.';
        } elseif ($nom === '' || mb_strlen($nom) > 100) {
            $erreur = 'Le nom du rôle est obligatoire et ne doit pas dépasser 100 caractères.';
        } elseif (!$permissions) {
            $erreur = 'Choisissez au moins une tâche pour ce rôle.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM roles WHERE nom = ? AND id <> ?');
            $stmt->execute([$nom, $roleId]);
            if ($stmt->fetch()) {
                $erreur = 'Un autre rôle porte déjà ce nom.';
            } else {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('UPDATE roles SET nom = ? WHERE id = ?');
                $stmt->execute([$nom, $roleId]);
                $stmt = $pdo->prepare('DELETE FROM role_permissions WHERE role_id = ?');
                $stmt->execute([$roleId]);
                $stmt = $pdo->prepare('INSERT INTO role_permissions (role_id, permission) VALUES (?, ?)');
                foreach ($permissions as $permission) {
                    $stmt->execute([$roleId, $permission]);
                }
                $pdo->commit();
                $succes = 'Le rôle a été mis à jour.';
            }
        }
    } elseif (isset($_POST['creer_utilisateur'])) {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        $roleId = (int) ($_POST['role_id'] ?? 0);

        if ($nom === '' || mb_strlen($nom) > 100) {
            $erreur = 'Le nom est obligatoire et ne doit pas dépasser 100 caractères.';
        } elseif (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'Veuillez saisir une adresse e-mail valide.';
        } elseif (strlen($motDePasse) < 10) {
            $erreur = 'Le mot de passe doit contenir au moins 10 caractères.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $erreur = 'Cette adresse e-mail est déjà utilisée.';
            } else {
                $stmt = $pdo->prepare('SELECT id, role_key FROM roles WHERE id = ?');
                $stmt->execute([$roleId]);
                $role = $stmt->fetch();
                if (!$role) {
                    $erreur = 'Sélectionnez un rôle valide.';
                } else {
                    $legacyRole = $role['role_key'] === 'administrateur' ? 'super_admin' : 'admin';
                    $stmt = $pdo->prepare('INSERT INTO admins (nom, email, mot_de_passe, role, role_id) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$nom, $email, password_hash($motDePasse, PASSWORD_DEFAULT), $legacyRole, $roleId]);
                    $succes = 'L’utilisateur a été créé.';
                }
            }
        }
    }
}

$roles = $pdo->query('SELECT id, nom, role_key FROM roles ORDER BY est_systeme DESC, nom')->fetchAll();
$rolesUtilisables = $pdo->query('SELECT id, nom FROM roles ORDER BY est_systeme DESC, nom')->fetchAll();
$utilisateurs = $pdo->query('SELECT a.id, a.nom, a.email, a.date_creation, r.nom AS role_nom FROM admins a JOIN roles r ON r.id = a.role_id ORDER BY a.nom')->fetchAll();
$roleEnEdition = null;
if (isset($_GET['modifier_role'])) {
    $stmt = $pdo->prepare("SELECT id, nom, role_key FROM roles WHERE id = ? AND role_key <> 'administrateur'");
    $stmt->execute([(int) $_GET['modifier_role']]);
    $roleEnEdition = $stmt->fetch() ?: null;
}
$permissionsParRole = [];
$stmt = $pdo->query('SELECT rp.role_id, rp.permission FROM role_permissions rp');
foreach ($stmt->fetchAll() as $permission) {
    $permissionsParRole[$permission['role_id']][] = $permission['permission'];
}

$page_admin = 'roles';
$titre_admin = 'Utilisateurs et rôles';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1>Utilisateurs et rôles</h1></div>
<?php if ($succes): ?><div class="alerte alerte-succes"><?= h($succes) ?></div><?php endif; ?>
<?php if ($erreur): ?><div class="alerte alerte-erreur"><?= h($erreur) ?></div><?php endif; ?>
<?php if (isset($_GET['utilisateur_supprime'])): ?><div class="alerte alerte-succes">L’utilisateur a été supprimé.</div><?php endif; ?>

<div class="admin-carte">
    <h2>Créer un rôle</h2>
    <p>Un rôle donne accès uniquement aux tâches sélectionnées. L’administrateur conserve tous les accès.</p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <div class="champ">
            <label for="nom_role">Nom du rôle</label>
            <input type="text" id="nom_role" name="nom_role" maxlength="100" required>
        </div>
        <fieldset class="permissions-role">
            <legend>Tâches autorisées</legend>
            <?php foreach ($permissionsDisponibles as $permission => $libelle): ?>
            <label><input type="checkbox" name="permissions[]" value="<?= h($permission) ?>"> <?= h($libelle) ?></label>
            <?php endforeach; ?>
        </fieldset>
        <button type="submit" name="creer_role" class="bouton bouton-emeraude">Créer le rôle</button>
    </form>
</div>

<div class="admin-carte">
    <h2>Créer un utilisateur</h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <div class="grille grille-2">
            <div class="champ">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" maxlength="100" required>
            </div>
            <div class="champ">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" maxlength="150" required>
            </div>
        </div>
        <div class="grille grille-2">
            <div class="champ">
                <label for="mot_de_passe">Mot de passe temporaire (10 caractères minimum)</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="10" required>
            </div>
            <div class="champ">
                <label for="role_id">Rôle</label>
                <select id="role_id" name="role_id" required>
                    <option value="">Choisir un rôle</option>
                    <?php foreach ($rolesUtilisables as $role): ?>
                    <option value="<?= (int) $role['id'] ?>"><?= h($role['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" name="creer_utilisateur" class="bouton bouton-emeraude">Créer l’utilisateur</button>
    </form>
    <?php if (!$rolesUtilisables): ?><p>Créez d’abord un rôle pour pouvoir ajouter un utilisateur.</p><?php endif; ?>
</div>

<?php if ($roleEnEdition): ?>
<?php
$permissionsRoleEnEdition = $permissionsParRole[$roleEnEdition['id']] ?? [];
?>
<div class="admin-carte">
    <h2>Modifier le rôle : <?= h($roleEnEdition['nom']) ?></h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="role_id" value="<?= (int) $roleEnEdition['id'] ?>">
        <div class="champ">
            <label for="nom_role_modifie">Nom du rôle</label>
            <input type="text" id="nom_role_modifie" name="nom_role" maxlength="100" required value="<?= h($roleEnEdition['nom']) ?>">
        </div>
        <fieldset class="permissions-role">
            <legend>Tâches autorisées</legend>
            <?php foreach ($permissionsDisponibles as $permission => $libelle): ?>
            <label><input type="checkbox" name="permissions[]" value="<?= h($permission) ?>" <?= in_array($permission, $permissionsRoleEnEdition, true) ? 'checked' : '' ?>> <?= h($libelle) ?></label>
            <?php endforeach; ?>
        </fieldset>
        <button type="submit" name="modifier_role" class="bouton bouton-emeraude">Enregistrer le rôle</button>
        <a href="<?= BASE_URL ?>/admin/roles.php" class="bouton bouton-contour">Annuler</a>
    </form>
</div>
<?php endif; ?>

<div class="admin-carte">
    <h2>Rôles existants</h2>
    <table class="table-admin">
        <thead><tr><th>Rôle</th><th>Tâches</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($roles as $role): ?>
        <tr>
            <td><?= h($role['nom']) ?></td>
            <td>
                <?php if ($role['role_key'] === 'administrateur'): ?>
                    Toutes les tâches
                <?php else: ?>
                    <?php
                    $libelles = [];
                    foreach ($permissionsParRole[$role['id']] ?? [] as $permission) {
                        if (isset($permissionsDisponibles[$permission])) {
                            $libelles[] = $permissionsDisponibles[$permission];
                        }
                    }
                    echo h(implode(', ', $libelles));
                    ?>
                <?php endif; ?>
            </td>
            <td class="actions">
                <?php if ($role['role_key'] !== 'administrateur'): ?>
                <a href="<?= BASE_URL ?>/admin/roles.php?modifier_role=<?= (int) $role['id'] ?>">Modifier</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="admin-carte">
    <h2>Utilisateurs existants</h2>
    <table class="table-admin">
        <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Créé le</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($utilisateurs as $utilisateur): ?>
        <tr>
            <td><?= h($utilisateur['nom']) ?></td>
            <td><?= h($utilisateur['email']) ?></td>
            <td><?= h($utilisateur['role_nom']) ?></td>
            <td><?= h(formatDateFr($utilisateur['date_creation'])) ?></td>
            <td class="actions"><a href="<?= BASE_URL ?>/admin/utilisateur-form.php?id=<?= (int) $utilisateur['id'] ?>">Modifier</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
