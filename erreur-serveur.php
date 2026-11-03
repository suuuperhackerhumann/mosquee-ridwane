<?php
$errorCode = isset($errorCode) ? (int) $errorCode : (int) ($_SERVER['REDIRECT_STATUS'] ?? 500);
$errorPages = [
    500 => [
        'title' => 'Une erreur est survenue',
        'message' => 'Le site rencontre un problème inattendu. Notre équipe en a été informée. Merci de réessayer dans quelques instants.',
    ],
    502 => [
        'title' => 'Service momentanément inaccessible',
        'message' => 'Le site ne peut pas répondre pour le moment. Merci de patienter quelques instants avant de réessayer.',
    ],
    503 => [
        'title' => 'Service temporairement indisponible',
        'message' => 'Le site est momentanément indisponible, notamment en raison d’une maintenance ou d’un problème technique. Merci de réessayer plus tard.',
    ],
    504 => [
        'title' => 'Le délai de réponse est dépassé',
        'message' => 'Le site met trop de temps à répondre. Merci de réessayer dans quelques instants.',
    ],
];

if (!isset($errorPages[$errorCode])) {
    $errorCode = 500;
}

http_response_code($errorCode);
$errorTitle = $errorPages[$errorCode]['title'];
$errorMessage = $errorPages[$errorCode]['message'];
$errorReturnUrl = null;
$errorReturnLabel = null;
require __DIR__ . '/includes/error-page.php';
exit;
