<?php
require_once __DIR__ . '/config/config.php';

$adminRequest = ($_GET['admin'] ?? '') === '1';
if ($adminRequest) {
    requireAdminPermission('discussions');
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

$session = $pdo->query("SELECT id, titre, statut FROM live_sessions ORDER BY (statut = 'actif') DESC, date_debut DESC LIMIT 1")->fetch();

if (!$session) {
    echo json_encode(['session' => null, 'questions' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$stmt = $pdo->prepare('SELECT id, nom_auteur, contenu, statut, date_creation FROM live_questions WHERE session_id = ? ORDER BY date_creation DESC LIMIT 100');
$stmt->execute([(int) $session['id']]);
$questions = array_map(static function ($question) {
    return [
        'id' => (int) $question['id'],
        'nom' => $question['nom_auteur'] ?: 'Anonyme',
        'contenu' => $question['contenu'],
        'statut' => $question['statut'],
        'date' => formatDateFr($question['date_creation'], true),
    ];
}, $stmt->fetchAll());

echo json_encode([
    'session' => [
        'id' => (int) $session['id'],
        'titre' => $session['titre'],
        'statut' => $session['statut'],
    ],
    'questions' => $questions,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
