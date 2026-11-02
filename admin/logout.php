<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}
checkCsrf();
$_SESSION = [];
session_destroy();
redirect('/admin/index.php');
