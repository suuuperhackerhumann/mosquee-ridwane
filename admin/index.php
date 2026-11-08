<?php
require_once __DIR__ . '/../config/config.php';

$adminConnecte = isAdminLoggedIn();
$connexionEnCours = $adminConnecte && !empty($_SESSION['admin_login_pending']);
if ($adminConnecte && !$connexionEnCours) {
    redirect('/admin/dashboard');
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
        $_SESSION['admin_session_started_at'] = time();
        $_SESSION['admin_last_activity'] = time();
        $_SESSION['admin_login_pending'] = true;
        redirect('/admin');
    } else {
        $erreur = "E-mail ou mot de passe incorrect.";
    }
}

if ($connexionEnCours) {
    unset($_SESSION['admin_login_pending']);
    $dashboardUrl = BASE_URL . '/admin/dashboard';
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="2;url=<?= h($dashboardUrl) ?>">
    <title>Connexion réussie — Mosquée Ridwane</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon-32.png?v=20261108">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    </head>
    <body class="admin-body">
    <main class="login-page">
        <section class="login-carte" aria-labelledby="titre-connexion-reussie">
            <img class="login-logo" src="<?= BASE_URL ?>/assets/img/logo-mosquee.png?v=20261108" alt="">
            <h1 id="titre-connexion-reussie" style="text-align:center;">Connexion réussie</h1>
            <p class="login-message" role="status">Ouverture de votre espace d’administration…</p>
            <div class="login-progression" aria-hidden="true"><span></span></div>
        </section>
    </main>
    <script>
        window.setTimeout(function () {
            window.location.replace(<?= json_encode($dashboardUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
        }, 1500);
    </script>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion admin — Mosquée Ridwane</title>
<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon-32.png?v=20261108">
<link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/img/apple-touch-icon.png?v=20261108">
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Karla:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">
<div class="login-page">
    <div class="login-carte">
        <img class="login-logo" src="<?= BASE_URL ?>/assets/img/logo-mosquee.png?v=20261108" alt="Emblème de la Mosquée Ridwane">
        <h1 style="text-align:center;">Connexion admin</h1>
        <p style="text-align:center; color:var(--encre-600); font-size:0.9rem; margin-bottom:18px;">Mosquée Ridwane</p>

        <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= h($erreur) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/admin/">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <div class="champ">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <div class="champ">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            <button type="submit" class="bouton bouton-emeraude" style="width:100%;"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Se connecter</button>
        </form>
        <p style="text-align:center; margin-top:20px;"><a href="<?= BASE_URL ?>/" style="font-size:0.85rem; color:var(--encre-600);"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour au site</a></p>
    </div>
</div>
</body>
</html>
