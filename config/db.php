<?php
/**
 * Connexion à la base de données MySQL via PDO
 * Renseigne ces identifiants avec ceux fournis par ton hébergeur
 * (ex : InfinityFree, o2switch, etc.)
 */

define('DB_HOTE', 'localhost');
define('DB_NOM', 'mosquee_ridwane');
define('DB_UTILISATEUR', 'root');
define('DB_MOT_DE_PASSE', '');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOTE . ";dbname=" . DB_NOM . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_UTILISATEUR, DB_MOT_DE_PASSE, $options);
} catch (PDOException $e) {
    if (defined('MODE_DEBUG') && MODE_DEBUG) {
        die('Erreur de connexion à la base de données : ' . $e->getMessage());
    }
    die('Le site est temporairement indisponible. Merci de réessayer plus tard.');
}
