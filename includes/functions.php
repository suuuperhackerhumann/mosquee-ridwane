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

// --- Authentification admin ---
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('/admin/index.php');
    }

    if (!isset($_SESSION['admin_role_id'], $_SESSION['admin_role_key'])) {
        global $pdo;
        $stmt = $pdo->prepare('SELECT a.role_id, r.role_key FROM admins a JOIN roles r ON r.id = a.role_id WHERE a.id = ?');
        $stmt->execute([(int) $_SESSION['admin_id']]);
        $role = $stmt->fetch();
        if (!$role) {
            unset($_SESSION['admin_id'], $_SESSION['admin_nom'], $_SESSION['admin_role']);
            redirect('/admin/index.php');
        }
        $_SESSION['admin_role_id'] = (int) $role['role_id'];
        $_SESSION['admin_role_key'] = $role['role_key'];
    }
}

function adminPermissions() {
    return [
        'actualites' => 'Gérer les actualités',
        'evenements' => 'Gérer les événements',
        'horaires' => 'Gérer les horaires de prière',
        'pages' => 'Modifier le contenu des pages',
        'media' => 'Gérer la médiathèque',
        'messages' => 'Consulter les messages de contact',
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
        http_response_code(403);
        exit('Accès interdit : vous n’avez pas la permission nécessaire.');
    }
}

function requireFullAdmin() {
    requireAdmin();
    if (!isFullAdmin()) {
        http_response_code(403);
        exit('Accès interdit : cette action est réservée à l’administrateur.');
    }
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function checkCsrf() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('Requête invalide (jeton de sécurité manquant ou expiré). Merci de recharger la page.');
    }
}
