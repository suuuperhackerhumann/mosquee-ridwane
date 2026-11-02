<?php
/**
 * setup-admin.php
 * Mosquée Ridwane — Création / réinitialisation du compte administrateur
 *
 * A UTILISER UNE SEULE FOIS après l'import de la base de données,
 * puis à SUPPRIMER du serveur pour des raisons de sécurité.
 *
 * Ce script :
 *  - se connecte à la base mosquee_ridwane
 *  - si aucun admin n'existe encore -> permet d'en créer un (role super_admin)
 *  - si un admin existe déjà -> exige la clé secrète ci-dessous pour en ajouter un autre
 *  - hash le mot de passe avec password_hash() (bcrypt), jamais en clair
 */

session_start();

// ============================================================
// 1) CONFIGURATION — à adapter selon ton hébergeur (Hostinger, o2switch, etc.)
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'mosquee_ridwane');
define('DB_USER', 'root');           // change en prod
define('DB_PASS', '');               // change en prod

// Clé de sécurité à utiliser UNIQUEMENT si un admin existe déjà
// et que tu veux quand même passer par ce script (ex: mot de passe oublié).
// Change cette valeur avant la mise en ligne !
define('SETUP_SECRET_KEY', 'change-moi-avant-mise-en-ligne');

// ============================================================
// 2) CONNEXION BASE DE DONNÉES
// ============================================================
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . htmlspecialchars($e->getMessage()));
}

// ============================================================
// 3) ÉTAT ACTUEL : y a-t-il déjà un admin en base ?
// ============================================================
$nbAdmins = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$adminExiste = $nbAdmins > 0;

// Jeton CSRF simple
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erreurs = [];
$succes = false;

// ============================================================
// 4) TRAITEMENT DU FORMULAIRE
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $erreurs[] = "Session invalide, merci de recharger la page et réessayer.";
    }

    // Si un admin existe déjà, on exige la clé secrète
    if ($adminExiste) {
        $cle = $_POST['setup_key'] ?? '';
        if (!hash_equals(SETUP_SECRET_KEY, $cle)) {
            $erreurs[] = "Clé de sécurité incorrecte. Un administrateur existe déjà.";
        }
    }

    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    if ($nom === '') {
        $erreurs[] = "Le nom est obligatoire.";
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "Adresse email invalide.";
    }
    if (strlen($motDePasse) < 8) {
        $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }
    if ($motDePasse !== $confirmation) {
        $erreurs[] = "Les deux mots de passe ne correspondent pas.";
    }

    // Email déjà utilisé ?
    if (empty($erreurs)) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        if ((int) $stmt->fetchColumn() > 0) {
            $erreurs[] = "Cet email est déjà utilisé par un autre administrateur.";
        }
    }

    // Création
    if (empty($erreurs)) {
        $hash = password_hash($motDePasse, PASSWORD_BCRYPT);
        $role = $adminExiste ? 'admin' : 'super_admin';

        $stmt = $pdo->prepare(
            'INSERT INTO admins (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nom, $email, $hash, $role]);

        $succes = true;
        $adminExiste = true;
        // Nouveau jeton CSRF après succès
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mosquée Ridwane — Configuration administrateur</title>
<style>
    :root {
        --vert-fonce: #0f3d2e;
        --vert: #1a5c42;
        --or: #c9a24b;
        --creme: #faf7ef;
        --texte: #2b2b26;
        --erreur: #a8342a;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: "Segoe UI", Tahoma, sans-serif;
        background: linear-gradient(180deg, var(--vert-fonce) 0%, var(--vert) 45%, var(--creme) 45%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .carte {
        width: 100%;
        max-width: 440px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        overflow: hidden;
    }
    .entete {
        background: var(--vert-fonce);
        color: var(--creme);
        padding: 28px 28px 20px;
        text-align: center;
        border-bottom: 3px solid var(--or);
    }
    .entete .motif {
        font-size: 28px;
        color: var(--or);
        letter-spacing: 6px;
    }
    .entete h1 {
        margin: 8px 0 4px;
        font-size: 18px;
        font-weight: 600;
    }
    .entete p {
        margin: 0;
        font-size: 12.5px;
        color: #d8e6dc;
    }
    .contenu {
        padding: 26px 28px 30px;
        color: var(--texte);
    }
    .badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        margin-bottom: 16px;
    }
    .badge-premier { background: #e8f3ec; color: var(--vert); }
    .badge-existant { background: #fbeeea; color: var(--erreur); }

    label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin: 14px 0 6px;
    }
    input[type="text"], input[type="email"], input[type="password"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d8d3c4;
        border-radius: 6px;
        font-size: 14px;
        background: var(--creme);
    }
    input:focus {
        outline: none;
        border-color: var(--or);
        box-shadow: 0 0 0 3px rgba(201,162,75,0.25);
    }
    button {
        width: 100%;
        margin-top: 22px;
        padding: 12px;
        border: none;
        border-radius: 6px;
        background: var(--vert-fonce);
        color: var(--creme);
        font-size: 14.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    button:hover { background: var(--vert); }

    .liste-erreurs {
        background: #fbeeea;
        border-left: 4px solid var(--erreur);
        color: var(--erreur);
        padding: 12px 14px;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 6px;
    }
    .liste-erreurs ul { margin: 4px 0 0; padding-left: 18px; }

    .succes {
        background: #e8f3ec;
        border-left: 4px solid var(--vert);
        color: var(--vert-fonce);
        padding: 14px;
        border-radius: 6px;
        font-size: 13.5px;
        line-height: 1.5;
    }
    .avertissement {
        margin-top: 18px;
        font-size: 12px;
        color: #8a5a00;
        background: #fff6e0;
        border-radius: 6px;
        padding: 10px 12px;
        line-height: 1.5;
    }
    .lien-retour {
        display: inline-block;
        margin-top: 16px;
        font-size: 13px;
        color: var(--vert);
        text-decoration: none;
        font-weight: 600;
    }
</style>
</head>
<body>
<div class="carte">
    <div class="entete">
        <div class="motif">۞</div>
        <h1>Mosquée Ridwane</h1>
        <p>Configuration du compte administrateur</p>
    </div>
    <div class="contenu">

        <?php if ($succes): ?>
            <div class="succes">
                <strong>Compte administrateur créé avec succès.</strong><br>
                Tu peux maintenant te connecter à l'espace d'administration avec cet email et ce mot de passe.
            </div>
            <div class="avertissement">
                ⚠️ Pour la sécurité du site, supprime (ou renomme) le fichier <code>setup-admin.php</code> du serveur maintenant.
            </div>
        <?php else: ?>

            <?php if ($adminExiste): ?>
                <span class="badge badge-existant">Un administrateur existe déjà</span>
            <?php else: ?>
                <span class="badge badge-premier">Premier compte — sera super_admin</span>
            <?php endif; ?>

            <?php if (!empty($erreurs)): ?>
                <div class="liste-erreurs">
                    <strong>Merci de corriger :</strong>
                    <ul>
                        <?php foreach ($erreurs as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <?php if ($adminExiste): ?>
                    <label for="setup_key">Clé de sécurité (obligatoire, un admin existe déjà)</label>
                    <input type="password" id="setup_key" name="setup_key" required>
                <?php endif; ?>

                <label for="nom">Nom complet</label>
                <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">

                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                <label for="mot_de_passe">Mot de passe (8 caractères minimum)</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="8">

                <label for="confirmation">Confirmer le mot de passe</label>
                <input type="password" id="confirmation" name="confirmation" required minlength="8">

                <button type="submit">Créer le compte administrateur</button>
            </form>

        <?php endif; ?>

    </div>
</div>
</body>
</html>