<?php
/**
 * Configuration générale — Mosquée Ridwane
 * Modifie les constantes ci-dessous avant la mise en ligne.
 */

// --- Informations générales du site ---
define('SITE_NOM', 'Mosquée Ridwane');
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/mosquee-ridwane/index.php'));
$adminPath = strpos($basePath, '/admin');
if ($adminPath !== false) {
    $basePath = substr($basePath, 0, $adminPath);
}
define('BASE_URL', rtrim($basePath, '/'));
define('SITE_URL', 'https://www.mosquee-ridwane.org'); // à adapter au domaine réel
define('SITE_DESCRIPTION', "La Mosquée Ridwane — horaires de prière, actualités, événements, enseignements et services pour toute la communauté.");

// --- Coordonnées (modifiables aussi via l'admin > Pages > Contact) ---
define('SITE_ADRESSE', 'Côte d\'Ivoire,ABIDJAN,RIVIERA ABATTA  non loin du carrefour BCEAO ');
define('SITE_TELEPHONE', '+225 07 05 92 75');
define('SITE_EMAIL', 'contact@mosquee-ridwane.org');
define('SITE_FACEBOOK', 'https://www.facebook.com/profile.php?id=61589458095478');
define('SITE_INSTAGRAM', '#');
define('SITE_YOUTUBE', '#');
define('SITE_WHATSAPP', 'https://wa.me/22507059275');
define('SITE_IMAM_TELEPHONE', '+225 07 08 38 18 47');
define('SITE_SECRETAIRE_TELEPHONE', '+225 01 72 16 02 42');
define('SITE_PRESIDENT_TELEPHONE', '+225 07 07 76 21 62');

// --- Coordonnées GPS pour la carte (page Contact) ---
// Riviera Abatta, Abidjan, Côte d'Ivoire
define('SITE_LAT', '5.3457053505111585');
define('SITE_LNG', '-3.920595904453195');

// --- Environnement ---
define('MODE_DEBUG', false); // passer à false en production

if (MODE_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

date_default_timezone_set('Africa/Abidjan');

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../includes/functions.php';
