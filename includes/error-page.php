<?php
$errorHomeUrl = rtrim(BASE_URL, '/') . '/index.php';
$errorAdminUrl = rtrim(BASE_URL, '/') . '/admin/dashboard.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0e2d29">
    <title><?= (int) $errorCode ?> — <?= h($errorTitle) ?> | <?= h(SITE_NOM) ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon-32.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
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
            <img class="erreur-logo" src="<?= BASE_URL ?>/assets/img/logo-mosquee.png" alt="">
            <p class="erreur-code"><?= (int) $errorCode ?></p>
            <h1 id="titre-erreur"><?= h($errorTitle) ?></h1>
            <p><?= h($errorMessage) ?></p>
            <div class="erreur-actions">
                <a class="bouton bouton-emeraude" href="<?= h($errorHomeUrl) ?>">Retour au site officiel</a>
                <?php if (!empty($errorReturnUrl) && !empty($errorReturnLabel)): ?>
                <a class="erreur-lien-secondaire" href="<?= h($errorReturnUrl) ?>"><?= h($errorReturnLabel) ?></a>
                <?php elseif ($errorCode === 403 && function_exists('isAdminLoggedIn') && isAdminLoggedIn()): ?>
                <a class="erreur-lien-secondaire" href="<?= h($errorAdminUrl) ?>">Tableau de bord admin</a>
                <?php endif; ?>
            </div>
            <span class="erreur-marque"><?= h(SITE_NOM) ?> · Riviera Abatta, Abidjan</span>
        </section>
    </main>
</body>
</html>
