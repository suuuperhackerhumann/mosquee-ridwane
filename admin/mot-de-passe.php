<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $motDePasseActuel = $_POST['mot_de_passe_actuel'] ?? '';
    $nouveauMotDePasse = $_POST['nouveau_mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    $stmt = $pdo->prepare('SELECT mot_de_passe FROM admins WHERE id = ?');
    $stmt->execute([(int) $_SESSION['admin_id']]);
    $hashActuel = $stmt->fetchColumn();

    if (!$hashActuel || !password_verify($motDePasseActuel, $hashActuel)) {
        $erreur = 'Le mot de passe actuel est incorrect.';
    } elseif (strlen($nouveauMotDePasse) < 10) {
        $erreur = 'Le nouveau mot de passe doit contenir au moins 10 caractères.';
    } elseif ($nouveauMotDePasse !== $confirmation) {
        $erreur = 'La confirmation ne correspond pas au nouveau mot de passe.';
    } elseif (password_verify($nouveauMotDePasse, $hashActuel)) {
        $erreur = 'Choisissez un mot de passe différent de l’actuel.';
    } else {
        $stmt = $pdo->prepare('UPDATE admins SET mot_de_passe = ? WHERE id = ?');
        $stmt->execute([password_hash($nouveauMotDePasse, PASSWORD_DEFAULT), (int) $_SESSION['admin_id']]);
        session_regenerate_id(true);
        $succes = 'Votre mot de passe a été modifié.';
    }
}

$page_admin = 'mot-de-passe';
$titre_admin = 'Changer mon mot de passe';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1>Changer mon mot de passe</h1></div>
<?php if ($succes): ?><div class="alerte alerte-succes"><?= h($succes) ?></div><?php endif; ?>
<?php if ($erreur): ?><div class="alerte alerte-erreur"><?= h($erreur) ?></div><?php endif; ?>

<div class="admin-carte">
    <form method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <div class="champ">
            <label for="mot_de_passe_actuel">Mot de passe actuel</label>
            <input type="password" id="mot_de_passe_actuel" name="mot_de_passe_actuel" autocomplete="current-password" required>
        </div>
        <div class="champ">
            <label for="nouveau_mot_de_passe">Nouveau mot de passe (10 caractères minimum)</label>
            <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" minlength="10" autocomplete="new-password" required>
        </div>
        <div class="champ">
            <label for="confirmation">Confirmer le nouveau mot de passe</label>
            <input type="password" id="confirmation" name="confirmation" minlength="10" autocomplete="new-password" required>
        </div>
        <button type="submit" class="bouton bouton-emeraude">Mettre à jour le mot de passe</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
