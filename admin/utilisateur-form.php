<?php
require_once __DIR__ . '/../config/config.php';
requireFullAdmin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT a.id, a.nom, a.email, a.role_id, r.role_key FROM admins a JOIN roles r ON r.id = a.role_id WHERE a.id = ?');
$stmt->execute([$id]);
$utilisateur = $stmt->fetch();
if (!$utilisateur) {
    redirect('/admin/roles');
}
$utilisateur['id'] = (int) $utilisateur['id'];
$utilisateur['role_id'] = (int) $utilisateur['role_id'];

$roles = $pdo->query('SELECT id, nom, role_key FROM roles ORDER BY est_systeme DESC, nom')->fetchAll();
$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    if (isset($_POST['supprimer_utilisateur'])) {
        if ($id === (int) $_SESSION['admin_id']) {
            $erreur = 'Vous ne pouvez pas supprimer votre propre compte.';
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins a JOIN roles r ON r.id = a.role_id WHERE r.role_key = 'administrateur'");
            $stmt->execute();
            $nombreAdministrateurs = (int) $stmt->fetchColumn();
            if ($utilisateur['role_key'] === 'administrateur' && $nombreAdministrateurs <= 1) {
                $erreur = 'Le dernier administrateur ne peut pas être supprimé.';
            } else {
                $stmt = $pdo->prepare('DELETE FROM admins WHERE id = ?');
                $stmt->execute([$id]);
                redirect('/admin/roles?utilisateur_supprime=1');
            }
        }
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $nouveauMotDePasse = $_POST['nouveau_mot_de_passe'] ?? '';

        if ($nom === '' || mb_strlen($nom) > 100) {
            $erreur = 'Le nom est obligatoire et ne doit pas dépasser 100 caractères.';
        } elseif (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'Veuillez saisir une adresse e-mail valide.';
        } elseif ($nouveauMotDePasse !== '' && strlen($nouveauMotDePasse) < 10) {
            $erreur = 'Le nouveau mot de passe doit contenir au moins 10 caractères.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ? AND id <> ?');
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $erreur = 'Cette adresse e-mail est déjà utilisée.';
            } else {
                $stmt = $pdo->prepare('SELECT id, role_key FROM roles WHERE id = ?');
                $stmt->execute([$roleId]);
                $role = $stmt->fetch();
                if (!$role) {
                    $erreur = 'Sélectionnez un rôle valide.';
                } elseif ($id === (int) $_SESSION['admin_id'] && $roleId !== (int) $utilisateur['role_id']) {
                    $erreur = 'Vous ne pouvez pas modifier le rôle de votre propre compte.';
                } else {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins a JOIN roles r ON r.id = a.role_id WHERE r.role_key = 'administrateur'");
                    $stmt->execute();
                    $nombreAdministrateurs = (int) $stmt->fetchColumn();
                    if ($utilisateur['role_key'] === 'administrateur' && $role['role_key'] !== 'administrateur' && $nombreAdministrateurs <= 1) {
                        $erreur = 'Le dernier administrateur doit conserver son rôle.';
                    } else {
                        $roleLegacy = $role['role_key'] === 'administrateur' ? 'super_admin' : 'admin';
                        if ($nouveauMotDePasse !== '') {
                            $stmt = $pdo->prepare('UPDATE admins SET nom = ?, email = ?, role_id = ?, role = ?, mot_de_passe = ? WHERE id = ?');
                            $stmt->execute([$nom, $email, $roleId, $roleLegacy, password_hash($nouveauMotDePasse, PASSWORD_DEFAULT), $id]);
                        } else {
                            $stmt = $pdo->prepare('UPDATE admins SET nom = ?, email = ?, role_id = ?, role = ? WHERE id = ?');
                            $stmt->execute([$nom, $email, $roleId, $roleLegacy, $id]);
                        }

                        if ($id === (int) $_SESSION['admin_id']) {
                            $_SESSION['admin_nom'] = $nom;
                        }
                        redirect('/admin/utilisateur-form?id=' . $id . '&ok=1');
                    }
                }
            }
        }
    }
}

if (isset($_GET['ok'])) {
    $succes = 'Les informations de l’utilisateur ont été mises à jour.';
}

$page_admin = 'roles';
$titre_admin = 'Modifier un utilisateur';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete">
    <h1>Modifier un utilisateur</h1>
    <a href="<?= BASE_URL ?>/admin/roles.php" class="bouton bouton-contour">Retour aux utilisateurs et rôles</a>
</div>
<?php if ($succes): ?><div class="alerte alerte-succes"><?= h($succes) ?></div><?php endif; ?>
<?php if ($erreur): ?><div class="alerte alerte-erreur"><?= h($erreur) ?></div><?php endif; ?>

<div class="admin-carte">
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= (int) $utilisateur['id'] ?>">
        <div class="grille grille-2">
            <div class="champ">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" maxlength="100" required value="<?= h($utilisateur['nom']) ?>">
            </div>
            <div class="champ">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" maxlength="150" required value="<?= h($utilisateur['email']) ?>">
            </div>
        </div>
        <div class="champ">
            <label for="role_id">Rôle</label>
            <select id="role_id" name="role_id" required <?= $utilisateur['id'] === (int) $_SESSION['admin_id'] ? 'disabled' : '' ?>>
                <?php foreach ($roles as $role): ?>
                <option value="<?= (int) $role['id'] ?>" <?= (int) $utilisateur['role_id'] === (int) $role['id'] ? 'selected' : '' ?>>
                    <?= h($role['nom']) ?><?= $role['role_key'] === 'administrateur' ? ' — accès complet' : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if ($utilisateur['id'] === (int) $_SESSION['admin_id']): ?>
            <input type="hidden" name="role_id" value="<?= (int) $utilisateur['role_id'] ?>">
            <small>Votre propre rôle ne peut pas être modifié ici.</small>
            <?php endif; ?>
        </div>
        <div class="champ">
            <label for="nouveau_mot_de_passe">Définir un nouveau mot de passe (facultatif, 10 caractères minimum)</label>
            <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" minlength="10" autocomplete="new-password">
            <small>Laissez vide pour conserver le mot de passe actuel.</small>
        </div>
        <button type="submit" class="bouton bouton-emeraude">Enregistrer les modifications</button>
    </form>
</div>

<?php if ($utilisateur['id'] !== (int) $_SESSION['admin_id']): ?>
<div class="admin-carte">
    <h2>Supprimer cet utilisateur</h2>
    <p>Cette action est définitive. Les contenus publiés par cet utilisateur restent sur le site.</p>
    <form method="post" onsubmit="return confirm('Supprimer définitivement cet utilisateur ?');">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= (int) $utilisateur['id'] ?>">
        <button type="submit" name="supprimer_utilisateur" class="bouton bouton-danger">Supprimer l’utilisateur</button>
    </form>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
