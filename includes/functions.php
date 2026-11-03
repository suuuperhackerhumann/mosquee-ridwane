<?php
/**
 * Fonctions utilitaires — Mosquée Ridwane
 */

// Échappement HTML rapide
function h($texte) {
    return htmlspecialchars($texte ?? '', ENT_QUOTES, 'UTF-8');
}

// Récupère un bloc de contenu modifiable (table pages_contenu) par sa clé
function getContenu($cle) {
    global $pdo;
    static $cache = [];
    if (isset($cache[$cle])) return $cache[$cle];
    $stmt = $pdo->prepare("SELECT * FROM pages_contenu WHERE cle = ?");
    $stmt->execute([$cle]);
    $cache[$cle] = $stmt->fetch();
    return $cache[$cle];
}

function contenuTexte($cle, $defaut = '') {
    $c = getContenu($cle);
    return $c ? $c['contenu'] : $defaut;
}

function contenuTitre($cle, $defaut = '') {
    $c = getContenu($cle);
    return $c ? $c['titre'] : $defaut;
}

// Horaires de prière du jour (ou le plus récent disponible si le jour n'existe pas)
function getHorairesDuJour() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM horaires_prieres WHERE date_jour = CURDATE()");
    $stmt->execute();
    $h = $stmt->fetch();
    if ($h) return $h;
    // repli sur le dernier enregistrement connu
    $stmt = $pdo->query("SELECT * FROM horaires_prieres ORDER BY date_jour DESC LIMIT 1");
    return $stmt->fetch();
}

function formatHeure($heure) {
    if (!$heure) return '—';
    return substr($heure, 0, 5);
}

function formatDateFr($date, $avecHeure = false) {
    if (!$date) return '';
    $mois = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $jours = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
    $ts = is_numeric($date) ? $date : strtotime($date);
    $jourSemaine = $jours[date('w', $ts)];
    $j = date('j', $ts);
    $m = $mois[date('n', $ts) - 1];
    $a = date('Y', $ts);
    $result = "$jourSemaine $j $m $a";
    if ($avecHeure) $result .= ' à ' . date('H:i', $ts);
    return $result;
}

function moisAbrev($date) {
    $mois = ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
    $ts = is_numeric($date) ? $date : strtotime($date);
    return $mois[date('n', $ts) - 1];
}

function slugify($texte) {
    $texte = iconv('UTF-8', 'ASCII//TRANSLIT', $texte);
    $texte = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $texte));
    return trim($texte, '-');
}

function extraitTexte($texte, $longueur = 160) {
    $texte = strip_tags($texte);
    if (mb_strlen($texte) <= $longueur) return $texte;
    return mb_substr($texte, 0, $longueur) . '…';
}

// Renvoie ' actif' si $fichier correspond à la page actuellement affichée (pour la nav)
function estActif($fichier) {
    $courante = basename($_SERVER['SCRIPT_NAME']);
    $courante = preg_replace('/\.php$/i', '', $courante);
    $fichier = preg_replace('/\.php$/i', '', $fichier);
    return $courante === $fichier ? ' actif' : '';
}

function redirect($url) {
    if (strpos($url, '/') === 0 && strpos($url, '//') !== 0 && $url !== BASE_URL && strpos($url, BASE_URL . '/') !== 0) {
        $url = rtrim(BASE_URL, '/') . $url;
    }
    header("Location: $url");
    exit;
}

function siteAssetUrl($path) {
    if (!$path || preg_match('/^(https?:)?\/\//i', $path)) {
        return $path;
    }
    if (strpos($path, BASE_URL . '/') === 0) {
        return $path;
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function publicRevisionPath() {
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR
        . 'mosquee-ridwane-' . hash('sha256', dirname(__DIR__)) . '.revision';
}

function publicRevision() {
    $path = publicRevisionPath();
    if (!is_file($path)) {
        return '0';
    }

    $revision = file_get_contents($path);
    if ($revision === false) {
        throw new RuntimeException('Impossible de lire la version du contenu public.');
    }

    return trim($revision);
}

function signalerModificationPublique() {
    $revision = bin2hex(random_bytes(16));
    if (file_put_contents(publicRevisionPath(), $revision, LOCK_EX) === false) {
        throw new RuntimeException('Impossible de signaler la mise à jour du contenu public.');
    }
}

function whatsappMessageUrl($message) {
    if (!defined('SITE_WHATSAPP') || !preg_match('~^https://wa\.me/\d+$~', SITE_WHATSAPP)) {
        return '';
    }

    return SITE_WHATSAPP . '?text=' . rawurlencode($message);
}

// --- Authentification admin ---
function isAdminLoggedIn() {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }

    $maintenant = time();
    $debutSession = (int) ($_SESSION['admin_session_started_at'] ?? 0);
    $derniereActivite = (int) ($_SESSION['admin_last_activity'] ?? 0);
    $delaiInactivite = defined('ADMIN_SESSION_IDLE_TIMEOUT') ? ADMIN_SESSION_IDLE_TIMEOUT : 1800;
    $dureeMaximale = defined('ADMIN_SESSION_MAX_LIFETIME') ? ADMIN_SESSION_MAX_LIFETIME : 28800;
    if (
        !$debutSession
        || !$derniereActivite
        || $debutSession > $maintenant
        || $derniereActivite > $maintenant
        || $maintenant - $derniereActivite >= $delaiInactivite
        || $maintenant - $debutSession >= $dureeMaximale
    ) {
        foreach (array_keys($_SESSION) as $cle) {
            if (strpos($cle, 'admin_') === 0) {
                unset($_SESSION[$cle]);
            }
        }
        session_regenerate_id(true);
        return false;
    }

    return true;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('/admin');
    }

    global $pdo;
    $stmt = $pdo->prepare('SELECT a.nom, a.role, a.role_id, r.role_key FROM admins a JOIN roles r ON r.id = a.role_id WHERE a.id = ?');
    $stmt->execute([(int) $_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    if (!$admin) {
        destroyCurrentSession();
        redirect('/admin');
    }

    $_SESSION['admin_last_activity'] = time();
    $_SESSION['admin_nom'] = $admin['nom'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_role_id'] = (int) $admin['role_id'];
    $_SESSION['admin_role_key'] = $admin['role_key'];
}

function destroyCurrentSession() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function adminPermissions() {
    return [
        'actualites' => 'Gérer les actualités',
        'evenements' => 'Gérer les événements',
        'horaires' => 'Gérer les horaires de prière',
        'pages' => 'Modifier le contenu des pages',
        'media' => 'Gérer la médiathèque',
        'messages' => 'Consulter les messages de contact',
        'discussions' => 'Gérer les discussions',
    ];
}

function isFullAdmin() {
    return ($_SESSION['admin_role_key'] ?? '') === 'administrateur';
}

function hasAdminPermission($permission) {
    if (isFullAdmin()) {
        return true;
    }

    $permissions = adminPermissions();
    if (!isset($permissions[$permission]) || empty($_SESSION['admin_role_id'])) {
        return false;
    }

    static $permissionsParRole = [];
    $roleId = (int) $_SESSION['admin_role_id'];
    if (!isset($permissionsParRole[$roleId])) {
        global $pdo;
        $stmt = $pdo->prepare('SELECT permission FROM role_permissions WHERE role_id = ?');
        $stmt->execute([$roleId]);
        $permissionsParRole[$roleId] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    return in_array($permission, $permissionsParRole[$roleId], true);
}

function requireAdminPermission($permission) {
    requireAdmin();
    if (!hasAdminPermission($permission)) {
        renderSiteError(403, 'Accès refusé', 'Votre compte ne dispose pas de l’autorisation nécessaire pour consulter cette page.');
    }
}

function requireFullAdmin() {
    requireAdmin();
    if (!isFullAdmin()) {
        renderSiteError(403, 'Accès réservé', 'Cette page est accessible uniquement à l’administrateur du site.');
    }
}

function renderSiteError($code, $title, $message, $returnUrl = null, $returnLabel = null) {
    if (!in_array($code, [403, 404], true)) {
        throw new InvalidArgumentException('Code de page d’erreur non pris en charge.');
    }

    http_response_code($code);
    $errorCode = $code;
    $errorTitle = $title;
    $errorMessage = $message;
    $errorReturnUrl = $returnUrl;
    $errorReturnLabel = $returnLabel;
    require __DIR__ . '/error-page.php';
    exit;
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function checkCsrf() {
    if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('Requête invalide (jeton de sécurité manquant ou expiré). Merci de recharger la page.');
    }
}
