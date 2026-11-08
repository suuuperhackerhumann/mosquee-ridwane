<?php
$errorScriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$errorScriptPath = preg_replace('~/(?:admin|config|includes)(?:/.*)?$~', '', $errorScriptPath);
$errorBaseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : rtrim($errorScriptPath, '/');
$errorSiteName = defined('SITE_NOM') ? SITE_NOM : 'Mosquée Ridwane';
$errorEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$errorHomeUrl = $errorBaseUrl . '/index.php';
$errorAdminUrl = $errorBaseUrl . '/admin/dashboard.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0e2d29">
    <title><?= (int) $errorCode ?> — <?= $errorEscape($errorTitle) ?> | <?= $errorEscape($errorSiteName) ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $errorEscape($errorBaseUrl) ?>/assets/img/favicon-32.png?v=20261108">
    <link rel="stylesheet" href="<?= $errorEscape($errorBaseUrl) ?>/assets/css/style.css">
    <style>
        .erreur-page { min-height: 100vh; display: grid; place-items: center; padding: 28px 16px; background: radial-gradient(ellipse at top right, var(--mint-700), var(--mint-950) 70%); }
        .erreur-carte { width: min(100%, 560px); padding: clamp(24px, 6vw, 48px); border: 1px solid rgba(216, 184, 116, .4); border-radius: 12px; background: var(--plâtre-50); text-align: center; box-shadow: var(--ombre-forte); }
        .erreur-logo { width: 88px; height: 88px; margin: 0 auto 20px; object-fit: contain; border-radius: 50%; background: #fff; }
        .erreur-code { margin: 0; color: var(--laiton-500); font-family: var(--police-donnee); font-size: clamp(4rem, 18vw, 7rem); font-weight: 700; line-height: 1; }
        .erreur-carte h1 { margin: 12px 0; font-size: clamp(1.7rem, 6vw, 2.4rem); }
        .erreur-carte p { margin: 0 auto 24px; max-width: 420px; color: var(--encre-600); }
        .erreur-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; }
        .erreur-lien-secondaire { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 10px 16px; border: 1px solid var(--mint-700); border-radius: var(--rayon); color: var(--mint-800); font-weight: 700; }
        .erreur-lien-secondaire:hover { background: var(--eucalyptus-100); }
        .erreur-marque { display: block; margin-top: 24px; color: var(--encre-600); font-size: .85rem; }
    </style>
</head>
<body>
    <main class="erreur-page">
        <section class="erreur-carte" aria-labelledby="titre-erreur">
            <img class="erreur-logo" src="<?= $errorEscape($errorBaseUrl) ?>/assets/img/logo-mosquee.png?v=20261108" alt="">
            <p class="erreur-code"><?= (int) $errorCode ?></p>
            <h1 id="titre-erreur"><?= $errorEscape($errorTitle) ?></h1>
            <p><?= $errorEscape($errorMessage) ?></p>
            <div class="erreur-actions">
                <a class="bouton bouton-emeraude" href="<?= $errorEscape($errorHomeUrl) ?>">Retour au site officiel</a>
                <?php if (!empty($errorReturnUrl) && !empty($errorReturnLabel)): ?>
                <a class="erreur-lien-secondaire" href="<?= $errorEscape($errorReturnUrl) ?>"><?= $errorEscape($errorReturnLabel) ?></a>
                <?php elseif ($errorCode === 403 && function_exists('isAdminLoggedIn') && isAdminLoggedIn()): ?>
                <a class="erreur-lien-secondaire" href="<?= $errorEscape($errorAdminUrl) ?>">Tableau de bord admin</a>
                <?php endif; ?>
            </div>
            <span class="erreur-marque"><?= $errorEscape($errorSiteName) ?> · Riviera Abatta, Abidjan</span>
        </section>
    </main>
</body>
</html>
