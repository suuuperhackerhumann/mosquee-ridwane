<?php
require_once __DIR__ . '/../config/config.php';

if (isAdminLoggedIn()) {
    redirect('/admin/dashboard.php');
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['mot_de_passe'] ?? '';

    $stmt = $pdo->prepare("SELECT a.*, r.role_key FROM admins a JOIN roles r ON r.id = a.role_id WHERE a.email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($motdepasse, $admin['mot_de_passe'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nom'] = $admin['nom'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_role_id'] = (int) $admin['role_id'];
        $_SESSION['admin_role_key'] = $admin['role_key'];
        redirect('/admin/dashboard.php');
    } else {
        $erreur = "E-mail ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — Espace admin — Mosquée Ridwane</title>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Karla:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">
<div class="login-page">
    <div class="login-carte">
        <h2 style="text-align:center;">Espace d'administration</h2>
        <p style="text-align:center; color:var(--encre-600); font-size:0.9rem; margin-bottom:24px;">Mosquée Ridwane</p>

        <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= h($erreur) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/admin/index.php">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <div class="champ">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <div class="champ">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            <button type="submit" class="bouton bouton-emeraude" style="width:100%;">Se connecter</button>
        </form>
        <p style="text-align:center; margin-top:20px;"><a href="<?= BASE_URL ?>/index.php" style="font-size:0.85rem; color:var(--encre-600);">← Retour au site</a></p>
    </div>
</div>
</body>
</html>
