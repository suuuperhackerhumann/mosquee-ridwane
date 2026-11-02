<?php
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(SITE_URL, '/');
$urls = [
    ['loc' => $base . '/', 'lastmod' => null],
];

foreach ([
    'a-propos.php',
    'horaires.php',
    'actualites.php',
    'evenements.php',
    'enseignements.php',
    'services.php',
    'mediatheque.php',
    'discussions.php',
    'contact.php',
    'dons.php',
] as $page) {
    $urls[] = ['loc' => $base . '/' . $page, 'lastmod' => null];
}

$actualites = $pdo->query("SELECT slug, date_publication FROM actualites WHERE statut = 'publie' ORDER BY date_publication DESC")->fetchAll();
foreach ($actualites as $actualite) {
    $urls[] = [
        'loc' => $base . '/actualite.php?slug=' . rawurlencode($actualite['slug']),
        'lastmod' => $actualite['date_publication'],
    ];
}

$evenements = $pdo->query("SELECT slug, date_creation FROM evenements WHERE statut = 'publie' ORDER BY date_creation DESC")->fetchAll();
foreach ($evenements as $evenement) {
    $urls[] = [
        'loc' => $base . '/evenement.php?slug=' . rawurlencode($evenement['slug']),
        'lastmod' => $evenement['date_creation'],
    ];
}

$xml = new XMLWriter();
$xml->openMemory();
$xml->startDocument('1.0', 'UTF-8');
$xml->startElement('urlset');
$xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

foreach ($urls as $url) {
    $xml->startElement('url');
    $xml->writeElement('loc', $url['loc']);
    if ($url['lastmod']) {
        $xml->writeElement('lastmod', date(DATE_W3C, strtotime($url['lastmod'])));
    }
    $xml->endElement();
}

$xml->endElement();
$xml->endDocument();
echo $xml->outputMemory();
